<?php

use Illuminate\Support\Facades\Route;

Route::view('/tw', 'home')->name('marketing.tw');

Route::view('/en', 'home')->name('marketing.en');

Route::view('/privacy', 'marketing.pages.privacy')->name('marketing.privacy');

Route::view('/terms', 'marketing.pages.terms')->name('marketing.terms');
