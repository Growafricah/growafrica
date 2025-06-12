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
        Schema::create('users', function (Blueprint $table) {

            $table->uuid('id')->primary();
            $table->string('pic')->nullable();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('store_category');
            $table->enum('role', ['admin','super_admin','buyer','seller']);
            $table->enum('gender', ['male','female']);
            $table->string('phone')->nullable();
            $table->string('email')->unique()->nullable();

            $table->string('store_link')->nullable();
            $table->string('country')->nullable();
            $table->string('address')->nullable();

            $table->string('business_name')->nullable();
            $table->string('business_state')->nullable();
            $table->string('business_city')->nullable();
            $table->string('id_doc')->nullable();
            $table->enum('kyc_status', ['pending','applied','approved','rejected']);

            $table->string('verification_code')->nullable();
            $table->string('verification_expiry')->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->boolean('store_status')->nullable()->default(true);
            $table->boolean('status')->nullable()->default(true);
            $table->rememberToken();
            $table->timestamps();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
