<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            if (!Schema::hasColumn('users', 'parent_user_id')) {
                $table->unsignedBigInteger('parent_user_id')->nullable()->after('tenant_id');
                $table->foreign('parent_user_id', 'fk_users_parent')->references('id')->on('users')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            if (Schema::hasColumn('users', 'parent_user_id')) {
                $table->dropForeign('fk_users_parent');
                $table->dropColumn('parent_user_id');
            }
        });
    }
};
