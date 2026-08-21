<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\HouseResource;
use App\Repositories\Contracts\HouseRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class HouseController extends Controller
{
    public function __construct(protected HouseRepositoryInterface $houses) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        return HouseResource::collection(
            $this->houses->paginate($request->user()->society_id, $request->get('search'))
        );
    }

    public function show(int $house): HouseResource
    {
        return new HouseResource($this->houses->find($house));
    }

    public function ledger(int $house): \Illuminate\Http\JsonResponse
    {
        return response()->json(['ledger' => $this->houses->ledger($house)]);
    }
}
