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
        Schema::table('diagnoses', function (Blueprint $table): void {
            $table->string('status', 20)
                ->default('pending')
                ->after('image_path');
            $table->text('failure_reason')->nullable()->after('treatment');
            $table->timestamp('attempted_at')->nullable()->after('failure_reason');
            $table->timestamp('completed_at')->nullable()->after('attempted_at');

            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('diagnoses', function (Blueprint $table): void {
            $table->dropIndex(['status']);
            $table->dropColumn([
                'status',
                'failure_reason',
                'attempted_at',
                'completed_at',
            ]);
        });
    }
};
