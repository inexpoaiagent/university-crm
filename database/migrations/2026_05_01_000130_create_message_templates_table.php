<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('message_templates')) {
            return;
        }

        Schema::create('message_templates', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('tenant_id');
            $table->string('channel', 30)->default('notification');
            $table->string('name', 150);
            $table->string('subject', 190)->nullable();
            $table->longText('body');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->foreign('tenant_id', 'fk_message_templates_tenant')->references('id')->on('tenants')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('message_templates');
    }
};
