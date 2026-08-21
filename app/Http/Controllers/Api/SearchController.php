<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Complaint;
use App\Models\House;
use App\Repositories\Contracts\MemberRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function __construct(protected MemberRepositoryInterface $members) {}

    public function global(Request $request): JsonResponse
    {
        $q = $request->get('q', '');
        $societyId = $request->user()->society_id;

        return response()->json([
            'members' => $this->members->search($societyId, $q),
            'houses' => House::where('society_id', $societyId)
                ->where('house_number', 'like', "%{$q}%")
                ->limit(10)->get(),
            'complaints' => Complaint::where('society_id', $societyId)
                ->where('complaint_number', 'like', "%{$q}%")
                ->limit(10)->get(),
        ]);
    }
}
