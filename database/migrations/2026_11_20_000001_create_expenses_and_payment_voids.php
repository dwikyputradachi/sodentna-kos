<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('expenses', function (Blueprint $t) {
            $t->id();
            $t->foreignId('property_id')->constrained()->cascadeOnDelete();
            $t->string('request_id', 40)->nullable();      // one per form render: a double click cannot post twice
            $t->date('spent_on')->index();
            $t->string('category', 40);
            $t->unsignedBigInteger('amount');
            $t->string('method', 10);                       // cash | transfer
            $t->string('description', 200)->nullable();
            $t->foreignId('room_id')->nullable()->constrained()->nullOnDelete();
            $t->string('receipt_path')->nullable();         // private storage
            $t->timestamp('voided_at')->nullable();
            $t->string('void_reason', 200)->nullable();
            $t->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $t->timestamps();
            $t->unique(['property_id', 'request_id']);
        });

        // Payments are never deleted: a wrong one is cancelled (kept for audit) and the invoice becomes payable again.
        Schema::table('payments', function (Blueprint $t) {
            $t->timestamp('voided_at')->nullable();
            $t->string('void_reason', 200)->nullable();
            $t->foreignId('voided_by')->nullable()->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $t) {
            $t->dropConstrainedForeignId('voided_by');
            $t->dropColumn(['voided_at', 'void_reason']);
        });
        Schema::dropIfExists('expenses');
    }
};
