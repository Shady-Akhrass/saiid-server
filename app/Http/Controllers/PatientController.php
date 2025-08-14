<?php

namespace App\Http\Controllers;

use App\Models\Patient;
use Illuminate\Http\Request;
use App\Exports\PatientsExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Validator;

class PatientController extends Controller
{
    public function create(Request $request)
    {
        $validator  = Validator::make(
            $request->all(),
            [
                'name' => 'required|min:4',
                'id_number' => 'required|regex:/^\d{9}$/',
                'birth_date' => 'required|date',
                'gender' => 'required',
                'health_status' => 'required',
                'marital_status' => 'required',
                // 'number_of_brothers' => 'required_if:marital_status,متزوج,أرمل,مطلق|integer',
                // 'number_of_sisters' => 'required_if:marital_status,متزوج,أرمل,مطلق|integer',
                'current_address' => 'required',
                'guardian_phone_number' => 'required|regex:/^\d{10}$/',
                'alternative_phone_number' => 'required|regex:/^\d{10}$/',
            ],
            [
                'name.required' => 'يرجى إدخال الاسم رباعي',
                'name.min' => 'الاسم يجب أن يكون رباعي',
                'id_number.required' => 'يرجى إدخال رقم الهوية',
                'id_number.regex' => 'رقم الهوية يجب أن يتكون من 9 أرقام',
                'birth_date.required' => 'يرجى إدخال تاريخ الميلاد',
                'gender.required' => 'يرجى اختيار الجنس',
                'health_status.required' => 'يرجى اختيار حالة الصحة',
                'marital_status.required' => 'يرجى اختيار الحالة الإجتماعية',
                'current_address.required' => 'يرجى اختيار عنوان السكن الحالي',
                'guardian_phone_number.required' => 'يرجى إدخال رقم الجوال',
                'guardian_phone_number.regex' => 'رقم الجوال يجب أن يتكون من 10 أرقام',
                'alternative_phone_number.required' => 'يرجى إدخال رقم الجوال البديل',
                'alternative_phone_number.regex' => 'رقم الجوال البديل يجب أن يتكون من 10 أرقام',

            ]
        )->setAttributeNames([
            'الاسم رباعي' => 'name',
            'رقم الهوية' => 'id_number',
            'تاريخ الميلاد' => 'birth_date',
            'الجنس' => 'gender',
            'الحالة الصحية' => 'health_status',
            'الحالة الإجتماعية' => 'marital_status',
            'عدد الأبناء الذكور' => 'number_of_brothers',
            'عدد البنات الإناث' => 'number_of_sisters',
            'عنوان السكن الحالي' => 'current_address',
            'رقم الجوال' => 'guardian_phone_number',
            'رقم الجوال البديل' => 'alternative_phone_number',

        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        $data = Patient::create($request->all());

        // Return success response
        return response()->json([
            'message' => 'تمت إضافة بيانات رب الأسرة بنجاح',
            'data' => $data,
        ], 201);
    }
    public function fetchPatients(Request $request)
    {
        $searchQuery = $request->query('searchQuery');
        $perPage = $request->query('perPage', 10);
        $page = $request->query('page', 1);
        $limit = (int) $perPage;
        $offset = ($page - 1) * $limit;

        $searchFields = [
            'name',
            'id_number',
            'guardian_phone_number',
            'id_number',
            'health_status',

        ];

        $query = Patient::query();
        if ($searchQuery) {
            $query->where(function (Builder $query) use ($searchFields, $searchQuery) {
                foreach ($searchFields as $field) {
                    $query->orWhere($field, 'LIKE', "%{$searchQuery}%");
                }
            });
        }

        $totalPatients = $query->count();

        $aids = $query->orderBy('created_at', 'DESC')
            ->offset($offset)
            ->limit($limit)
            ->get();

        return response()->json([
            'patients' => $aids,
            'totalPatients' => $totalPatients,
            'totalPages' => ceil($totalPatients / $limit),
            'currentPage' => $page
        ], 200);
    }

    // public function incrementVisitorCount()
    // {
    //     $visitorCount = Visitor::first();

    //     if (!$visitorCount) {

    //         $visitorCount = new Visitor();
    //         $visitorCount->aid_visitors = 0;
    //     }

    //     $visitorCount->aid_visitors++;
    //     $visitorCount->save();

    //     return response()->json(['success' => true, 'count' => $visitorCount->aid_visitors]);
    // }

    // public function fetchAllAidsForDashboard()
    // {
    //     $aids = Aid::orderBy('created_at', 'DESC')->get();
    //     $totalAids = $aids->count();

    //     $visitorCount = Visitor::first();

    //     $totalAidVisitors = $visitorCount ? $visitorCount->aids_visitors : 0;

    //     $statusCounts = $aids->groupBy('status')->map->count();
    //     $aidTypeCounts = $aids->groupBy('aid_type')->map->count();
    //     $beneficiaryAddressCounts = $aids->groupBy('beneficiary_address')->map->count();

    //     // Assuming aid has a 'date_received' field and we calculate age groups based on it
    //     $aidAgeGroups = $aids->map(function ($aid) {
    //         $receivedDate = \Carbon\Carbon::parse($aid->date_received);
    //         $daysSinceReceived = \Carbon\Carbon::now()->diffInDays($receivedDate);
    //         return $daysSinceReceived < 30 ? '0-30 days' : ($daysSinceReceived < 60 ? '31-60 days' : '60+ days');
    //     })->groupBy(fn($age) => $age)->map->count();

    //     return response()->json([
    //         'totalAids' => $totalAids,
    //         'totalVisitors' => $totalAidVisitors,
    //         'statusCounts' => $statusCounts,
    //         'aidTypeCounts' => $aidTypeCounts,
    //         'beneficiaryAddressCounts' => $beneficiaryAddressCounts,
    //         'aidAgeGroups' => $aidAgeGroups,
    //     ], 200);
    // }
    public function exportPatientsToExcel()
    {
        return Excel::download(new PatientsExport, 'orphans.xlsx');
    }
}
