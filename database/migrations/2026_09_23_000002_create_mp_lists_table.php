<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('mp_lists')) {
            Schema::create('mp_lists', function (Blueprint $table) {
                $table->id();
                $table->string('vip_name')->nullable();
                $table->string('vip_type')->nullable();
                $table->string('house_name')->nullable();
                $table->string('constituency_name')->nullable();
                $table->unsignedTinyInteger('active_status')->default(1)->comment('0:Deleted, 1:Active');
                $table->timestamp('delete_date')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('mp_lists');
    }
};
