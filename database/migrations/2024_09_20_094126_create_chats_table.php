<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('chats', function (Blueprint $table) {
            $table->id();
            $table->bigInteger("sender", false, true);
            $table->bigInteger("receiver", false, true);
            $table->text("message");
            $table->text("type")->nullable();

            $table->foreignId('chats_channels_id')->nullable()->constrained('chats_channels')->cascadeOnDelete();
            $table->boolean('is_read')->default(false);
            $table->boolean('is_mute')->default(false);
            $table->boolean('is_blocked')->default(false);
            $table->timestamps();
            $table->foreign('sender')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('receiver')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chats');
    }
};
