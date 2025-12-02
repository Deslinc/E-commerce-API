<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Adds is_admin column to users table to distinguish admin users
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Add is_admin boolean column, default is false (regular user)
            // This allows us to restrict certain operations to admin users only
            $table->boolean('is_admin')->default(false)->after('password');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Remove the is_admin column if we rollback
            $table->dropColumn('is_admin');
        });
    }
};