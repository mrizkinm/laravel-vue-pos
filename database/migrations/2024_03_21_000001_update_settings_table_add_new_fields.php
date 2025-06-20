<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('settings', function (Blueprint $table) {
            // Rename columns
            $table->renameColumn('company_email', 'email');
            $table->renameColumn('company_phone', 'phone');
            $table->renameColumn('company_address', 'address');

            // Drop unused columns
            $table->dropColumn([
                'company_city',
                'company_state',
                'company_postal_code',
                'company_country',
                'timezone',
                'date_format',
                'favicon'
            ]);

            // Add new columns
            $table->integer('low_stock_threshold')->default(10);
            $table->integer('default_payment_term')->default(30);
            $table->text('receipt_footer')->nullable();
            $table->boolean('show_tax_number_on_receipt')->default(false);
        });
    }

    public function down()
    {
        Schema::table('settings', function (Blueprint $table) {
            // Restore original column names
            $table->renameColumn('email', 'company_email');
            $table->renameColumn('phone', 'company_phone');
            $table->renameColumn('address', 'company_address');

            // Restore dropped columns
            $table->string('company_city')->nullable();
            $table->string('company_state')->nullable();
            $table->string('company_postal_code')->nullable();
            $table->string('company_country')->nullable();
            $table->string('timezone')->nullable();
            $table->string('date_format')->nullable();
            $table->string('favicon')->nullable();

            // Remove new columns
            $table->dropColumn([
                'low_stock_threshold',
                'default_payment_term',
                'receipt_footer',
                'show_tax_number_on_receipt'
            ]);
        });
    }
}; 