<?php

namespace App\Http\Controllers;

use App\Exports\AidsExport;
use App\Models\Aid;
use App\Models\Visitor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Facades\Excel;

class AidController extends Controller
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
                'number_of_brothers' => 'required_if:marital_status,متزوج,أرمل,مطلق|integer',
                'number_of_sisters' => 'required_if:marital_status,متزوج,أرمل,مطلق|integer',
                'job' => 'required',
                'salary' => 'required',
                'original_address' => 'required',
                'current_address' => 'required',
                'address_details' => 'required',
                'guardian_phone_number' => 'required|regex:/^\d{10}$/',
                'alternative_phone_number' => 'required|regex:/^\d{10}$/',
                'aid' => 'required',
                'Nature_of_aid' => 'required_if:aid,وزارة التنمية,وكالة الغوث',
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
                'number_of_brothers.required_if' => 'يرجى إدخال عدد الأبناء الذكور',
                'number_of_sisters.required_if' => 'يرجى إدخال عدد البنات الإناث',
                'job.required' => 'يرجى اختيار نوع العمل',
                'salary.required' => 'يرجى اختيار مستوى الدخل',
                'original_address.required' => 'يرجى اختيار عنوان السكن الأساسي',
                'current_address.required' => 'يرجى اختيار عنوان السكن الحالي',
                'address_details.required' => 'يرجى إدخال عنوان السكن بالتفصيل',
                'guardian_phone_number.required' => 'يرجى إدخال رقم الجوال',
                'guardian_phone_number.regex' => 'رقم الجوال يجب أن يتكون من 10 أرقام',
                'alternative_phone_number.required' => 'يرجى إدخال رقم الجوال البديل',
                'alternative_phone_number.regex' => 'رقم الجوال البديل يجب أن يتكون من 10 أرقام',
                'aid.required' => 'يرجى اختيار نوع المساعدة',
                'Nature_of_aid.required_if' => 'يرجى إدخال طبيعة المساعدة',

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
            'نوع العمل' => 'job',
            'مستوى الدخل' => 'salary',
            'عنوان السكن الأساسي' => 'original_address',
            'عنوان السكن الحالي' => 'current_address',
            'عنوان السكن بالتفصيل' => 'address_details',
            'رقم الجوال' => 'guardian_phone_number',
            'رقم الجوال البديل' => 'alternative_phone_number',
            'نوع المساعدة' => 'aid',
            'طبيعة المساعدة' => 'Nature_of_aid',

        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        $data = Aid::create($request->all());

        // Return success response
        return response()->json([
            'message' => 'تمت إضافة بيانات رب الأسرة بنجاح',
            'data' => $data,
        ], 201);
    }
    public function fetchAids(Request $request)
    {
        $searchQuery = $request->query('searchQuery');
        $perPage = $request->query('perPage', 10);
        $page = $request->query('page', 1);
        $limit = (int) $perPage;
        $offset = ($page - 1) * $limit;

        $searchFields = [
            'aid_type',
            'aid_description',
            'beneficiary_name',
            'beneficiary_id_number',
            'status',
        ];

        $query = Aid::query();
        if ($searchQuery) {
            $query->where(function (Builder $query) use ($searchFields, $searchQuery) {
                foreach ($searchFields as $field) {
                    $query->orWhere($field, 'LIKE', "%{$searchQuery}%");
                }
            });
        }

        $totalAids = $query->count();

        $aids = $query->orderBy('created_at', 'DESC')
            ->offset($offset)
            ->limit($limit)
            ->get();

        return response()->json([
            'aids' => $aids,
            'totalAids' => $totalAids,
            'totalPages' => ceil($totalAids / $limit),
            'currentPage' => $page
        ], 200);
    }

    // Increment visitor counter and save in the database
    public function incrementVisitorCount()
    {
        $visitorCount = Visitor::first();

        if (!$visitorCount) {

            $visitorCount = new Visitor();
            $visitorCount->aids_visitors = 0;
        }

        $visitorCount->aids_visitors++;
        $visitorCount->save();

        return response()->json(['success' => true, 'count' => $visitorCount->aids_visitors]);
    }

    public function fetchAllAidsForDashboard()
    {
        $aids = Aid::orderBy('created_at', 'DESC')->get();
        $totalAids = $aids->count();

        $visitorCount = Visitor::first();

        $totalAidVisitors = $visitorCount ? $visitorCount->aids_visitors : 0;

        $statusCounts = $aids->groupBy('status')->map->count();
        $aidTypeCounts = $aids->groupBy('aid_type')->map->count();
        $beneficiaryAddressCounts = $aids->groupBy('beneficiary_address')->map->count();

        // Assuming aid has a 'date_received' field and we calculate age groups based on it
        $aidAgeGroups = $aids->map(function ($aid) {
            $receivedDate = \Carbon\Carbon::parse($aid->date_received);
            $daysSinceReceived = \Carbon\Carbon::now()->diffInDays($receivedDate);
            return $daysSinceReceived < 30 ? '0-30 days' : ($daysSinceReceived < 60 ? '31-60 days' : '60+ days');
        })->groupBy(fn($age) => $age)->map->count();

        return response()->json([
            'totalAids' => $totalAids,
            'totalVisitors' => $totalAidVisitors,
            'statusCounts' => $statusCounts,
            'aidTypeCounts' => $aidTypeCounts,
            'beneficiaryAddressCounts' => $beneficiaryAddressCounts,
            'aidAgeGroups' => $aidAgeGroups,
        ], 200);
    }
    public function exportAidsToExcel()
    {
        return Excel::download(new AidsExport, 'orphans.xlsx');
    }
}
