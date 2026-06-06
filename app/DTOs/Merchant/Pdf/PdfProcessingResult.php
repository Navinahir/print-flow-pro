<?php

declare(strict_types=1);

namespace App\DTOs\Merchant\Pdf;

use App\Enums\PdfProcessingStatus;

final readonly class PdfProcessingResult
{
    /**
     * @param  list<string>  $errors
     */
    public function __construct(
        public bool $success,
        public PdfProcessingContext $context,
        public PdfProcessingStatus $status,
        public ?PdfValidationResult $validation = null,
        public ?PdfNormalizationResult $normalization = null,
        public ?string $message = null,
        public array $errors = [],
    ) {}

    public static function fromContext(
        PdfProcessingContext $context,
        ?PdfValidationResult $validation = null,
        ?PdfNormalizationResult $normalization = null,
    ): self {
        $message = $normalization?->message
            ?? ($normalization?->implemented
                ? __('merchant.pdf.processing.complete')
                : __('merchant.pdf.processing.framework_complete'));

        return new self(
            success: true,
            context: $context,
            status: $context->status,
            validation: $validation,
            normalization: $normalization,
            message: $message,
        );
    }

    public function withSuccess(bool $success): self
    {
        if ($this->success === $success) {
            return $this;
        }

        return new self(
            success: $success,
            context: $this->context,
            status: $success ? $this->status : PdfProcessingStatus::Failed,
            validation: $this->validation,
            normalization: $this->normalization,
            message: $this->message,
            errors: $success ? $this->errors : ($this->errors !== [] ? $this->errors : [$this->message ?? '']),
        );
    }

    /**
     * @param  list<string>  $errors
     */
    public static function failed(
        PdfProcessingContext $context,
        string $message,
        array $errors = [],
        ?PdfValidationResult $validation = null,
    ): self {
        return new self(
            success: false,
            context: $context->withStatus(PdfProcessingStatus::Failed),
            status: PdfProcessingStatus::Failed,
            validation: $validation,
            message: $message,
            errors: $errors,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'success' => $this->success,
            'status' => $this->status->value,
            'message' => $this->message,
            'errors' => $this->errors,
            'validation' => $this->validation?->toArray(),
            'normalization' => $this->normalization?->toArray(),
            'context' => [
                'upload_job_id' => $this->context->uploadJobId,
                'merchant_id' => $this->context->merchantId,
                'mode' => $this->context->mode->value,
                'status' => $this->context->status->value,
            ],
        ];
    }
}
