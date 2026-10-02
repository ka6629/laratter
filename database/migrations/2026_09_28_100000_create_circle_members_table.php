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
        Schema::create('circle_members', function (Blueprint $table) {
            $table->id();

            // サークルの持ち主
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            // サークルに入れたフォロワー
            $table->foreignId('member_id')->constrained('users')->cascadeOnDelete();
            $table->unique(['user_id', 'member_id']);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('circle_members');
    }
};
