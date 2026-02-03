<?php

use Hyperf\Database\Schema\Schema;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Migrations\Migration;
use Hyperf\DbConnection\Db;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('account_withdraw', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->uuid('account_id');
            $table->string('method', 20);
            $table->decimal('amount', 15, 2)->unsigned();

            $table->boolean('scheduled')->default(false);
            $table->dateTime('scheduled_for')->nullable();

            $table->boolean('done')->default(false);
            $table->boolean('error')->default(false);
            $table->string('error_reason')->nullable();

            $table->dateTime('processed_at')->nullable();
            $table->timestamps();

            $table->index('account_id');
            $table->index(['scheduled', 'scheduled_for']);

            $table->foreign('account_id')
                ->references('id')
                ->on('account')
                ->onDelete('cascade');
        });

        Db::statement("
            ALTER TABLE account_withdraw
            ADD CONSTRAINT chk_withdraw_amount_positive
            CHECK (amount > 0)
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('account_withdraws');
    }
};
