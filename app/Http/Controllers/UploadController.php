<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\UploadJobType;
use App\Http\Requests\StoreUploadRequest;
use App\Models\UploadJob;
use App\Services\UploadService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UploadController extends Controller
{
    public function __construct(
        private readonly UploadService $uploadService,
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', UploadJob::class);

        $jobs = UploadJob::query()
            ->forUser($request->user())
            ->with(['uploadedBy', 'merchant'])
            ->latest()
            ->paginate(15);

        return view('uploads.index', [
            'jobs' => $jobs,
            'uploadTypes' => UploadJobType::cases(),
        ]);
    }

    public function create(Request $request): View
    {
        $this->authorize('create', UploadJob::class);

        return view('uploads.create', [
            'uploadTypes' => UploadJobType::cases(),
        ]);
    }

    public function store(StoreUploadRequest $request): RedirectResponse
    {
        $user = $request->user();
        $merchant = $user->merchant;

        if ($merchant === null) {
            return back()->withErrors([
                'files' => 'Your account is not linked to a merchant profile. Please contact support.',
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

        $upload->load(['pdfUploads', 'uploadedBy', 'merchant']);

        return view('uploads.show', [
            'job' => $upload,
        ]);
    }
}
