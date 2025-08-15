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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('username');
            $table->string('email')->unique();
            $table->string('avatar')->nullable();
            $table->string('color')->default($this->generateColor());
            $table->string('address')->nullable();
            $table->string('street')->nullable();
            $table->string('city')->nullable();
            $table->string('country')->nullable();
            $table->string('phone')->nullable();
            $table->boolean('status')->default(true); //active
            $table->boolean('is_online')->nullable()->default(0);
            $table->timestamp('last_seen_at')->nullable()->default(now());
            $table->timestamp('email_verified_at')->nullable();

            $table->boolean('email_verify')->default(false);
            $table->boolean('phone_verify')->default(false);
            $table->string('code')->nullable();
            $table->timestamp('code_expires_at')->nullable();
            $table->string('mode')->default("SYSTEM"); /// dark or white or system
            $table->boolean('auth_2_factory')->default(false); /// boleen true if login
            $table->string('telegram_id')->nullable();
            $table->string('google_id')->nullable();
            $table->string('github_id')->nullable();
            $table->string('facebook_id')->nullable();


            $table->string('password');
            $table->rememberToken();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }

    private function generateColor()
    {
        $colors = ['ff9d00', '00ad65', '00b8c7', '008aeb', '0060ff', '6c00ff', 'fd00ff', 'ff0020', 'ff7d6e', 'ff7724', 'ee8700', '00bad8', '000000', '254abd', 'c61480', '00baff', '6a6aba', '3cbfdc', 'ff60bb'];

        $XColor = $colors[array_rand($colors)];

        return  $XColor;
    }
};
