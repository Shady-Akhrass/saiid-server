<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Shelter extends Model
{
    use HasFactory;

    protected $primaryKey = 'manager_id_number';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'manager_id_number',
        'camp_name',
        'governorate',
        'district',
        'detailed_address',
        'tents_count',
        'families_count',
        'manager_name',
        'manager_phone',
        'manager_alternative_phone',
        'manager_job_description',
        'deputy_manager_name',
        'deputy_manager_id_number',
        'deputy_manager_phone',
        'deputy_manager_alternative_phone',
        'deputy_manager_job_description',
        // 'data_approval_name',
        'excel_sheet',
    ];
}
