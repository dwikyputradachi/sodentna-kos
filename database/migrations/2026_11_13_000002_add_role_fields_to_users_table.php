<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $t) {
            $t->string('phone', 20)->nullable()->unique()->after('email');
            $t->string('role', 20)->default('tenant')->index()->after('phone');
            $t->boolean('is_active')->default(true)->after('role');
        });

        // Applicants may not have an email; invited tenants have no password until activation.
        Schema::table('users', function (Blueprint $t) {
            $t->string('email')->nullable()->change();
            $t->string('password')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $t) {
            $t->dropColumn(['phone', 'role', 'is_active']);
        });
    }
};
