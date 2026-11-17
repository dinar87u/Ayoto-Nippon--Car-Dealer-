<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->char('nik', 16)->nullable()->change();
            $table->text('alamat')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->char('nik', 16)->nullable(false)->change();
            $table->text('alamat')->nullable(false)->change();
        });
    }
};
