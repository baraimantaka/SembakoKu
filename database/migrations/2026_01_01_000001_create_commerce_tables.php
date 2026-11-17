<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Users Table
        Schema::create('users', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('phone')->nullable()->unique();
            $table->string('password');
            $table->string('role')->default('customer'); // customer, admin, driver
            $table->text('address')->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->boolean('is_online')->default(false);
            $table->rememberToken();
            $table->timestamps();
        });

        // Products Table
        Schema::create('products', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('title');
            $table->string('category');
            $table->string('unit'); // kg, pcs, liter, galon
            $table->decimal('price', 12, 2);
            $table->integer('stock');
            $table->integer('total_sold')->default(0); // Counter barang terjual
            $table->string('image_url');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Orders Table
        Schema::create('orders', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('invoice_no')->unique();
            $table->foreignUuid('customer_id')->constrained('users')->onDelete('cascade');
            $table->foreignUuid('courier_id')->nullable()->constrained('users')->onDelete('set null');
            $table->decimal('subtotal', 12, 2);
            $table->decimal('shipping_fee', 12, 2);
            $table->decimal('total_price', 12, 2);
            
            $table->enum('payment_method', ['QRIS', 'COD']);
            $table->enum('payment_status', ['PENDING', 'PAID', 'FAILED'])->default('PENDING');
            $table->enum('order_status', ['PENDING', 'CONFIRMED', 'PROCESSING', 'SHIPPED', 'DELIVERED', 'CANCELLED'])->default('PENDING');
            $table->boolean('min_order_satisfied')->default(true);
            
            $table->text('delivery_address');
            $table->decimal('delivery_lat', 10, 7);
            $table->decimal('delivery_lng', 10, 7);
            $table->text('notes')->nullable();
            $table->string('proof_image_url')->nullable();
            $table->timestamps();
        });

        // Order Items Table
        Schema::create('order_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('order_id')->constrained('orders')->onDelete('cascade');
            $table->foreignUuid('product_id')->constrained('products')->onDelete('cascade');
            $table->integer('quantity');
            $table->decimal('price', 12, 2);
            $table->timestamps();
        });

        // Courier Location Logs Table
        Schema::create('courier_location_logs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('order_id')->constrained('orders')->onDelete('cascade');
            $table->decimal('latitude', 10, 7);
            $table->decimal('longitude', 10, 7);
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('courier_location_logs');
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
        Schema::dropIfExists('products');
        Schema::dropIfExists('users');
    }
};

