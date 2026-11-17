<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('store_settings', function (Blueprint $table) {
            $table->id();
            $table->string('store_name')->default('Toko Sembako Modern');
            $table->text('store_address');
            $table->decimal('store_latitude', 10, 8);
            $table->decimal('store_longitude', 11, 8);
            $table->time('open_time')->default('07:00:00');
            $table->time('close_time')->default('21:00:00');
            $table->boolean('is_open')->default(true);
            $table->integer('shipping_rate_per_km')->default(2500);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('store_settings');
    }
};
