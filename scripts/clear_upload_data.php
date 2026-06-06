<?php

require __DIR__.'/../vendor/autoload.php';

$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\DeliveryLabel;
use App\Models\PdfUpload;
use App\Models\PickingList;
use App\Models\PrintJob;
use App\Models\UploadJob;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

$deletedFiles = 0;

$deleteStoredFile = static function (?string $disk, ?string $path) use (&$deletedFiles): void {
    if ($disk === null || $path === null || $path === '') {
        return;
    }

    if (Storage::disk($disk)->exists($path)) {
        Storage::disk($disk)->delete($path);
        $deletedFiles++;
    }
};

PrintJob::query()->each(function (PrintJob $printJob) use ($deleteStoredFile): void {
    $deleteStoredFile($printJob->output_disk, $printJob->output_path);
});

PdfUpload::query()->each(function (PdfUpload $pdfUpload) use ($deleteStoredFile): void {
    $deleteStoredFile($pdfUpload->disk, $pdfUpload->path);
});

DeliveryLabel::query()->each(function (DeliveryLabel $label) use ($deleteStoredFile): void {
    $deleteStoredFile($label->output_disk, $label->output_path);
});

PickingList::query()->each(function (PickingList $list) use ($deleteStoredFile): void {
    $deleteStoredFile($list->source_disk, $list->source_path);
    $deleteStoredFile($list->output_disk, $list->output_path);
});

$counts = [
    'print_jobs' => PrintJob::query()->count(),
    'pdf_uploads' => PdfUpload::query()->count(),
    'delivery_labels' => DeliveryLabel::query()->count(),
    'picking_lists' => PickingList::query()->count(),
    'upload_jobs' => UploadJob::query()->count(),
];

DB::transaction(function (): void {
    PrintJob::query()->delete();
    DeliveryLabel::query()->delete();
    PickingList::query()->delete();
    PdfUpload::query()->delete();
    UploadJob::query()->delete();

    DB::table('audit_logs')
        ->where('auditable_type', UploadJob::class)
        ->delete();
});

$tempMerchants = storage_path('app/temp/merchants');
$tempClientVerify = storage_path('app/temp/client-verify');

foreach ([$tempMerchants, $tempClientVerify] as $directory) {
    if (is_dir($directory)) {
        File::deleteDirectory($directory);
        $deletedFiles++;
    }
}

echo "Cleared database rows:\n";

foreach ($counts as $table => $count) {
    echo "  - {$table}: {$count}\n";
}

echo "Removed tracked files: {$deletedFiles}\n";
echo "Removed temp directories: merchants/, client-verify/ (if present)\n";
echo "Done.\n";
