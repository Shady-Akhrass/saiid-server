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
        'orphan_gender',
        'health_status',
        'disease_description',
        'original_address',
        'current_address',
        'address_details',
        'number_of_brothers',
        'number_of_sisters',
        'is_enrolled_in_memorization_center',
        'orphan_photo',
        'guardian_id_number',
        'guardian_full_name',
        'guardian_relationship',
        'guardian_phone_number',
        'alternative_phone_number',
        'deceased_father_full_name',
        'deceased_father_birth_date',
        'death_date',
        'death_cause',
        'previous_father_job',
        'death_certificate',
        'mother_full_name',
        'mother_id_number',
        'is_mother_deceased',
        'mother_birth_date',
        'mother_death_date',
        'mother_status',
        'mother_job',
        'data_approval_name',
        
    ];
}
