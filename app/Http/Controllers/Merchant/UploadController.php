<?php

declare(strict_types=1);

namespace App\Http\Controllers\Merchant;

use App\Actions\Merchant\Upload\DeleteUploadJob;
use App\Actions\Merchant\Upload\RegenerateUploadProcessing;
use App\Enums\UploadJobType;
use App\Enums\UploadStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUploadRequest;
use App\Models\UploadJob;
use App\Services\Merchant\UploadPreviewService;
use App\Services\Merchant\UploadService;
use App\Services\Merchant\UploadShowViewService;
use App\Services\Merchant\UploadTypeGuideService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UploadController extends Controller
{
    public function __construct(
        private readonly UploadService $uploadService,
        private readonly UploadPreviewService $uploadPreviewService,
        private readonly UploadShowViewService $uploadShowViewService,
        private readonly UploadTypeGuideService $uploadTypeGuideService,
        private readonly RegenerateUploadProcessing $regenerateUploadProcessing,
        private readonly DeleteUploadJob $deleteUploadJob,
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
            'uploadGuides' => $this->uploadTypeGuideService->guidesForForm(),
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
            thermalCombinedOutput: $request->uploadType() === UploadJobType::ThermalLabel
                ? $request->thermalCombinedOutput()
                : null,
        );

        return redirect()
            ->route('uploads.show', $job)
            ->with('status', 'upload-received');
    }

    public function show(Request $request, UploadJob $upload): View
    {
        $this->authorize('view', $upload);

        $upload->load(['pdfUploads', 'uploadedBy', 'merchant', 'deliveryLabels', 'printJobs.pdfUpload']);

        $uploadPreview = $this->uploadPreviewService->resolve($upload);
        $showView = $this->uploadShowViewService->prepare($upload, $uploadPreview);

        return view('merchant.pages.uploads.show', [
            'job' => $upload,
            'uploadPreview' => $uploadPreview,
            'previewConfig' => $showView['preview_config'],
            'showView' => $showView,
        ]);
    }

    public function regenerate(Request $request, UploadJob $upload): RedirectResponse
    {
        $this->authorize('update', $upload);

        $this->regenerateUploadProcessing->execute($upload);

        return redirect()
            ->route('uploads.show', $upload)
            ->with('status', 'upload-regenerating');
    }

    public function destroy(Request $request, UploadJob $upload): JsonResponse|RedirectResponse
    {
        $this->authorize('delete', $upload);

        $this->deleteUploadJob->execute($upload);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => __('merchant.uploads.delete.success'),
            ]);
        }

        return redirect()
            ->route('uploads.index')
            ->with('status', 'upload-deleted');
    }
}
