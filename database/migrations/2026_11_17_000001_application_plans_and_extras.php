<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('rental_applications', function (Blueprint $t) {
            $t->string('name_2', 100)->nullable()->after('name');
            $t->string('id_doc_2_path')->nullable()->after('id_doc_path');
            $t->string('religion', 30)->nullable()->after('institution');
        });

        // Daily rentals use a shorter form, so these are no longer mandatory for every application.
        Schema::table('rental_applications', function (Blueprint $t) {
            $t->string('birth_place', 80)->nullable()->change();
            $t->date('birth_date')->nullable()->change();
            $t->string('job', 80)->nullable()->change();
            $t->string('institution', 120)->nullable()->change();
            $t->text('emergency_address')->nullable()->change();
        });

        Schema::table('tenants', fn (Blueprint $t) => $t->string('religion', 30)->nullable());
        Schema::table('rentals', fn (Blueprint $t) => $t->string('co_occupant_name', 100)->nullable());
    }

    public function down(): void
    {
        Schema::table('rentals', fn (Blueprint $t) => $t->dropColumn('co_occupant_name'));
        Schema::table('tenants', fn (Blueprint $t) => $t->dropColumn('religion'));
        Schema::table('rental_applications', fn (Blueprint $t) => $t->dropColumn(['name_2', 'id_doc_2_path', 'religion']));
    }
};
