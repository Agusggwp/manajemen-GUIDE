<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Ensure default operational shift is open for general tests unless explicitly tested
        if (\Illuminate\Support\Facades\Schema::hasTable('settings')) {
            \App\Models\Setting::set('operational_status', 'OPEN');
            \App\Models\Setting::set('operational_status_date', \Carbon\Carbon::today()->toDateString());
        }
    }
}
