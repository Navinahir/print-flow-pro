<?php

declare(strict_types=1);

namespace App\Http\Controllers\Merchant\Printing;

use App\Http\Controllers\Controller;
use App\Http\Requests\Merchant\Printing\ValidateAspectRatioRequest;
use App\Services\Merchant\Preview\AspectRatioValidationService;
use Illuminate\Http\JsonResponse;

class AspectRatioValidationController extends Controller
{
    public function __construct(
        private readonly AspectRatioValidationService $aspectRatioValidation,
    ) {}

    public function store(ValidateAspectRatioRequest $request): JsonResponse
    {
        $result = $request->hasFile('file')
            ? $this->aspectRatioValidation->validateUploadedFile($request->file('file'))
            : $this->aspectRatioValidation->validateDimensions(
                (int) $request->validated('width'),
                (int) $request->validated('height'),
            );

        return response()->json([
            ...$result->toArray(),
            'message' => $result->valid
                ? __('merchant.preview.aspect_ratio.valid')
                : __('merchant.preview.aspect_ratio.invalid', [
                    'deviation' => number_format($result->deviationPercent, 1),
                    'tolerance' => number_format($result->tolerancePercent, 0),
                ]),
        ]);
    }
}
