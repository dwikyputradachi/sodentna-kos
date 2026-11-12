<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('properties', function (Blueprint $t) {
            $t->id();
            $t->string('name');
            $t->string('slug')->unique();
            $t->text('address')->nullable();
            $t->string('phone', 20)->nullable();
            $t->string('timezone', 40)->default('Asia/Jakarta');
            $t->boolean('is_active')->default(true);
            $t->timestamps();
        });

        Schema::create('property_users', function (Blueprint $t) {
            $t->id();
            $t->foreignId('property_id')->constrained()->cascadeOnDelete();
            $t->foreignId('user_id')->constrained()->cascadeOnDelete();
            $t->timestamps();
            $t->unique(['property_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('property_users');
        Schema::dropIfExists('properties');
    }
};
