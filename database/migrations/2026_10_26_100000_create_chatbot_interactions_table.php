<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chatbot_interactions', function (Blueprint $table) {
            $table->id();
            $table->string('session_id')->index();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('user_message');
            $table->text('bot_response');
            $table->string('detected_intent')->nullable();
            $table->json('retrieved_sources')->nullable();
            $table->decimal('confidence_score', 4, 3)->default(0.000);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chatbot_interactions');
    }
};
