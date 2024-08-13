<?php

namespace App\Http\Controllers;

use App\Models\Orphan;
use Illuminate\Http\Request;
use App\Exports\OrphansExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Validator;

class OrphanController extends Controller
{
    // Method to create a new orphan record
    public function create(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'orphan_id_number' => 'required|string|min:9|unique:orphans,orphan_id_number',
            'orphan_full_name' => 'required|string|min:3',
            'orphan_birth_date' => 'required|date',
            'health_status' => 'required|in:جيدة,مريض',
            'disease_description' => 'nullable|string',
            'original_address' => 'required|in:رفح,غزة,خانيونس,بيت حانون,جباليا,بيت لاهيا,دير البلح,النصيرات,الزوايدة,المغازي,البريج,جحر الديك',
            'current_address' => 'required|in:رفح,غزة,خانيونس,بيت حانون,جباليا,بيت لاهيا,دير البلح,النصيرات,الزوايدة,المغازي,البريج,جحر الديك',
            'deceased_father_full_name' => 'required|string|min:3',
            'deceased_father_birth_date' => 'required|date',
            'death_date' => 'required|date',
            'death_cause' => 'required|in:شهيد حرب,وفاة طبيعية,وفاة بسبب المرض',
            'previous_father_job' => 'nullable|string',
            'number_of_siplings' => 'required|integer|min:0',
            'mother_full_name' => 'required|string|min:3',
            'mother_id_number' => 'required|string|min:9',
            'mother_birth_date' => 'required|date',
            'mother_death_date' => 'nullable|date',
            'mother_status' => 'required|in:أرملة,متزوجة',
            'mother_job' => 'required|string|min:3',
            'guardian_full_name' => 'required|string|min:3',
            'guardian_relationship' => 'required|string|min:2',
            'guardian_phone_number' => 'required|string|regex:/^([0-9\s\-\+\(\)]*)$/|min:10',
            'alternative_phone_number' => 'nullable|string|regex:/^([0-9\s\-\+\(\)]*)$/|min:10',
            'is_enrolled_in_memorization_center' => 'required|in:نعم,لا',
            'orphan_photo' => 'nullable|file|image|max:2048',
            'data_approval_name' => 'required|string|min:3',
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

        try {
            $orphan = Orphan::create($orphanData);
            return response()->json(['orphan' => $orphan], 201);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Internal Server Error'], 500);
        }
    }

    public function fetchOrphans(Request $request)
    {
        // Extract query parameters
        $searchQuery = $request->query('searchQuery');
        $perPage = $request->query('perPage', 10);
        $page = $request->query('page', 1);
        $limit = (int) $perPage;
        $offset = ($page - 1) * $limit;

        // List of searchable fields
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
            'number_of_siblings'
        ];

        // Build the query
        $query = Orphan::query();
        if ($searchQuery) {
            $query->where(function (Builder $query) use ($searchFields, $searchQuery) {
                foreach ($searchFields as $field) {
                    $query->orWhere($field, 'LIKE', "%{$searchQuery}%");
                }
            });
        }

        // Get the total number of records
        $totalOrphans = $query->count();

        // Fetch paginated results
        $orphans = $query->orderBy('created_at', 'DESC')
            ->offset($offset)
            ->limit($limit)
            ->get();

        // Return response
        return response()->json([
            'orphans' => $orphans,
            'totalOrphans' => $totalOrphans,
            'totalPages' => ceil($totalOrphans / $limit),
            'currentPage' => $page
        ], 200);
    }


    public function exportOrphansToExcel()
    {
        return Excel::download(new OrphansExport, 'orphans.xlsx');
    }
}
