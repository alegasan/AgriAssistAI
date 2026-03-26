<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        $columns = array_values(array_filter([
            Schema::hasColumn('users', 'latitude') ? 'latitude' : null,
            Schema::hasColumn('users', 'longitude') ? 'longitude' : null,
            Schema::hasColumn('users', 'location_name') ? 'location_name' : null,
        ]));

        if ($columns !== []) {
            Schema::table('users', function (Blueprint $table) use ($columns) {
                $table->dropColumn($columns);
            });
        }
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'latitude')) {
                $table->decimal('latitude', 10, 7)->nullable()->after('email');
            }

            if (!Schema::hasColumn('users', 'longitude')) {
                $table->decimal('longitude', 10, 7)->nullable()->after('latitude');
            }

            if (!Schema::hasColumn('users', 'location_name')) {
                $table->string('location_name')->nullable()->after('longitude');
            }
        });
    }
};
