<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->foreignId('store_id')->nullable()->after('id')->constrained('stores')->nullOnDelete();
            $table->index(['store_id', 'date']);
        });

        DB::table('attendances')
            ->join('employees', 'employees.id', '=', 'attendances.employee_id')
            ->whereNotNull('employees.store_id')
            ->update(['attendances.store_id' => DB::raw('employees.store_id')]);
    }

    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropIndex(['store_id', 'date']);
            $table->dropConstrainedForeignId('store_id');
        });
    }
};
