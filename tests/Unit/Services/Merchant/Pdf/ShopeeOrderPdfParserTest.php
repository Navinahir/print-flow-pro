<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Merchant\Pdf;

use App\Services\Merchant\Pdf\ShopeeOrderPdfParser;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Smalot\PdfParser\Parser;
use Tests\Support\PdfFixtureFactory;
use Tests\TestCase;

class ShopeeOrderPdfParserTest extends TestCase
{
    public function test_parses_shopee_order_table_from_extracted_text(): void
    {
        $text = <<<'TEXT'
商品列表
訂單編號 (單): 260521116PAHD1 package 1
買家備註 :
# 主商品貨號	商品名稱	商品選項貨號 商品規格名稱 數量 總計
1	【樂上網】菲律賓 SIM 上
網卡5G 無限不降速｜
SMART 原生手機門號 5–
30天吃到飽 長灘島巴拉望
宿霧馬尼拉薄荷島
5天, A:5G無限(接電
話)
2 724
TEXT;

        $parser = new ShopeeOrderPdfParser(new Parser);
        $result = $parser->parseText($text);

        $this->assertCount(1, $result->orders);
        $this->assertSame('260521116PAHD1 package 1', $result->orders[0]->orderNumber);
        $this->assertCount(1, $result->lineItems);
        $this->assertSame(2, $result->lineItems[0]['qty']);
        $this->assertSame('NT$ 724', $result->lineItems[0]['price']);
        $this->assertStringContainsString('菲律賓 SIM', $result->lineItems[0]['name']);
    }

    public function test_parses_line_items_from_generated_pdf_fixture(): void
    {
        Storage::fake('temp');

        $relativePath = 'parser-test/order.pdf';
        PdfFixtureFactory::putA4Label($relativePath);

        $parser = new ShopeeOrderPdfParser(new Parser);
        $result = $parser->parseFile(Storage::disk('temp')->path($relativePath));

        $this->assertSame([], $result->lineItems);

        File::deleteDirectory(storage_path('framework/testing/disks/temp/parser-test'));
    }
}
