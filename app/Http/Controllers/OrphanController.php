<?php

namespace App\Http\Controllers;

use App\Models\Orphan;
use App\Models\Visitor;
use Illuminate\Http\Request;
use App\Exports\OrphansExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Validator;

class OrphanController extends Controller
{
    public function create(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'orphan_id_number' => 'required|string|min:9|unique:orphans,orphan_id_number',
            'orphan_full_name' => 'required|string|min:3',
            'orphan_birth_date' => [
                'required',
                'date',
                function ($attribute, $value, $fail) {
                    $birthDate = \Carbon\Carbon::parse($value);
                    $twelveYearsAgo = \Carbon\Carbon::now()->subYears(12);
                    if ($birthDate->lt($twelveYearsAgo)) {
                        $fail('يجب أن يكون عمر اليتيم أقل من 12 عامًا.');
                    }
                },
            ],
            'orphan_gender' => 'required|in:ذكر,أنثى',
            'health_status' => 'required|in:جيدة,مريض',
            'disease_description' => 'nullable|string',
            'original_address' => 'required|in:محافظة الشمال,محافظة غزة,محافظة الوسطى,محافظة خانيونس,محافظة رفح',
            'current_address' => 'required|in:محافظة الشمال,محافظة غزة,محافظة الوسطى,محافظة خانيونس,محافظة رفح',
            'address_details' => 'nullable|string',
            'number_of_brothers' => 'nullable|integer|min:0',
            'number_of_sisters' => 'nullable|integer|min:0',
            'is_enrolled_in_memorization_center' => 'required|in:نعم,لا',
            'orphan_photo' => 'required|file|image|max:2048',
            'guardian_id_number' => 'required|string|min:9',
            'guardian_full_name' => 'required|string|min:3',
            'guardian_relationship' => 'required|string|min:2',
            'guardian_phone_number' => ['required', 'string', 'regex:/^(056|059)[0-9]{7}$/'],
            'alternative_phone_number' => ['nullable', 'string', 'regex:/^(056|059)[0-9]{7}$/'],
            'deceased_father_full_name' => 'required|string|min:3',
            'deceased_father_birth_date' => 'required|date',
            'death_date' => 'required|date',
            'death_cause' => 'required|in:شهيد حرب,وفاة طبيعية,وفاة بسبب المرض',
            'previous_father_job' => 'nullable|string',
            'death_certificate' => 'required|file|image|max:2048',
            'mother_full_name' => 'required|string|min:3',
            'mother_id_number' => 'required|string|min:9',
            'is_mother_deceased' => 'required|in:نعم,لا',
            'mother_birth_date' => 'required|date',
            'mother_death_date' => 'nullable|date',
            'mother_status' => 'required|in:أرملة,متزوجة',
            'mother_job' => 'required|string|min:3',
            'data_approval_name' => 'required|string|min:3',
        ], [
            'required' => 'حقل :attribute مطلوب.',
            'string' => 'حقل :attribute يجب أن يكون نصاً.',
            'min' => [
                'string' => 'حقل :attribute يجب أن يحتوي على الأقل :min أحرف.',
                'numeric' => 'حقل :attribute يجب أن يكون على الأقل :min.',
            ],
            'unique' => 'قيمة :attribute مستخدمة بالفعل.',
            'date' => 'حقل :attribute يجب أن يكون تاريخاً صحيحاً.',
            'in' => 'القيمة المحددة في حقل :attribute غير صالحة.',
            'integer' => 'حقل :attribute يجب أن يكون رقماً صحيحاً.',
            'file' => 'حقل :attribute يجب أن يكون ملفاً.',
            'image' => 'حقل :attribute يجب أن يكون صورة.',
            'max' => [
                'file' => 'حجم :attribute يجب ألا يتجاوز :max كيلوبايت.',
            ],
            'regex' => 'صيغة حقل :attribute غير صحيحة.',
            'orphan_birth_date' => 'يجب أن يكون عمر اليتيم أقل من 12 عامًا.',
        ])->setAttributeNames([
            'orphan_id_number' => 'رقم هوية اليتيم',
            'orphan_full_name' => 'الاسم الكامل لليتيم',
            'orphan_birth_date' => 'تاريخ ميلاد اليتيم',
            'orphan_gender' => 'جنس اليتيم',
            'health_status' => 'الحالة الصحية',
            'disease_description' => 'وصف المرض',
            'original_address' => 'العنوان الأصلي',
            'current_address' => 'العنوان الحالي',
            'address_details' => 'تفاصيل العنوان',
            'number_of_brothers' => 'عدد الإخوة',
            'number_of_sisters' => 'عدد الأخوات',
            'is_enrolled_in_memorization_center' => 'ملتحق بمركز تحفيظ',
            'orphan_photo' => 'صورة اليتيم',
            'guardian_id_number' => 'رقم هوية الوصي',
            'guardian_full_name' => 'الاسم الكامل للوصي',
            'guardian_relationship' => 'صلة القرابة بالوصي',
            'guardian_phone_number' => 'رقم هاتف الوصي',
            'alternative_phone_number' => 'رقم الهاتف البديل',
            'deceased_father_full_name' => 'الاسم الكامل للأب المتوفى',
            'deceased_father_birth_date' => 'تاريخ ميلاد الأب المتوفى',
            'death_date' => 'تاريخ الوفاة',
            'death_cause' => 'سبب الوفاة',
            'previous_father_job' => 'وظيفة الأب السابقة',
            'death_certificate' => 'شهادة الوفاة',
            'mother_full_name' => 'الاسم الكامل للأم',
            'mother_id_number' => 'رقم هوية الأم',
            'is_mother_deceased' => 'هل الأم متوفاة',
            'mother_birth_date' => 'تاريخ ميلاد الأم',
            'mother_death_date' => 'تاريخ وفاة الأم',
            'mother_status' => 'حالة الأم',
            'mother_job' => 'وظيفة الأم',
            'data_approval_name' => 'اسم معتمد البيانات',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        $orphanData = $request->all();
        if ($request->hasFile('orphan_photo')) {
            $fileName = time() . '.' . $request->file('orphan_photo')->extension();
            $request->file('orphan_photo')->move(public_path('orphan_photos'), $fileName);
            $orphanData['orphan_photo'] = 'orphan_photos/' . $fileName;
        }

        if ($request->has('death_certificate')) {
            $fileName = time() . '.' . $request->file('death_certificate')->extension();
            $request->file('death_certificate')->move(public_path('death_certificates'), $fileName);
            $orphanData['death_certificate'] = 'death_certificates/' . $fileName;
        }

        try {
            $orphan = Orphan::create($orphanData);
            return response()->json(['orphan' => $orphan], 201);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Internal Server Error'], 500);
        }
    }


    public function fetchOrphans(Request $request)
    {
        $searchQuery = $request->query('searchQuery');
        $perPage = $request->query('perPage', 10);
        $page = $request->query('page', 1);
        $limit = (int) $perPage;
        $offset = ($page - 1) * $limit;

        $searchFields = [
            'orphan_id_number',
            'orphan_full_name',
            'original_address',
            'current_address',
            'health_status',
            'deceased_father_full_name',
            'death_cause',
            'mother_full_name',
            'mother_status',
            'mother_job',
            'guardian_full_name',
            'guardian_relationship',
            'guardian_phone_number',
            'alternative_phone_number',
            'is_enrolled_in_memorization_center',
            'mother_id_number',

        ];

        $query = Orphan::query();
        if ($searchQuery) {
            $query->where(function (Builder $query) use ($searchFields, $searchQuery) {
                foreach ($searchFields as $field) {
                    $query->orWhere($field, 'LIKE', "%{$searchQuery}%");
                }
            });
        }

        $totalOrphans = $query->count();

        $orphans = $query->orderBy('created_at', 'DESC')
            ->offset($offset)
            ->limit($limit)
            ->get();

        return response()->json([
            'orphans' => $orphans,
            'totalOrphans' => $totalOrphans,
            'totalPages' => ceil($totalOrphans / $limit),
            'currentPage' => $page
        ], 200);
    }


    public function incrementVisitorCount()
    {
        $visitorCount = Visitor::first();

        if (!$visitorCount) {

            $visitorCount = new Visitor();
            $visitorCount->orphan_visitors = 0;
        }

        $visitorCount->orphan_visitors++;
        $visitorCount->save();

        return response()->json(['success' => true, 'count' => $visitorCount->orphan_visitors]);
    }

    public function fetchAllOrphansForDashboard()
    {
        $orphans = Orphan::orderBy('created_at', 'DESC')->get();
        $totalOrphans = $orphans->count();


        $visitorCount = Visitor::first();

        $totalAidVisitors = $visitorCount ? $visitorCount->aids_visitors : 0;


        $motherStatusCounts = $orphans->groupBy('mother_status')->map->count();
        $deathCauseCounts = $orphans->groupBy('death_cause')->map->count();
        $originalAddressCounts = $orphans->groupBy('original_address')->map->count();

        // Grouping orphans by age categories
        $ageGroups = $orphans->map(function ($orphan) {
            $age = \Carbon\Carbon::now()->diffInYears(\Carbon\Carbon::parse($orphan->orphan_birth_date));
            return $age < 5 ? '0-4' : ($age < 10 ? '5-9' : '10-14');
        })->groupBy(fn($age) => $age)->map->count();

        $motherDeceasedCounts = $orphans->groupBy('is_mother_deceased')->map->count();
        $genderCounts = $orphans->groupBy('orphan_gender')->map->count();

        return response()->json([
            'totalOrphans' => $totalOrphans,
            'totalVisitors' => $totalAidVisitors,  // Pass the visitor count here
            'motherStatusCounts' => $motherStatusCounts,
            'deathCauseCounts' => $deathCauseCounts,
            'originalAddressCounts' => $originalAddressCounts,
            'ageGroups' => $ageGroups,
            'motherDeceasedCounts' => $motherDeceasedCounts,
            'genderCounts' => $genderCounts,
        ], 200);
    }



    public function show($id)
    {
        $orphan = Orphan::findOrFail($id);
        $imagePath = public_path($orphan->orphan_photo);
        if (!file_exists($imagePath)) {
            return response()->json(['error' => 'Image not found'], 404);
        }
        return response()->file($imagePath);
    }

    public function death_certificate($id)
    {
        $orphan = Orphan::findOrFail($id);
        $imagePath = public_path($orphan->death_certificate);
        if (!file_exists($imagePath)) {
            return response()->json(['error' => 'Image not found'], 404);
        }
        return response()->file($imagePath);
    }

    public function exportOrphansToExcel()
    {
        return Excel::download(new OrphansExport, 'orphans.xlsx');
    }
}
