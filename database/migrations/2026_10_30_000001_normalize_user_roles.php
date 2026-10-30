<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\{DB, Schema};

return new class extends Migration {
    public function up(): void
    {
        // Unknown legacy roles receive the least privileged role.
        DB::table('users')->whereNotIn('role', ['admin', 'manager', 'user'])->update(['role' => 'user']);
        Schema::table('users', fn (Blueprint $table) => $table->string('role')->default('user')->change());
    }

    public function down(): void
    {
        DB::table('users')->where('role', 'user')->update(['role' => 'customer']);
        Schema::table('users', fn (Blueprint $table) => $table->string('role')->default('customer')->change());
    }
};
