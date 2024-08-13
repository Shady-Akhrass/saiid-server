<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Orphan extends Model
{
    use HasFactory;

    protected $primaryKey = 'orphan_id_number';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'orphan_id_number',
        'orphan_full_name',
        'orphan_birth_date',
        'health_status',
        'disease_description',
        'original_address',
        'current_address',
        'deceased_father_full_name',
        'deceased_father_birth_date',
        'death_date',
        'death_cause',
        'previous_father_job',
        'number_of_siplings',
        'mother_full_name',
        'mother_id_number',
        'mother_birth_date',
        'mother_death_date',
        'mother_status',
        'mother_job',
        'guardian_full_name',
        'guardian_relationship',
        'guardian_phone_number',
        'alternative_phone_number',
        'is_enrolled_in_memorization_center',
        'orphan_photo',
        'data_approval_name'
    ];
}
