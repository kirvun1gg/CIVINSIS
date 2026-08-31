<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('translations', function (Blueprint $table) {
            $table->id();
            $table->string('translatable_type', 150);
            $table->unsignedBigInteger('translatable_id');
            $table->string('locale', 5);
            $table->string('field', 50);
            $table->longText('translated_text');
            $table->string('content_hash', 64);
            $table->timestamps();

            $table->unique(['translatable_type', 'translatable_id', 'locale', 'field'], 'translations_unicas');
            $table->index(['translatable_type', 'translatable_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('translations');
    }
};
