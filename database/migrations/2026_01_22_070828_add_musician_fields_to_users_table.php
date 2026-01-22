<?php

use App\Enums\UserRole;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('role', 50)->default(UserRole::Musician->value)->after('remember_token');
            $table->string('phone', 50)->nullable()->after('role');
            $table->foreignId('region_id')->nullable()->constrained()->nullOnDelete()->after('phone');
            $table->text('notes')->nullable()->after('region_id');
            $table->boolean('is_active')->default(true)->after('notes');
            $table->softDeletes();

            $table->index('role');
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['region_id']);
            $table->dropIndex(['role']);
            $table->dropIndex(['is_active']);
            $table->dropSoftDeletes();
            $table->dropColumn(['role', 'phone', 'region_id', 'notes', 'is_active']);
        });
    }
};
