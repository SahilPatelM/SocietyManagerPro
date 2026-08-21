<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\DashboardService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct(protected DashboardService $dashboard) {}

    public function index(Request $request): JsonResponse
    {
        $societyId = $request->user()->society_id;

        return response()->json($this->dashboard->stats($societyId));
    }

    public function charts(Request $request): JsonResponse
    {
        return response()->json($this->dashboard->charts($request->user()->society_id));
    }
}
