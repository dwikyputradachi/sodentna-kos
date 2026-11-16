<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('rentals', fn (Blueprint $t) => $t->date('next_due_date')->nullable()->after('billing_anchor_day'));

        // Money = whole rupiah. Rent and deposit are kept apart so deposits never count as rental income.
        Schema::create('invoices', function (Blueprint $t) {
            $t->id();
            $t->foreignId('property_id')->constrained()->cascadeOnDelete();
            $t->foreignId('rental_id')->constrained()->restrictOnDelete();
            $t->string('number', 30)->nullable()->unique();
            $t->string('kind', 10); // first | monthly | daily
            $t->date('period_start');
            $t->date('period_end')->nullable();
            $t->date('due_date')->index();
            $t->unsignedBigInteger('rent_amount');
            $t->unsignedBigInteger('deposit_amount')->default(0);
            $t->unsignedBigInteger('total_amount');
            $t->string('status', 10)->default('unpaid')->index(); // unpaid | paid | void
            $t->timestamp('paid_at')->nullable();
            $t->string('void_reason')->nullable();
            $t->timestamps();
            $t->unique(['rental_id', 'kind', 'period_start']); // never bill the same period twice
        });

        Schema::create('payments', function (Blueprint $t) {
            $t->id();
            $t->foreignId('property_id')->constrained()->cascadeOnDelete();
            $t->foreignId('invoice_id')->constrained()->restrictOnDelete();
            $t->foreignId('rental_id')->constrained()->restrictOnDelete();
            $t->unsignedBigInteger('amount');
            $t->unsignedBigInteger('rent_amount');
            $t->unsignedBigInteger('deposit_amount')->default(0);
            $t->string('method', 10); // cash | transfer
            $t->date('paid_on');
            $t->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $t->string('note', 200)->nullable();
            $t->timestamps();
        });

        Schema::create('receipts', function (Blueprint $t) {
            $t->id();
            $t->foreignId('property_id')->constrained()->cascadeOnDelete();
            $t->foreignId('payment_id')->unique()->constrained()->restrictOnDelete();
            $t->string('number', 30)->unique();
            $t->string('token', 40)->unique(); // unguessable public link, no login needed
            $t->date('issued_on');
            $t->timestamps();
        });

        // Audit of every WhatsApp action. 'opened' = the owner opened WhatsApp; it is NOT proof of delivery.
        Schema::create('message_logs', function (Blueprint $t) {
            $t->id();
            $t->foreignId('property_id')->constrained()->cascadeOnDelete();
            $t->foreignId('tenant_id')->nullable()->constrained()->nullOnDelete();
            $t->foreignId('invoice_id')->nullable()->constrained()->nullOnDelete();
            $t->string('kind', 15); // reminder | due | overdue | receipt
            $t->string('channel', 20)->default('whatsapp_link'); // whatsapp_link | whatsapp_bot (later)
            $t->string('status', 10)->default('opened'); // opened | sent | failed
            $t->string('to_phone', 20);
            $t->text('body');
            $t->string('error')->nullable();
            $t->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('message_logs');
        Schema::dropIfExists('receipts');
        Schema::dropIfExists('payments');
        Schema::dropIfExists('invoices');
        Schema::table('rentals', fn (Blueprint $t) => $t->dropColumn('next_due_date'));
    }
};
