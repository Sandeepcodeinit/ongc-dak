<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Committee extends Model
{
    protected $table = 'committes';

    protected $fillable = [
        'committee_name',
        'active_status',
        'delete_date',
    ];
}
