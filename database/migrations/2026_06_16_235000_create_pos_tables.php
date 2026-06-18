<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pos_products', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->string('name', 100);
            $table->string('category', 50);
            $table->string('sku', 50)->nullable();
            $table->string('unit', 20)->default('piece');
            $table->integer('cost_paise')->default(0);
            $table->integer('price_paise');
            $table->decimal('gst_rate', 5, 2)->default(0);
            $table->integer('stock_quantity')->default(0);
            $table->integer('low_stock_threshold')->default(5);
            $table->string('photo_url')->nullable();
            $table->text('description')->nullable();
            $table->string('status', 20)->default('active');
            $table->timestamps();

            $table->unique(['tenant_id', 'name']);
            $table->unique(['tenant_id', 'sku']);
            $table->index(['tenant_id', 'category']);
            $table->index(['tenant_id', 'status']);
        });

        Schema::create('pos_sales', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->unsignedBigInteger('branch_id')->index();
            $table->string('bill_number', 20)->unique();
            $table->foreignId('member_id')->nullable()->constrained('members')->nullOnDelete();
            $table->integer('subtotal_paise');
            $table->integer('gst_paise')->default(0);
            $table->integer('discount_paise')->default(0);
            $table->integer('total_paise');
            $table->string('method', 20);
            $table->string('reference', 100)->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('sold_by')->nullable()->constrained('staff')->nullOnDelete();
            $table->timestamp('refunded_at')->nullable();
            $table->foreignId('refunded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('refund_reason', 200)->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'created_at']);
            $table->index(['tenant_id', 'method']);
            $table->index(['tenant_id', 'branch_id']);
        });

        Schema::create('pos_sale_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('sale_id')->constrained('pos_sales')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('pos_products')->restrictOnDelete();
            $table->string('product_name', 100);
            $table->integer('qty');
            $table->integer('unit_price_paise');
            $table->decimal('gst_rate', 5, 2)->default(0);
            $table->integer('line_subtotal_paise');
            $table->integer('gst_paise')->default(0);
            $table->integer('line_total_paise');
            $table->timestamps();
        });

        Schema::create('pos_stock_movements', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('product_id')->constrained('pos_products')->cascadeOnDelete();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->unsignedBigInteger('branch_id')->nullable()->index();
            $table->foreignId('sale_id')->nullable()->constrained('pos_sales')->nullOnDelete();
            $table->string('type', 20);
            $table->integer('quantity');
            $table->integer('cost_paise')->nullable();
            $table->string('reason', 200)->nullable();
            $table->string('reference', 100)->nullable();
            $table->date('movement_date');
            $table->foreignId('created_by')->nullable()->constrained('staff')->nullOnDelete();
            $table->timestamps();

            $table->index(['tenant_id', 'type']);
            $table->index(['tenant_id', 'movement_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pos_stock_movements');
        Schema::dropIfExists('pos_sale_items');
        Schema::dropIfExists('pos_sales');
        Schema::dropIfExists('pos_products');
    }
};
