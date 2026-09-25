<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProposalHardCopy extends Model
{
    use HasFactory;

    protected $table = 'proposal_hard_copies';

    protected $fillable = [
        'project_receipt_date',
        'proposal_title',
        'agency_email',
        'proposal_cost',
        'proposal_schedule',
        'is_aspirrational_district',
        'project_location',
        'proposal_status',
        'district',
        'remarks',
        'commitee_id',
        'mopng_reference',
        'referring_person_name',
        'mp_id',
        'vip_type',
        'implementing_agency',
        'fpr_name',
        'proposal_pdf',
    ];
}
