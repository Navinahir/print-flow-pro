<?php

declare(strict_types=1);

namespace App\Providers;

use App\Actions\Merchant\Pdf\HandlePdfProcessingFailure;
use App\Actions\Merchant\Pdf\PreparePdfProcessingContext;
use App\Actions\Merchant\Pdf\ResolvePdfEngineConfiguration;
use App\Actions\Merchant\Pdf\RunPdfProcessingPipeline;
use App\Contracts\Merchant\Pdf\PdfBoundaryDetectionInterface;
use App\Contracts\Merchant\Pdf\PdfCanvasInterface;
use App\Contracts\Merchant\Pdf\PdfConfigurationInterface;
use App\Contracts\Merchant\Pdf\PdfEngineInterface;
use App\Contracts\Merchant\Pdf\PdfNormalizationInterface;
use App\Contracts\Merchant\Pdf\PdfTempStorageInterface;
use App\Contracts\Merchant\Pdf\PdfValidationInterface;
use App\Contracts\Merchant\Pdf\ThermalPdfNormalizationInterface;
use App\Contracts\Merchant\Pdf\ThermalPdfValidationInterface;
use App\Services\Merchant\Pdf\PdfBoundaryDetectionService;
use App\Services\Merchant\Pdf\PdfCanvasService;
use App\Services\Merchant\Pdf\PdfConfigurationService;
use App\Services\Merchant\Pdf\PdfEngineService;
use App\Services\Merchant\Pdf\PdfNormalizationService;
use App\Services\Merchant\Pdf\PdfProcessorRegistry;
use App\Services\Merchant\Pdf\PdfTempStorageService;
use App\Services\Merchant\Pdf\PdfValidationService;
use App\Services\Merchant\Pdf\Pipeline\Stages\DetectBoundariesStage;
use App\Services\Merchant\Pdf\Pipeline\Stages\FinalizeProcessingStage;
use App\Services\Merchant\Pdf\Pipeline\Stages\InitializeProcessingStage;
use App\Services\Merchant\Pdf\Pipeline\Stages\NormalizeProcessingStage;
use App\Services\Merchant\Pdf\Pipeline\Stages\PrepareCanvasStage;
use App\Services\Merchant\Pdf\Pipeline\Stages\ValidateInputStage;
use App\Services\Merchant\Pdf\Processors\LogisticsLabelsProcessor;
use App\Services\Merchant\Pdf\Support\FpdiDocumentAdapter;
use App\Services\Merchant\Pdf\ThermalA4SheetComposer;
use App\Services\Merchant\Pdf\ThermalPdfNormalizationService;
use App\Services\Merchant\Pdf\ThermalPdfValidationService;
use Illuminate\Support\ServiceProvider;

class PdfServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(FpdiDocumentAdapter::class);

        $this->app->singleton(PdfConfigurationInterface::class, PdfConfigurationService::class);
        $this->app->singleton(PdfCanvasInterface::class, PdfCanvasService::class);
        $this->app->singleton(PdfTempStorageInterface::class, PdfTempStorageService::class);
        $this->app->singleton(PdfBoundaryDetectionInterface::class, PdfBoundaryDetectionService::class);
        $this->app->singleton(PdfValidationInterface::class, PdfValidationService::class);
        $this->app->singleton(ThermalPdfValidationInterface::class, ThermalPdfValidationService::class);
        $this->app->singleton(ThermalPdfNormalizationInterface::class, ThermalPdfNormalizationService::class);
        $this->app->singleton(ThermalA4SheetComposer::class);
        $this->app->singleton(PdfNormalizationInterface::class, PdfNormalizationService::class);
        $this->app->singleton(PdfEngineInterface::class, PdfEngineService::class);

        $this->app->singleton(LogisticsLabelsProcessor::class);

        $this->app->singleton(PdfProcessorRegistry::class, function ($app): PdfProcessorRegistry {
            return new PdfProcessorRegistry([
                $app->make(LogisticsLabelsProcessor::class),
            ]);
        });

        $this->app->singleton(InitializeProcessingStage::class);
        $this->app->singleton(ValidateInputStage::class);
        $this->app->singleton(DetectBoundariesStage::class);
        $this->app->singleton(PrepareCanvasStage::class);
        $this->app->singleton(NormalizeProcessingStage::class);
        $this->app->singleton(FinalizeProcessingStage::class);

        $this->app->singleton(ResolvePdfEngineConfiguration::class);
        $this->app->singleton(PreparePdfProcessingContext::class);
        $this->app->singleton(RunPdfProcessingPipeline::class);
        $this->app->singleton(HandlePdfProcessingFailure::class);
    }
}
