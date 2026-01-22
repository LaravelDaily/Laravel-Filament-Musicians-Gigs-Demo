<?php

use App\Enums\AssignmentStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gig_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('gig_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('instrument_id')->constrained()->cascadeOnDelete();

            // Status
            $table->string('status', 50)->default(AssignmentStatus::Pending->value);

            // Assignment-specific details
            $table->decimal('pay_amount', 10, 2)->nullable();
            $table->text('notes')->nullable();

            // Response tracking
            $table->timestamp('responded_at')->nullable();
            $table->text('subout_reason')->nullable();
            $table->text('decline_reason')->nullable();

            $table->timestamps();

            // Unique constraint - one assignment per musician per gig
            $table->unique(['gig_id', 'user_id']);

            // Indexes
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gig_assignments');
    }
};
