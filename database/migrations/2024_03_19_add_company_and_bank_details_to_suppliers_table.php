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
        Schema::table('suppliers', function (Blueprint $table) {
            $table->string('company')->after('name');
            $table->string('bank_name')->nullable()->after('tax_number');
            $table->string('bank_account')->nullable()->after('bank_name');
            $table->text('notes')->nullable()->after('bank_account');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('suppliers', function (Blueprint $table) {
            $table->dropColumn(['company', 'bank_name', 'bank_account', 'notes']);
        });
    }
}; 