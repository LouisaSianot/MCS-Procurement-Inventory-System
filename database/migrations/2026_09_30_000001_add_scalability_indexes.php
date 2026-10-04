<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $v4Names = DB::connection()->getDriverName() === 'pgsql';
        $purchaseOrderTable = $v4Names ? 'ORDERS' : 'purchase_orders';
        $purchaseReceiptTable = $v4Names ? 'RECEIPT' : 'purchase_receipts';
        $itemBranchTable = $v4Names ? 'ITEMBRANCH' : 'item_branches';

        Schema::table('ge_orders', function (Blueprint $table): void {
            $table->index(['status', 'order_date'], 'ge_orders_status_order_date_index');
            $table->index('approval_status', 'ge_orders_approval_status_index');
        });

        Schema::table($purchaseOrderTable, function (Blueprint $table): void {
            $table->index(['status', 'order_date'], 'purchase_orders_status_order_date_index');
            $table->index(['branch_id', 'status'], 'purchase_orders_branch_status_index');
            $table->index(['supplier_id', 'status'], 'purchase_orders_supplier_status_index');
            $table->index('ordered_at', 'purchase_orders_ordered_at_index');
        });

        Schema::table('purchase_order_items', function (Blueprint $table): void {
            $table->index(['item_id', 'purchase_order_id'], 'purchase_order_items_item_order_index');
        });

        Schema::table($purchaseReceiptTable, function (Blueprint $table): void {
            $table->index(['received_at', 'id'], 'purchase_receipts_received_at_id_index');
            $table->index('purchase_order_id', 'purchase_receipts_purchase_order_index');
        });

        Schema::table($itemBranchTable, function (Blueprint $table): void {
            $table->index(['branch_id', 'location_id'], 'item_branches_branch_location_index');
            $table->index(['item_id', 'branch_id', 'location_id'], 'item_branches_item_branch_location_index');
        });
    }

    public function down(): void
    {
        $v4Names = DB::connection()->getDriverName() === 'pgsql';
        $purchaseOrderTable = $v4Names ? 'ORDERS' : 'purchase_orders';
        $purchaseReceiptTable = $v4Names ? 'RECEIPT' : 'purchase_receipts';
        $itemBranchTable = $v4Names ? 'ITEMBRANCH' : 'item_branches';

        Schema::table('ge_orders', function (Blueprint $table): void {
            $table->dropIndex('ge_orders_status_order_date_index');
            $table->dropIndex('ge_orders_approval_status_index');
        });

        Schema::table($purchaseOrderTable, function (Blueprint $table): void {
            $table->dropIndex('purchase_orders_status_order_date_index');
            $table->dropIndex('purchase_orders_branch_status_index');
            $table->dropIndex('purchase_orders_supplier_status_index');
            $table->dropIndex('purchase_orders_ordered_at_index');
        });

        Schema::table('purchase_order_items', function (Blueprint $table): void {
            $table->dropIndex('purchase_order_items_item_order_index');
        });

        Schema::table($purchaseReceiptTable, function (Blueprint $table): void {
            $table->dropIndex('purchase_receipts_received_at_id_index');
            $table->dropIndex('purchase_receipts_purchase_order_index');
        });

        Schema::table($itemBranchTable, function (Blueprint $table): void {
            $table->dropIndex('item_branches_branch_location_index');
            $table->dropIndex('item_branches_item_branch_location_index');
        });
    }
};
