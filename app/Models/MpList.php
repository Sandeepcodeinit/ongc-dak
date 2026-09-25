<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MpList extends Model
{
    protected $table = 'mp_lists';

    protected $fillable = [
        'vip_name',
        'vip_type',
        'house_name',
        'constituency_name',
        'active_status',
        'delete_date',
    ];
}
