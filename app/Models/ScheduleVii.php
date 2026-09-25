<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ScheduleVii extends Model
{
    protected $table = 'schedule_viis';

    protected $primaryKey = 'sc_viis_id';

    protected $fillable = [
        'sc_viis_no',
        'sc_viis',
        'active_yn',
        'schedule_name',
    ];
}
