<?php

use App\Enums\GigStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gigs', function (Blueprint $table) {
            $table->id();
            $table->string('name');

            // Date and times
            $table->date('date');
            $table->time('call_time');
            $table->time('performance_time')->nullable();
            $table->time('end_time')->nullable();

            // Venue information
            $table->string('venue_name');
            $table->text('venue_address');

            // Client contact (optional)
            $table->string('client_contact_name')->nullable();
            $table->string('client_contact_phone', 50)->nullable();
            $table->string('client_contact_email')->nullable();

            // Additional details
            $table->text('dress_code')->nullable();
            $table->text('notes')->nullable();
            $table->string('pay_info')->nullable();

            // Relationships
            $table->foreignId('region_id')->nullable()->constrained()->nullOnDelete();

            // Status
            $table->string('status', 50)->default(GigStatus::Draft->value);

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('date');
            $table->index('status');
            $table->index(['date', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gigs');
    }
};
