<?php

declare(strict_types=1);

namespace App\Http\Controllers\Merchant;

use App\Enums\UploadJobType;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUploadRequest;
use App\Models\UploadJob;
use App\Services\Merchant\Preview\PreviewConfigurationService;
use App\Services\Merchant\UploadPreviewService;
use App\Services\UploadService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UploadController extends Controller
{
    public function __construct(
        private readonly UploadService $uploadService,
        private readonly UploadPreviewService $uploadPreviewService,
        private readonly PreviewConfigurationService $previewConfigurationService,
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', UploadJob::class);

        $jobs = UploadJob::query()
            ->forUser($request->user())
            ->with(['uploadedBy', 'merchant'])
            ->latest()
            ->paginate(15);

        return view('merchant.pages.uploads.index', [
            'jobs' => $jobs,
            'uploadTypes' => UploadJobType::cases(),
        ]);
    }

    public function create(Request $request): View
    {
        $this->authorize('create', UploadJob::class);

        return view('merchant.pages.uploads.create', [
            'uploadTypes' => UploadJobType::cases(),
        ]);
    }

    public function store(StoreUploadRequest $request): RedirectResponse
    {
        $user = $request->user();
        $merchant = $user->merchant;

        if ($merchant === null) {
            return back()->withErrors([
                'files' => __('merchant.uploads.errors.no_merchant_profile'),
            ]);
        }

        $job = $this->uploadService->createJob(
            user: $user,
            merchant: $merchant,
            type: $request->uploadType(),
            files: $request->file('files', []),
        );

        return redirect()
            ->route('uploads.show', $job)
            ->with('status', 'upload-received');
    }

    public function show(Request $request, UploadJob $upload): View
    {
        $this->authorize('view', $upload);

        $upload->load(['pdfUploads', 'uploadedBy', 'merchant', 'deliveryLabels']);

        return view('merchant.pages.uploads.show', [
            'job' => $upload,
            'uploadPreview' => $this->uploadPreviewService->resolve($upload),
            'previewConfig' => $this->previewConfigurationService->configuration(),
        ]);
    }
}
