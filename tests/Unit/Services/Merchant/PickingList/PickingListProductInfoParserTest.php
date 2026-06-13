<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Merchant\PickingList;

use App\Services\Merchant\PickingList\PickingListProductInfoParser;
use Tests\TestCase;

class PickingListProductInfoParserTest extends TestCase
{
    private PickingListProductInfoParser $parser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->parser = app(PickingListProductInfoParser::class);
    }

    public function test_parses_shopee_product_info_string(): void
    {
        $productInfo = '[1] 商品名稱:【樂上網 】高速直流馬達 DC12V/DC24V 大扭力 DC Motor 5000RPM DIY機器人 自動化設備; 商品選項名稱:KA-38R64L,24V; 價格: $ 550; 數量: 1; 商品選項貨號: ;  ';

        $parsed = $this->parser->parse($productInfo);

        $this->assertStringContainsString('高速直流馬達', $parsed['product_name']);
        $this->assertSame('KA-38R64L,24V', $parsed['variant_name']);
        $this->assertSame(1, $parsed['quantity']);
    }

    public function test_parses_format_b_variant_and_quantity(): void
    {
        $productInfo = '[1] 商品名稱:【樂上網】菲律賓SIM 上網卡5G 無限不降速｜SMART 原生手機門號 5–30天吃到飽 長灘島巴拉望宿霧馬尼拉薄荷島; 商品選項名稱:5天,🟧A:5G無限(接電話); 價格: $ 362; 數量: 2; 商品選項貨號: ;  ';

        $parsed = $this->parser->parse($productInfo);

        $this->assertStringContainsString('菲律賓SIM', $parsed['product_name']);
        $this->assertSame('5天,🟧A:5G無限(接電話)', $parsed['variant_name']);
        $this->assertSame(2, $parsed['quantity']);
    }

    public function test_maps_sku_like_variant_name_to_variant_sku_column(): void
    {
        $parsed = $this->parser->parse('[1] 商品名稱:Motor; 商品選項名稱:KA-38R64L,24V; 價格: $ 550; 數量: 1; 商品選項貨號: ;');

        $columns = $this->parser->resolveVariantColumns($parsed);

        $this->assertSame('KA-38R64L,24V', $columns['variant_sku']);
        $this->assertSame('', $columns['variant_name']);
    }

    public function test_maps_descriptive_variant_name_to_variant_name_column(): void
    {
        $parsed = $this->parser->parse('[1] 商品名稱:SIM; 商品選項名稱:5天,🟧A:5G無限(接電話); 價格: $ 362; 數量: 2; 商品選項貨號: ;');

        $columns = $this->parser->resolveVariantColumns($parsed);

        $this->assertSame('', $columns['variant_sku']);
        $this->assertSame('5天,🟧A:5G無限(接電話)', $columns['variant_name']);
    }
}
