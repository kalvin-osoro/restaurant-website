<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('consultations', function (Blueprint $t) {
            $t->id(); $t->uuid('reference')->unique();
            foreach (['full_name', 'organization', 'email', 'phone', 'details'] as $field) $t->text($field)->nullable();
            $t->string('nature_of_inquiry'); $t->string('preferred_office')->nullable(); $t->timestamps();
        });
        Schema::create('notification_logs', function (Blueprint $t) {
            $t->id(); $t->foreignId('consultation_id')->constrained()->cascadeOnDelete();
            $t->string('channel'); $t->string('status')->default('pending')->index();
            $t->unsignedInteger('attempts')->default(0); $t->string('provider_reference')->nullable();
            $t->string('error_code')->nullable(); $t->timestamp('sent_at')->nullable(); $t->timestamps();
            $t->unique(['consultation_id', 'channel']);
        });
        Schema::create('tbl_request_logs', function (Blueprint $t) {
            $t->id(); $t->uuid('request_id')->unique(); $t->string('method', 16);
            $t->string('route')->nullable(); $t->string('ip_hash', 64)->nullable();
            $t->unsignedSmallInteger('status')->nullable(); $t->unsignedInteger('duration_ms')->nullable(); $t->timestamps();
        });
        Schema::create('failed_jobs', function (Blueprint $t) {
            $t->id(); $t->string('uuid')->unique(); $t->text('connection'); $t->text('queue');
            $t->longText('payload'); $t->longText('exception'); $t->timestamp('failed_at')->useCurrent();
        });
    }
    public function down(): void {
        foreach (['failed_jobs', 'tbl_request_logs', 'notification_logs', 'consultations'] as $table) Schema::dropIfExists($table);
    }
};
