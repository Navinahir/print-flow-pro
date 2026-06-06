<?php

declare(strict_types=1);

namespace App\Enums;

enum PrintJobStatus: string
{
    case Pending = 'pending';
    case Processing = 'processing';
    case Ready = 'ready';
    case Failed = 'failed';
    case Downloaded = 'downloaded';
    case Shredded = 'shredded';

    public function label(): string
    {
        return match ($this) {
            self::Pending => __('merchant.print_jobs.status.pending'),
            self::Processing => __('merchant.print_jobs.status.processing'),
            self::Ready => __('merchant.print_jobs.status.ready'),
            self::Failed => __('merchant.print_jobs.status.failed'),
            self::Downloaded => __('merchant.print_jobs.status.downloaded'),
            self::Shredded => __('merchant.print_jobs.status.shredded'),
        };
    }
}
