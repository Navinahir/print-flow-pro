<?php

namespace Tests\Feature;

use Tests\TestCase;

class MarketingPageTest extends TestCase
{
    public function test_marketing_tw_page_renders(): void
    {
        $response = $this->get('/tw');

        $response->assertOk();
        $response->assertSee('id="features"', false);
    }

    public function test_marketing_en_page_renders(): void
    {
        $response = $this->get('/en');

        $response->assertOk();
    }

    public function test_marketing_tw_uses_traditional_chinese_locale(): void
    {
        $response = $this->get('/tw');

        $response->assertOk();
        $this->assertSame('zh-TW', app()->getLocale());
    }

    public function test_marketing_tw_shows_copy_from_text_memo(): void
    {
        $response = $this->get('/tw');

        $response->assertOk();
        $response->assertSee('跨超商一站式出貨解決方案', false);
        $response->assertSee('不用再分開列印超商物流', false);
        $response->assertSee('立即體驗', false);
    }

    public function test_marketing_en_uses_english_locale(): void
    {
        $response = $this->get('/en');

        $response->assertOk();
        $this->assertSame('en', app()->getLocale());
    }

    public function test_marketing_logo_links_to_current_locale_home(): void
    {
        $this->get('/en')
            ->assertOk()
            ->assertSee('href="'.route('marketing.en').'"', false);

        $this->get('/tw')
            ->assertOk()
            ->assertSee('href="'.route('marketing.tw').'"', false);
    }
}
