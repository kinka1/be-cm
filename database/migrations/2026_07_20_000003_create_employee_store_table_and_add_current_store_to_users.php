<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employee_store', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->foreignId('store_id')->constrained('stores')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['employee_id', 'store_id']);
        });

        DB::table('employees')
            ->whereNotNull('store_id')
            ->orderBy('id')
            ->get(['id', 'store_id'])
            ->each(function ($employee): void {
                DB::table('employee_store')->updateOrInsert(
                    ['employee_id' => $employee->id, 'store_id' => $employee->store_id],
                    ['created_at' => now(), 'updated_at' => now()]
                );
            });

        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('current_store_id')->nullable()->after('employee_id')->constrained('stores')->nullOnDelete();
        });

        DB::table('users')
            ->join('employees', 'employees.id', '=', 'users.employee_id')
            ->whereNotNull('employees.store_id')
            ->update(['users.current_store_id' => DB::raw('employees.store_id')]);
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('current_store_id');
        });

        Schema::dropIfExists('employee_store');
    }
};
