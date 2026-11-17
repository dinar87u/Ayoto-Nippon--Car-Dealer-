<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('trade_ins');
    }

    public function down(): void
    {
        // Fitur Trade-In dihapus permanen.
    }
};
