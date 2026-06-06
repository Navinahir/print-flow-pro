<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;

class MarketingLegalPagesTest extends TestCase
{
    public function test_privacy_page_renders(): void
    {
        $this->get('/privacy')
            ->assertOk()
            ->assertSee(__('marketing.legal.privacy.title'), false);
    }

    public function test_terms_page_renders(): void
    {
        $this->get('/terms')
            ->assertOk()
            ->assertSee(__('marketing.legal.terms.title'), false);
    }
}
