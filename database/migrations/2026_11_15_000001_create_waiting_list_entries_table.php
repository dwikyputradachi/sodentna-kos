<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('waiting_list_entries', function (Blueprint $t) {
            $t->id();
            $t->foreignId('property_id')->constrained()->cascadeOnDelete();
            $t->string('code', 20)->unique();
            $t->string('name', 100);
            $t->string('phone', 20)->index();
            $t->foreignId('room_type_id')->nullable()->constrained()->nullOnDelete();
            $t->unsignedTinyInteger('occupants')->default(1);
            $t->date('desired_start');
            $t->string('notes', 300)->nullable();
            $t->string('status', 20)->default('waiting')->index(); // waiting|offered|converted|cancelled|expired
            $t->timestamp('offered_at')->nullable();
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('waiting_list_entries');
    }
};
