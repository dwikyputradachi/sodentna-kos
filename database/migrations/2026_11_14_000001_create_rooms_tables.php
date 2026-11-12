<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('floors', function (Blueprint $t) {
            $t->id();
            $t->foreignId('property_id')->constrained()->cascadeOnDelete();
            $t->string('name', 40);
            $t->unsignedSmallInteger('sort_order')->default(0);
            $t->timestamps();
            $t->unique(['property_id', 'name']);
        });

        // Money is stored as whole rupiah (integer). Monthly rate depends on number of occupants.
        Schema::create('room_types', function (Blueprint $t) {
            $t->id();
            $t->foreignId('property_id')->constrained()->cascadeOnDelete();
            $t->string('name', 60);
            $t->string('size', 30)->nullable();
            $t->unsignedTinyInteger('capacity')->default(1);
            $t->unsignedBigInteger('rate_one');
            $t->unsignedBigInteger('rate_two')->nullable();
            $t->unsignedBigInteger('deposit_amount')->default(500000);
            $t->boolean('allows_daily')->default(false);
            $t->unsignedBigInteger('daily_rate')->nullable();
            $t->timestamps();
            $t->unique(['property_id', 'name']);
        });

        Schema::create('rooms', function (Blueprint $t) {
            $t->id();
            $t->foreignId('property_id')->constrained()->cascadeOnDelete();
            $t->foreignId('floor_id')->constrained()->restrictOnDelete();
            $t->foreignId('room_type_id')->constrained()->restrictOnDelete();
            $t->string('code', 20);
            $t->string('status', 20)->default('available')->index();
            $t->text('notes')->nullable();
            $t->timestamps();
            $t->unique(['property_id', 'code']);
        });

        Schema::create('room_status_logs', function (Blueprint $t) {
            $t->id();
            $t->foreignId('property_id')->constrained()->cascadeOnDelete();
            $t->foreignId('room_id')->constrained()->cascadeOnDelete();
            $t->string('from_status', 20)->nullable();
            $t->string('to_status', 20);
            $t->string('note')->nullable();
            $t->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('room_status_logs');
        Schema::dropIfExists('rooms');
        Schema::dropIfExists('room_types');
        Schema::dropIfExists('floors');
    }
};
