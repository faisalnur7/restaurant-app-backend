<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('restaurant_settings', function (Blueprint $table) {
            $table->id();
            $table->string('restaurant_name')->nullable();
            $table->string('logo')->nullable();
            $table->string('favicon')->nullable();
            $table->string('contact_number')->nullable();
            $table->string('email')->nullable();
            $table->text('address')->nullable();
            $table->string('website')->nullable();
            $table->json('social_links')->nullable(); // store as {"facebook": "...", "instagram": "..."}
            $table->string('time_zone')->default('Asia/Dhaka');
            $table->string('language')->default('en');
            $table->string('opening_time')->nullable();
            $table->string('closing_time')->nullable();

            // 💰 Fee Settings
            $table->boolean('enable_tax')->default(false);
            $table->enum('tax_type', ['inclusive', 'exclusive'])->default('exclusive');
            $table->string('tax_name')->nullable();
            $table->decimal('tax_percentage', 5, 2)->default(0.00);
            $table->decimal('service_charge_percentage', 5, 2)->default(0.00);
            $table->decimal('delivery_charge', 8, 2)->default(0.00);
            $table->decimal('packaging_charge', 8, 2)->default(0.00);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('restaurant_settings');
    }
};
