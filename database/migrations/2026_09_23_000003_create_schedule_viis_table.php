<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('schedule_viis')) {
            Schema::create('schedule_viis', function (Blueprint $table) {
                $table->id('sc_viis_id');
                $table->string('sc_viis_no', 50)->nullable();
                $table->mediumText('sc_viis');
                $table->unsignedTinyInteger('active_yn')->default(1);
                $table->string('schedule_name')->nullable();
                $table->timestamps();
            });
        }

        if (Schema::hasTable('schedule_viis') && DB::table('schedule_viis')->count() === 0) {
            DB::table('schedule_viis')->insert([
                ['sc_viis_no' => '1', 'sc_viis' => '(i) eradicating hunger, poverty and malnutrition', 'active_yn' => 1, 'schedule_name' => 'Healthcare', 'created_at' => now(), 'updated_at' => now()],
                ['sc_viis_no' => '2', 'sc_viis' => '(ii) promoting education and skill development', 'active_yn' => 1, 'schedule_name' => 'Education', 'created_at' => now(), 'updated_at' => now()],
                ['sc_viis_no' => '3', 'sc_viis' => '(iii) promoting gender equality and women empowerment', 'active_yn' => 1, 'schedule_name' => 'Gender Equality', 'created_at' => now(), 'updated_at' => now()],
                ['sc_viis_no' => '7', 'sc_viis' => '(vii) training to promote rural sports and Olympic sports', 'active_yn' => 1, 'schedule_name' => 'Sports', 'created_at' => now(), 'updated_at' => now()],
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('schedule_viis');
    }
};
