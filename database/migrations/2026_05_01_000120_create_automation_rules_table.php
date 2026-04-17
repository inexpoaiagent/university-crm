<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('automation_rules')) {
            return;
        }

        Schema::create('automation_rules', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('tenant_id');
            $table->string('name', 150);
            $table->string('trigger_key', 80);
            $table->text('conditions_json')->nullable();
            $table->text('actions_json')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->foreign('tenant_id', 'fk_automation_rules_tenant')->references('id')->on('tenants')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('automation_rules');
    }
};
