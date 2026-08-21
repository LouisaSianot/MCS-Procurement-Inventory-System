<?php

use Illuminate\Support\Facades\Blade;

test('dashboard icon component renders a lucide icon', function () {
    $html = Blade::render('<x-dashboard-icon name="layout-dashboard" class="h-5 w-5 text-brand-600" />');

    expect($html)
        ->toContain('data-lucide="layout-dashboard"')
        ->toContain('h-5 w-5')
        ->toContain('text-brand-600');
});
