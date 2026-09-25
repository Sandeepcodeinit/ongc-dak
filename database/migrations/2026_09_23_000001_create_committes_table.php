<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('committes')) {
            Schema::create('committes', function (Blueprint $table) {
                $table->id();
                $table->string('committee_name')->nullable();
                $table->unsignedTinyInteger('active_status')->default(1)->comment('0:Deleted, 1:Active');
                $table->timestamp('delete_date')->nullable();
                $table->timestamps();
            });
        }

        if (Schema::hasTable('committes') && DB::table('committes')->count() === 0) {
            DB::table('committes')->insert([
                ['committee_name' => 'Business Advisory Committee', 'active_status' => 1, 'created_at' => now(), 'updated_at' => now()],
                ['committee_name' => 'Ethics', 'active_status' => 1, 'created_at' => now(), 'updated_at' => now()],
                ['committee_name' => 'Finance Committee', 'active_status' => 1, 'created_at' => now(), 'updated_at' => now()],
                ['committee_name' => 'External Affairs Committee', 'active_status' => 1, 'created_at' => now(), 'updated_at' => now()],
                ['committee_name' => 'Petroleum & Natural Gas Committee', 'active_status' => 1, 'created_at' => now(), 'updated_at' => now()],
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('committes');
    }
};
