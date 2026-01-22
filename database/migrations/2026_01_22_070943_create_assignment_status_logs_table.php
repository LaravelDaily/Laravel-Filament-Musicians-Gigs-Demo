<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assignment_status_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('gig_assignment_id')->constrained()->cascadeOnDelete();

            // Status change tracking
            $table->string('old_status', 50)->nullable();
            $table->string('new_status', 50);
            $table->text('reason')->nullable();

            // Who made the change (null if system/musician action)
            $table->foreignId('changed_by_user_id')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamp('created_at')->nullable();

            // Indexes
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assignment_status_logs');
    }
};
