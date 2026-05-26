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
        Schema::table('tenants', function (Blueprint $table) {
            $table->string('upi_id')->nullable()->after('google_sheet_email');
            $table->string('upi_number')->nullable()->after('upi_id');
            $table->string('bank_name')->nullable()->after('upi_number');
            $table->string('bank_account_number')->nullable()->after('bank_name');
            $table->string('bank_ifsc')->nullable()->after('bank_account_number');
            $table->string('qr_code_path')->nullable()->after('bank_ifsc');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropColumn([
                'upi_id',
                'upi_number',
                'bank_name',
                'bank_account_number',
                'bank_ifsc',
                'qr_code_path'
            ]);
        });
    }
};
