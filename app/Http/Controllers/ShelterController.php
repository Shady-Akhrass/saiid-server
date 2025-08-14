<?php

namespace App\Http\Controllers;

use App\Models\Refugee;
use App\Models\Shelter;
use App\Models\Visitor;
use Illuminate\Http\Request;
use App\Exports\RefugeesExport;
use App\Exports\SheltersExport;
use App\Imports\RefugeesImport;
use App\Imports\SheltersImport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Validator;

class ShelterController extends Controller
{
    public function create(Request $request)
    {
        $validator = Validator::make($request->all(), [
            // Manager Information
            'manager_id_number' => 'required|string|min:9|unique:shelters,manager_id_number',
            'manager_name' => 'required|string|min:3',
            'manager_phone' => ['required', 'string', 'regex:/^(056|059)[0-9]{7}$/'],
            'manager_alternative_phone' => ['nullable', 'string', 'regex:/^(056|059)[0-9]{7}$/'],
            'manager_job_description' => 'required|string',

            // Deputy Manager Information
            'deputy_manager_name' => 'required|string|min:3',
            'deputy_manager_id_number' => 'required|string|min:9|unique:shelters,deputy_manager_id_number',
            'deputy_manager_phone' => ['required', 'string', 'regex:/^(056|059)[0-9]{7}$/'],
            'deputy_manager_alternative_phone' => ['nullable', 'string', 'regex:/^(056|059)[0-9]{7}$/'],
            'deputy_manager_job_description' => 'required|string',

            // Shelter Information
            'camp_name' => 'required|string|min:3',
            'governorate' => 'required|in:محافظة الشمال,محافظة غزة,محافظة الوسطى,محافظة خانيونس,محافظة رفح',
            'district' => 'required|string',
            'detailed_address' => 'required|string',
            'tents_count' => 'required|integer|min:1',
            'families_count' => 'required|integer|min:1',

            // Approval Information
            'excel_sheet' => 'required|file|mimes:xlsx,xls,csv|max:2048',
        ], [
            'required' => 'حقل :attribute مطلوب.',
            'string' => 'حقل :attribute يجب أن يكون نصاً.',
            'min' => [
                'string' => 'حقل :attribute يجب أن يحتوي على الأقل :min أحرف.',
                'numeric' => 'حقل :attribute يجب أن يكون على الأقل :min.',
            ],
            'unique' => 'قيمة :attribute مستخدمة بالفعل.',
            'integer' => 'حقل :attribute يجب أن يكون رقماً صحيحاً.',
            'in' => 'القيمة المحددة في حقل :attribute غير صالحة.',
            'regex' => 'صيغة حقل :attribute غير صحيحة.',
            'mimes' => 'يجب أن يكون الملف من نوع: xlsx, xls, أو csv.',
            'max' => [
                'file' => 'حجم الملف يجب ألا يتجاوز :max كيلوبايت.',
            ],
        ])->setAttributeNames([
            'manager_id_number' => 'رقم هوية المدير',
            'manager_name' => 'اسم المدير',
            'manager_phone' => 'رقم هاتف المدير',
            'manager_alternative_phone' => 'رقم الهاتف البديل للمدير',
            'manager_job_description' => 'الوصف الوظيفي للمدير',
            'deputy_manager_name' => 'اسم نائب المدير',
            'deputy_manager_id_number' => 'رقم هوية نائب المدير',
            'deputy_manager_phone' => 'رقم هاتف نائب المدير',
            'deputy_manager_alternative_phone' => 'رقم الهاتف البديل لنائب المدير',
            'deputy_manager_job_description' => 'الوصف الوظيفي لنائب المدير',
            'camp_name' => 'اسم المخيم',
            'governorate' => 'المحافظة',
            'district' => 'المنطقة',
            'detailed_address' => 'العنوان التفصيلي',
            'tents_count' => 'عدد الخيم',
            'families_count' => 'عدد العائلات',
            'excel_sheet' => 'ملف Excel',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        $shelterData = $request->except('excel_sheet');

        if ($request->hasFile('excel_sheet')) {
            $managerId = $request->manager_id_number;
            $extension = $request->file('excel_sheet')->getClientOriginalExtension();
            $fileName = $managerId . '.' . $extension; // Unique filename based on manager_id_number
            $filePath = 'excel_sheets/' . $fileName;

            // Move the uploaded file
            $request->file('excel_sheet')->move(public_path('excel_sheets'), $fileName);

            // Store the path in the database
            $shelterData['excel_sheet'] = $filePath;
        }


        try {
            $shelter = Shelter::create($shelterData);

            return response()->json(['shelter' => $shelter], 201);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Internal Server Error', 'message' => $e->getMessage()], 500);
        }
    }

    public function fetchShelters(Request $request)
    {
        $searchQuery = $request->query('searchQuery');
        $perPage = $request->query('perPage', 10);
        $page = $request->query('page', 1);
        $limit = (int) $perPage;
        $offset = ($page - 1) * $limit;

        $searchFields = [
            'manager_id_number',
            'manager_name',
            'manager_phone',
            'deputy_manager_name',
            'deputy_manager_id_number',
            'deputy_manager_phone',
            'camp_name',
            'governorate',
            'district',
            'detailed_address',
        ];

        $query = Shelter::query();

        if ($searchQuery) {
            $query->where(function (Builder $query) use ($searchFields, $searchQuery) {
                foreach ($searchFields as $field) {
                    $query->orWhere($field, 'LIKE', "%{$searchQuery}%");
                }
            });
        }

        $totalShelters = $query->count();

        $shelters = $query->orderBy('created_at', 'DESC')
            ->offset($offset)
            ->limit($limit)
            ->get([
                'manager_id_number',
                'manager_name',
                'manager_phone',
                'manager_alternative_phone',
                'manager_job_description',
                'deputy_manager_name',
                'deputy_manager_id_number',
                'deputy_manager_phone',
                'deputy_manager_alternative_phone',
                'deputy_manager_job_description',
                'camp_name',
                'governorate',
                'district',
                'detailed_address',
                'tents_count',
                'families_count',
                'excel_sheet'
            ]);

        return response()->json([
            'shelters' => $shelters,
            'totalShelters' => $totalShelters,
            'totalPages' => ceil($totalShelters / $limit),
            'currentPage' => $page
        ], 200);
    }



    public function incrementVisitorCount()
    {
        $visitorCount = Visitor::first();

        if (!$visitorCount) {
            $visitorCount = new Visitor();
            $visitorCount->shelter_visitors = 0;
        }

        $visitorCount->shelter_visitors++;
        $visitorCount->save();

        return response()->json(['success' => true, 'count' => $visitorCount->shelter_visitors]);
    }

    public function fetchAllSheltersForDashboard()
    {
        $shelters = Shelter::orderBy('created_at', 'DESC')->get();
        $totalShelters = $shelters->count();

        $visitorCount = Visitor::first();
        $totalShelterVisitors = $visitorCount ? $visitorCount->shelter_visitors : 0;

        $shelterTypeCounts = $shelters->groupBy('current_shelter_type')->map->count();
        $originalAddressCounts = $shelters->groupBy('original_address')->map->count();
        $currentAddressCounts = $shelters->groupBy('current_address')->map->count();
        $houseDamageCounts = $shelters->groupBy('house_damage_level')->map->count();

        $ageGroups = $shelters->map(function ($shelter) {
            $age = \Carbon\Carbon::now()->diffInYears(\Carbon\Carbon::parse($shelter->shelter_birth_date));
            return $age < 5 ? '0-4' : ($age < 10 ? '5-9' : '10-14');
        })->groupBy(fn($age) => $age)->map->count();

        $genderCounts = $shelters->groupBy('shelter_gender')->map->count();

        return response()->json([
            'totalShelters' => $totalShelters,
            'totalVisitors' => $totalShelterVisitors,
            'shelterTypeCounts' => $shelterTypeCounts,
            'originalAddressCounts' => $originalAddressCounts,
            'currentAddressCounts' => $currentAddressCounts,
            'houseDamageCounts' => $houseDamageCounts,
            'ageGroups' => $ageGroups,
            'genderCounts' => $genderCounts,
        ], 200);
    }

   
    public function importRefugees(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'file' => 'required|mimes:xlsx,xls,csv|max:2048',
            'manager_id_number' => 'required|exists:shelters,manager_id_number'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        try {
            $import = new RefugeesImport($request->input('manager_id_number'));
            Excel::import($import, $request->file('file'));

            // Get imported rows for debugging
            $importedRows = $import->getImportedRows();

            return response()->json([
                'message' => 'تم استيراد البيانات بنجاح',
                'imported_rows' => $importedRows
            ], 200);
        } catch (\Exception $e) {
            \Log::error('Refugee Import Error: ' . $e->getMessage());
            $importedRows = $import->getImportedRows();
            return response()->json([
                'error' => 'حدث خطأ أثناء استيراد البيانات',
                'details' => $e->getMessage(),
                'imported_rows' => $importedRows
            ], 500);
        }
    }
    public function show($id)
    {
        $shelter = Shelter::findOrFail($id);
        $filePath = public_path($shelter->excel_sheet);
        if (!file_exists($filePath)) {
            return response()->json(['error' => 'file not found'], 404);
        }
        return response()->file($filePath);
    }
}
