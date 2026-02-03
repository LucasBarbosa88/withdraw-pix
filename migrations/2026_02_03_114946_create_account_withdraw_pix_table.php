<?php

use Hyperf\Database\Schema\Schema;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('account_withdraw_pix', function (Blueprint $table) {
            $table->uuid('account_withdraw_id')->primary();
            $table->string('type', 20);
            $table->string('pix_key', 255);

            $table->foreign('account_withdraw_id')
                ->references('id')
                ->on('account_withdraw')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('account_withdraw_pix');
    }
};
