<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('universities', function (Blueprint $table) {
            if (!Schema::hasColumn('universities', 'city')) {
                $table->string('city', 120)->nullable()->after('country');
            }
            if (!Schema::hasColumn('universities', 'institution_type')) {
                $table->string('institution_type', 40)->default('university')->after('city');
            }
        });
    }

    public function down(): void
    {
        Schema::table('universities', function (Blueprint $table) {
            if (Schema::hasColumn('universities', 'institution_type')) {
                $table->dropColumn('institution_type');
            }
            if (Schema::hasColumn('universities', 'city')) {
                $table->dropColumn('city');
            }
        });
    }
};

