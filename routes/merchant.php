<?php

use App\Http\Controllers\Merchant\DashboardController;
use App\Http\Controllers\Merchant\LocaleController;
use App\Http\Controllers\Merchant\ProfileController;
use App\Http\Controllers\Merchant\ThemeController;
use App\Http\Controllers\Merchant\PdfUploadDownloadController;
use App\Http\Controllers\Merchant\PdfUploadPreviewController;
use App\Http\Controllers\Merchant\PrintJobRegenerateController;
use App\Http\Controllers\Merchant\UploadController;
use App\Http\Controllers\Merchant\UploadPreviewController;
use Illuminate\Support\Facades\Route;

Route::get('/dashboard', DashboardController::class)
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/uploads', [UploadController::class, 'index'])->name('uploads.index');
    Route::get('/uploads/create', [UploadController::class, 'create'])->name('uploads.create');
    Route::post('/uploads', [UploadController::class, 'store'])->name('uploads.store');
    Route::get('/uploads/{upload}', [UploadController::class, 'show'])->name('uploads.show');
    Route::delete('/uploads/{upload}', [UploadController::class, 'destroy'])->name('uploads.destroy');
    Route::post('/uploads/{upload}/regenerate', [UploadController::class, 'regenerate'])->name('uploads.regenerate');
    Route::post('/uploads/{upload}/print-jobs/{printJob}/regenerate', PrintJobRegenerateController::class)->name('uploads.print_jobs.regenerate');
    Route::get('/uploads/{upload}/pdf-uploads/{pdfUpload}/preview', PdfUploadPreviewController::class)->name('uploads.pdf.preview');
    Route::get('/uploads/{upload}/pdf-uploads/{pdfUpload}/download', PdfUploadDownloadController::class)->name('uploads.pdf.download');
    Route::post('/uploads/{upload}/preview', [UploadPreviewController::class, 'show'])->name('uploads.preview.show');
});

Route::post('/locale', [LocaleController::class, 'update'])->name('locale.update');
Route::post('/theme', [ThemeController::class, 'update'])->name('theme.update');

Route::middleware(['auth'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::get('/profile/photo/{user}', [ProfileController::class, 'showPhoto'])->name('profile.photo.show');
    Route::post('/profile/photo', [ProfileController::class, 'updatePhoto'])->name('profile.photo.update');
    Route::delete('/profile/photo', [ProfileController::class, 'destroyPhoto'])->name('profile.photo.destroy');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/merchant/auth.php';
require __DIR__.'/merchant/printing.php';
