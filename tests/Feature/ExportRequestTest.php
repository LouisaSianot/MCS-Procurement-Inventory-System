<?php

use App\Jobs\GenerateExport;
use App\Models\ExportRequest;
use App\Models\User;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;

it('queues a purchase order xlsx export instead of loading it in the request', function () {
    Queue::fake();
    $user = User::factory()->create();
    $user->assignRole(Role::firstOrCreate(['name' => 'procurement_officer', 'guard_name' => 'web']));

    $response = $this->actingAs($user)->get(route('procurement.export', [
        'format' => 'xlsx',
        'search' => 'paper',
        'status' => 'ordered',
    ]));

    $response->assertRedirect()->assertSessionHas('success');
    $export = ExportRequest::firstOrFail();

    expect($export->type)->toBe('purchase_orders')
        ->and($export->status)->toBe(ExportRequest::STATUS_PENDING)
        ->and($export->filters)->toBe(['search' => 'paper', 'status' => 'ordered']);

    Queue::assertPushed(GenerateExport::class, fn (GenerateExport $job) => $job->exportRequestId === $export->id);
});

it('prevents another user from downloading an export', function () {
    $owner = User::factory()->create();
    $otherUser = User::factory()->create();
    $export = ExportRequest::create([
        'user_id' => $owner->id,
        'type' => 'reports',
        'status' => ExportRequest::STATUS_COMPLETED,
        'path' => 'exports/report.xlsx',
        'filename' => 'report.xlsx',
    ]);

    $this->actingAs($otherUser)
        ->get(route('exports.download', $export))
        ->assertForbidden();
});

it('generates a query-backed purchase order workbook in the queue job', function () {
    Storage::fake('local');
    $user = User::factory()->create();
    $export = ExportRequest::create([
        'user_id' => $user->id,
        'type' => 'purchase_orders',
        'filters' => [],
        'status' => ExportRequest::STATUS_PENDING,
        'filename' => 'purchase-orders-test.xlsx',
    ]);

    GenerateExport::dispatchSync($export->id);

    expect($export->fresh()->status)->toBe(ExportRequest::STATUS_COMPLETED)
        ->and($export->fresh()->path)->toBe('exports/purchase-orders-test.xlsx');

    expect(Storage::disk('local')->exists('exports/purchase-orders-test.xlsx'))->toBeTrue();
});
