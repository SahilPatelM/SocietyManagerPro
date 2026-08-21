<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Member\StoreMemberRequest;
use App\Http\Requests\Member\UpdateMemberRequest;
use App\Http\Resources\MemberResource;
use App\Repositories\Contracts\MemberRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class MemberController extends Controller
{
    public function __construct(protected MemberRepositoryInterface $members) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $paginated = $this->members->paginate(
            $request->user()->society_id,
            $request->get('search')
        );

        return MemberResource::collection($paginated);
    }

    public function store(StoreMemberRequest $request): MemberResource
    {
        $data = array_merge($request->validated(), [
            'society_id' => $request->user()->society_id,
        ]);
        $user = $this->members->create($data);
        $user->assignRole('member');

        return new MemberResource($user);
    }

    public function show(int $member): MemberResource
    {
        return new MemberResource($this->members->find($member));
    }

    public function update(UpdateMemberRequest $request, int $member): MemberResource
    {
        $user = $this->members->find($member);

        return new MemberResource($this->members->update($user, $request->validated()));
    }

    public function destroy(int $member): \Illuminate\Http\JsonResponse
    {
        $user = $this->members->find($member);
        $user->update(['status' => 'inactive']);

        return response()->json(['message' => 'Member deactivated']);
    }
}
