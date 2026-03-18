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
        Schema::create('workspaces', function (Blueprint $table) {
            $table->id();
            $table->string('uid')->unique(); // replaces slug usage

            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete;

            $table->string('name');
            $table->string('currency_code');
            $table->string('time_zone')->default('Asia/Dhaka');
            $table->string('created_by_user_id');

            $table->timestamps();

            $table->foreign('currency_code')->references('code')->on('currencies');
            $table->foreign('created_by_user_id')->references('id')->on('users');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('workspaces');
    }
};
