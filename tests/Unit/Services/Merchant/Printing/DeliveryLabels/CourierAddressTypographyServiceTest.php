<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Merchant\Printing\DeliveryLabels;

use App\Services\Merchant\Printing\DeliveryLabels\CourierAddressTypographyService;
use App\Services\Merchant\Printing\DeliveryLabels\CourierCsvHeaderDetector;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class CourierAddressTypographyServiceTest extends TestCase
{
    private CourierAddressTypographyService $typography;

    protected function setUp(): void
    {
        parent::setUp();

        $this->typography = new CourierAddressTypographyService;
    }

    #[DataProvider('fontSizeProvider')]
    public function test_resolve_font_size_px(string $address, int $expectedFontSize): void
    {
        $this->assertSame($expectedFontSize, $this->typography->resolveFontSizePx($address));
    }

    /**
     * @return array<string, array{0: string, 1: int}>
     */
    public static function fontSizeProvider(): array
    {
        return [
            'short address uses default 18px' => ['No. 88, Zhongxiao East Rd', 18],
            'threshold address uses default 18px' => [str_repeat('A', 35), 18],
            'long address shrinks below default' => [str_repeat('A', 50), 14],
            'very long address hits 14px floor' => [str_repeat('A', 120), 14],
        ];
    }

    public function test_wrap_address_lines_splits_explicit_newlines(): void
    {
        $lines = $this->typography->wrapAddressLines("Line one\nLine two");

        $this->assertSame(['Line one', 'Line two'], $lines);
    }

    public function test_csv_header_detector_finds_courier_columns(): void
    {
        $detector = new CourierCsvHeaderDetector;

        $columns = $detector->detectColumns([
            'Recipient Name',
            'Courier Address',
            'Remarks',
            'Tracking Number',
            'Carrier',
        ]);

        $this->assertSame('Recipient Name', $columns['recipient']);
        $this->assertSame('Courier Address', $columns['address']);
        $this->assertSame('Remarks', $columns['remarks']);
        $this->assertSame('Tracking Number', $columns['tracking']);
        $this->assertSame('Carrier', $columns['carrier']);
    }

    public function test_csv_header_detector_finds_chinese_columns(): void
    {
        $detector = new CourierCsvHeaderDetector;

        $columns = $detector->detectColumns([
            '收件人',
            '地址',
            '備註',
            '追蹤號碼',
            '物流商',
        ]);

        $this->assertSame('收件人', $columns['recipient']);
        $this->assertSame('地址', $columns['address']);
        $this->assertSame('備註', $columns['remarks']);
        $this->assertSame('追蹤號碼', $columns['tracking']);
        $this->assertSame('物流商', $columns['carrier']);
    }
}
