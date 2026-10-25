<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('job_title')->nullable()->after('email');
            $table->string('phone', 40)->nullable()->after('job_title');
            $table->string('avatar_color', 20)->default('#84CC16')->after('phone');
            $table->boolean('is_active')->default(true)->after('avatar_color');
            $table->timestamp('last_login_at')->nullable()->after('is_active');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['job_title', 'phone', 'avatar_color', 'is_active', 'last_login_at']);
        });
    }
};
