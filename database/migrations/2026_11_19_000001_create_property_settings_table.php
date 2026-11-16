<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('property_settings', function (Blueprint $t) {
            $t->id();
            $t->foreignId('property_id')->constrained()->cascadeOnDelete();
            $t->string('key', 40);
            $t->text('value')->nullable();
            $t->timestamps();
            $t->unique(['property_id', 'key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('property_settings');
    }
};
