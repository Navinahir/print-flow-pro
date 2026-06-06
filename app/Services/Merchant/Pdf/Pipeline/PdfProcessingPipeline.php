<?php

declare(strict_types=1);

namespace App\Services\Merchant\Pdf\Pipeline;

use App\Contracts\Merchant\Pdf\PdfPipelineStageInterface;
use App\DTOs\Merchant\Pdf\PdfProcessingContext;
use App\Exceptions\Merchant\Pdf\PdfEngineException;
use App\Exceptions\Merchant\Pdf\PdfProcessingException;

/**
 * Runs an ordered list of pipeline stages against an immutable-style context object.
 */
class PdfProcessingPipeline
{
    /**
     * @var list<PdfPipelineStageInterface>
     */
    private array $stages = [];

    public function pipe(PdfPipelineStageInterface $stage): self
    {
        $this->stages[] = $stage;

        return $this;
    }

    public function run(PdfProcessingContext $context): PdfProcessingContext
    {
        foreach ($this->stages as $stage) {
            try {
                $context = $stage->handle($context);
            } catch (PdfEngineException $exception) {
                throw $exception;
            } catch (\Throwable $throwable) {
                throw PdfProcessingException::stageFailed($stage->name(), $throwable->getMessage());
            }
        }

        return $context;
    }

    /**
     * @return list<PdfPipelineStageInterface>
     */
    public function stages(): array
    {
        return $this->stages;
    }
}
