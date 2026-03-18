<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('currencies')->insert([
            'uid' => uniqid(),
            'code' => 'BDT',
            'name' => 'Bangladeshi Taka',
            'symbol' => '৳',
            'decimal_places' => 2,
            'is_active' => true,
            'is_default' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('currencies')->insert([
            'uid' => uniqid(),
            'code' => 'USD',
            'name' => 'USA Dollar',
            'symbol' => '$',
            'decimal_places' => 2,
            'is_active' => true,
            'is_default' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
