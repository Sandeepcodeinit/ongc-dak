<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('proposal_hard_copies')) {
            Schema::create('proposal_hard_copies', function (Blueprint $table) {
                $table->id();
                $table->string('project_receipt_date', 50)->nullable();
                $table->mediumText('proposal_title')->nullable();
                $table->string('agency_email', 50)->nullable();
                $table->string('proposal_cost', 255)->nullable();
                $table->string('proposal_schedule', 255)->nullable();
                $table->string('is_aspirrational_district', 255)->default('0')->comment('0:no, 1:yes');
                $table->string('project_location', 255)->nullable();
                $table->string('proposal_status', 255)->nullable();
                $table->string('district', 255)->nullable();
                $table->mediumText('remarks')->nullable();
                $table->string('commitee_id', 255)->nullable();
                $table->string('mopng_reference', 255)->default('0')->comment('0:no, 1:yes');
                $table->string('referring_person_name', 255)->nullable();
                $table->unsignedInteger('mp_id')->default(0);
                $table->string('vip_type', 255)->nullable();
                $table->string('implementing_agency', 255)->nullable();
                $table->string('fpr_name', 255)->nullable();
                $table->mediumText('proposal_pdf')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('proposal_hard_copies');
    }
};
