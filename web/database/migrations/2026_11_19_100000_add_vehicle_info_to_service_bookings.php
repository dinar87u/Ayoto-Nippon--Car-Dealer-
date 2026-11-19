<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('service_bookings', function (Blueprint $table) {
            $table->foreignId('variant_id')->nullable()->after('branch_id')->constrained()->nullOnDelete();
            $table->foreignId('color_id')->nullable()->after('variant_id')->constrained()->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('service_bookings', function (Blueprint $table) {
            $table->dropConstrainedForeignId('color_id');
            $table->dropConstrainedForeignId('variant_id');
        });
    }
};
