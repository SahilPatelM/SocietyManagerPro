<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('societies', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('address')->nullable();
            $table->string('city')->nullable();
            $table->string('registration_number')->nullable();
            $table->decimal('opening_balance', 14, 2)->default(0);
            $table->string('logo')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('blocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('society_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('code')->nullable();
            $table->timestamps();
        });

        Schema::create('houses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('society_id')->constrained()->cascadeOnDelete();
            $table->foreignId('block_id')->nullable()->constrained()->nullOnDelete();
            $table->string('house_number');
            $table->enum('status', ['occupied', 'vacant'])->default('occupied');
            $table->foreignId('owner_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->decimal('outstanding_amount', 14, 2)->default(0);
            $table->decimal('paid_amount', 14, 2)->default(0);
            $table->date('last_payment_date')->nullable();
            $table->string('qr_code')->nullable();
            $table->timestamps();
            $table->unique(['society_id', 'house_number']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('house_id')->nullable()->after('email')->constrained()->nullOnDelete();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('society_id')->nullable()->after('id')->constrained()->nullOnDelete();
            $table->string('mobile', 15)->nullable()->unique()->after('email');
            $table->string('alternate_mobile', 15)->nullable();
            $table->string('block_wing')->nullable();
            $table->text('address')->nullable();
            $table->string('profile_photo')->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->string('locale', 5)->default('en');
            $table->string('fcm_token')->nullable();
            $table->string('emergency_contact_name')->nullable();
            $table->string('emergency_mobile', 15)->nullable();
            $table->timestamp('mobile_verified_at')->nullable();
        });

        Schema::create('otp_verifications', function (Blueprint $table) {
            $table->id();
            $table->string('mobile', 15);
            $table->string('otp', 6);
            $table->timestamp('expires_at');
            $table->boolean('is_used')->default(false);
            $table->timestamps();
        });

        Schema::create('family_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('relation');
            $table->string('mobile', 15)->nullable();
            $table->unsignedTinyInteger('age')->nullable();
            $table->timestamps();
        });

        Schema::create('vehicles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('vehicle_type');
            $table->string('car_number')->nullable();
            $table->string('bike_number')->nullable();
            $table->string('vehicle_image')->nullable();
            $table->timestamps();
        });

        Schema::create('financial_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('society_id')->constrained()->cascadeOnDelete();
            $table->enum('type', ['income', 'expense']);
            $table->string('category');
            $table->string('subcategory')->nullable();
            $table->decimal('amount', 14, 2);
            $table->date('transaction_date');
            $table->string('payment_method')->nullable();
            $table->string('reference_number')->nullable();
            $table->text('description')->nullable();
            $table->foreignId('house_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index(['society_id', 'type', 'transaction_date']);
        });

        Schema::create('transaction_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('financial_transaction_id')->constrained()->cascadeOnDelete();
            $table->string('file_path');
            $table->string('file_type')->nullable();
            $table->timestamps();
        });

        Schema::create('maintenance_bills', function (Blueprint $table) {
            $table->id();
            $table->foreignId('society_id')->constrained()->cascadeOnDelete();
            $table->foreignId('house_id')->constrained()->cascadeOnDelete();
            $table->string('bill_number')->unique();
            $table->string('month_year', 7);
            $table->decimal('maintenance_amount', 14, 2);
            $table->decimal('late_fee', 14, 2)->default(0);
            $table->date('due_date');
            $table->enum('status', ['pending', 'partial', 'paid', 'overdue'])->default('pending');
            $table->decimal('paid_amount', 14, 2)->default(0);
            $table->timestamps();
        });

        Schema::create('maintenance_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('maintenance_bill_id')->constrained()->cascadeOnDelete();
            $table->foreignId('house_id')->constrained()->cascadeOnDelete();
            $table->decimal('amount', 14, 2);
            $table->enum('payment_method', ['cash', 'upi', 'bank_transfer']);
            $table->string('receipt_number')->nullable();
            $table->string('receipt_path')->nullable();
            $table->date('payment_date');
            $table->foreignId('received_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('announcements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('society_id')->constrained()->cascadeOnDelete();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('type', ['text', 'image', 'pdf', 'emergency'])->default('text');
            $table->string('image')->nullable();
            $table->string('attachment')->nullable();
            $table->boolean('is_emergency')->default(false);
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();
        });

        Schema::create('announcement_targets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('announcement_id')->constrained()->cascadeOnDelete();
            $table->enum('target_type', ['all', 'house', 'block']);
            $table->unsignedBigInteger('target_id')->nullable();
            $table->timestamps();
        });

        Schema::create('user_notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->text('body')->nullable();
            $table->string('type');
            $table->json('data')->nullable();
            $table->boolean('is_read')->default(false);
            $table->enum('delivery_status', ['pending', 'sent', 'failed'])->default('pending');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });

        Schema::create('visitors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('society_id')->constrained()->cascadeOnDelete();
            $table->foreignId('house_id')->constrained()->cascadeOnDelete();
            $table->string('visitor_name');
            $table->string('mobile', 15)->nullable();
            $table->string('vehicle_number')->nullable();
            $table->timestamp('entry_time');
            $table->timestamp('exit_time')->nullable();
            $table->foreignId('logged_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('complaints', function (Blueprint $table) {
            $table->id();
            $table->foreignId('society_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('house_id')->nullable()->constrained()->nullOnDelete();
            $table->string('complaint_number')->unique();
            $table->enum('category', ['water', 'electricity', 'security', 'parking', 'cleaning', 'other']);
            $table->string('title');
            $table->text('description');
            $table->enum('status', ['pending', 'in_progress', 'resolved'])->default('pending');
            $table->text('admin_remarks')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();
        });

        Schema::create('complaint_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('complaint_id')->constrained()->cascadeOnDelete();
            $table->string('file_path');
            $table->string('file_type')->nullable();
            $table->timestamps();
        });

        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('society_id')->constrained()->cascadeOnDelete();
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('title');
            $table->enum('category', ['rules', 'meeting_minutes', 'agm', 'audit', 'receipt', 'other']);
            $table->string('file_path');
            $table->string('file_type')->nullable();
            $table->unsignedBigInteger('file_size')->nullable();
            $table->timestamps();
        });

        Schema::create('parking_slots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('society_id')->constrained()->cascadeOnDelete();
            $table->string('slot_number');
            $table->enum('status', ['available', 'occupied'])->default('available');
            $table->timestamps();
            $table->unique(['society_id', 'slot_number']);
        });

        Schema::create('parking_allocations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parking_slot_id')->constrained()->cascadeOnDelete();
            $table->foreignId('house_id')->constrained()->cascadeOnDelete();
            $table->string('vehicle_number');
            $table->date('allocated_from');
            $table->date('allocated_until')->nullable();
            $table->timestamps();
        });

        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('action');
            $table->string('model_type')->nullable();
            $table->unsignedBigInteger('model_id')->nullable();
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
        Schema::dropIfExists('parking_allocations');
        Schema::dropIfExists('parking_slots');
        Schema::dropIfExists('documents');
        Schema::dropIfExists('complaint_attachments');
        Schema::dropIfExists('complaints');
        Schema::dropIfExists('visitors');
        Schema::dropIfExists('user_notifications');
        Schema::dropIfExists('announcement_targets');
        Schema::dropIfExists('announcements');
        Schema::dropIfExists('maintenance_payments');
        Schema::dropIfExists('maintenance_bills');
        Schema::dropIfExists('transaction_attachments');
        Schema::dropIfExists('financial_transactions');
        Schema::dropIfExists('vehicles');
        Schema::dropIfExists('family_members');
        Schema::dropIfExists('otp_verifications');
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['house_id']);
            $table->dropColumn('house_id');
        });
        Schema::dropIfExists('houses');
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['society_id']);
            $table->dropColumn([
                'society_id', 'mobile', 'alternate_mobile', 'block_wing',
                'address', 'profile_photo', 'status', 'locale', 'fcm_token',
                'emergency_contact_name', 'emergency_mobile', 'mobile_verified_at',
            ]);
        });
        Schema::dropIfExists('blocks');
        Schema::dropIfExists('societies');
    }
};
