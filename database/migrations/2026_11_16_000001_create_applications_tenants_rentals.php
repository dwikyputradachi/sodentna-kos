<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('tenants', function (Blueprint $t) {
            $t->id();
            $t->foreignId('property_id')->constrained()->cascadeOnDelete();
            $t->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $t->string('name', 100);
            $t->string('phone', 20);
            $t->string('birth_place', 80)->nullable();
            $t->date('birth_date')->nullable();
            $t->text('address')->nullable();
            $t->text('id_number')->nullable(); // encrypted via model cast
            $t->string('job', 80)->nullable();
            $t->string('institution', 120)->nullable();
            $t->string('emergency_name', 100)->nullable();
            $t->string('emergency_relation', 50)->nullable();
            $t->string('emergency_phone', 20)->nullable();
            $t->text('emergency_address')->nullable();
            $t->string('id_doc_path')->nullable(); // private disk
            $t->string('status', 20)->default('applicant')->index(); // applicant|active|pending_checkout|former
            $t->timestamps();
            $t->unique(['property_id', 'phone']);
        });

        Schema::create('rental_applications', function (Blueprint $t) {
            $t->id();
            $t->foreignId('property_id')->constrained()->cascadeOnDelete();
            $t->string('code', 20)->unique();
            $t->foreignId('room_type_id')->constrained()->restrictOnDelete();
            $t->string('mode', 10); // monthly|daily
            $t->unsignedTinyInteger('occupants')->default(1);
            $t->date('start_date');
            $t->date('end_date')->nullable();
            $t->string('payment_method', 20);
            $t->string('name', 100);
            $t->string('phone', 20)->index();
            $t->string('birth_place', 80);
            $t->date('birth_date');
            $t->text('address');
            $t->text('id_number'); // encrypted via model cast
            $t->string('job', 80);
            $t->string('institution', 120);
            $t->string('emergency_name', 100);
            $t->string('emergency_relation', 50);
            $t->string('emergency_phone', 20);
            $t->text('emergency_address');
            $t->string('id_doc_path');
            $t->timestamp('consented_at');
            $t->string('status', 20)->default('pending')->index(); // pending|approved|rejected
            $t->foreignId('room_id')->nullable()->constrained()->nullOnDelete();
            $t->foreignId('tenant_id')->nullable()->constrained()->nullOnDelete();
            $t->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $t->timestamp('reviewed_at')->nullable();
            $t->string('reject_reason')->nullable();
            $t->timestamps();
        });

        // rate/deposit are snapshots so later price changes never alter existing contracts.
        Schema::create('rentals', function (Blueprint $t) {
            $t->id();
            $t->foreignId('property_id')->constrained()->cascadeOnDelete();
            $t->foreignId('tenant_id')->constrained()->restrictOnDelete();
            $t->foreignId('room_id')->constrained()->restrictOnDelete();
            $t->foreignId('application_id')->nullable()->constrained('rental_applications')->nullOnDelete();
            $t->string('mode', 10);
            $t->unsignedTinyInteger('occupants')->default(1);
            $t->date('start_date');
            $t->date('end_date')->nullable();
            $t->unsignedBigInteger('rate');
            $t->unsignedBigInteger('deposit_amount')->default(0);
            $t->unsignedTinyInteger('billing_anchor_day')->nullable();
            $t->string('status', 20)->default('reserved')->index(); // reserved|active|pending_checkout|ended
            $t->timestamps();
        });

        Schema::create('account_invitations', function (Blueprint $t) {
            $t->id();
            $t->foreignId('user_id')->constrained()->cascadeOnDelete();
            $t->string('token_hash', 64)->unique(); // sha256 of the one-time token; the token itself is never stored
            $t->timestamp('expires_at');
            $t->timestamp('used_at')->nullable();
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('account_invitations');
        Schema::dropIfExists('rentals');
        Schema::dropIfExists('rental_applications');
        Schema::dropIfExists('tenants');
    }
};
