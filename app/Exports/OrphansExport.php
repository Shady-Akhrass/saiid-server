<?php

namespace App\Exports;

use App\Models\Orphan;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class OrphansExport implements FromCollection, WithHeadings, WithMapping
{
    public function collection()
    {
        return Orphan::all();
    }

    public function headings(): array
    {
        return [
            'رقم هوية اليتيم',
            'اسم اليتيم',
            'تاريخ الميلاد',
            'الحالة الصحية',
            'وصف المرض',
            'العنوان الأصلي',
            'العنوان الحالي',
            'اسم الأب المتوفي',
            'تاريخ ميلاد الأب',
            'تاريخ الوفاة',
            'سبب الوفاة',
            'وظيفة الأب السابقة',
            'عدد الإخوة',
            'اسم الأم',
            'رقم هوية الأم',
            'تاريخ ميلاد الأم',
            'تاريخ وفاة الأم',
            'حالة الأم',
            'وظيفة الأم',
            'اسم الوصي',
            'علاقة الوصي',
            'رقم هاتف الوصي',
            'رقم هاتف بديل',
            'الالتحاق بمركز تحفيظ',
            'اسم موافق البيانات',
            'صورة اليتيم',
        ];
    }

    public function map($orphan): array
    {
        return [
            $orphan->orphan_id_number,
            $orphan->orphan_full_name,
            $orphan->orphan_birth_date,
            $orphan->health_status,
            $orphan->disease_description,
            $orphan->original_address,
            $orphan->current_address,
            $orphan->deceased_father_full_name,
            $orphan->deceased_father_birth_date,
            $orphan->death_date,
            $orphan->death_cause,
            $orphan->previous_father_job,
            $orphan->number_of_siplings,
            $orphan->mother_full_name,
            $orphan->mother_id_number,
            $orphan->mother_birth_date,
            $orphan->mother_death_date,
            $orphan->mother_status,
            $orphan->mother_job,
            $orphan->guardian_full_name,
            $orphan->guardian_relationship,
            $orphan->guardian_phone_number,
            $orphan->alternative_phone_number,
            $orphan->is_enrolled_in_memorization_center,
            $orphan->data_approval_name,
            $orphan->orphan_photo,
        ];
    }
}
