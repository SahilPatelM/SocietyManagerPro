<?php

namespace App\Http\Controllers\Api;

use App\Enums\ComplaintCategory;
use App\Enums\ComplaintStatus;
use App\Http\Controllers\Controller;
use App\Http\Resources\ComplaintResource;
use App\Models\Complaint;
use App\Services\ComplaintService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ComplaintController extends Controller
{
    public function __construct(
        protected ComplaintService $service,
    ) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $complaints = Complaint::with(['house', 'user', 'attachments'])
            ->where('society_id', $request->user()->society_id)
            ->when($request->user()->hasRole('member'), fn ($q) => $q->where('user_id', $request->user()->id))
            ->latest()
            ->paginate(15);

        return ComplaintResource::collection($complaints);
    }

    public function store(Request $request): ComplaintResource
    {
        $data = $request->validate([
            'category' => 'required|in:'.implode(',', array_column(ComplaintCategory::cases(), 'value')),
            'title' => 'required|string|max:255',
            'description' => 'required|string|max:5000',
            'house_id' => 'nullable|exists:houses,id',
            'photo' => 'nullable|image|mimes:jpeg,jpg,png,webp|max:5120',
        ]);

        $complaint = $this->service->submit(
            $request->user(),
            $data,
            $request->file('photo'),
        );

        return new ComplaintResource($complaint);
    }

    public function show(Request $request, Complaint $complaint): ComplaintResource
    {
        $this->authorizeComplaint($request, $complaint);

        return new ComplaintResource($complaint->load(['attachments', 'house', 'user']));
    }

    public function update(Request $request, Complaint $complaint): ComplaintResource
    {
        $this->authorizeComplaint($request, $complaint, adminOnly: true);

        $data = $request->validate([
            'status' => 'sometimes|in:pending,in_progress,resolved',
            'admin_remarks' => 'nullable|string|max:2000',
        ]);

        $complaint->fill($data);

        if (($data['status'] ?? null) === ComplaintStatus::Resolved->value) {
            $complaint->resolved_at = now();
        }

        $complaint->save();

        return new ComplaintResource($complaint->fresh(['attachments', 'house', 'user']));
    }

    protected function authorizeComplaint(Request $request, Complaint $complaint, bool $adminOnly = false): void
    {
        abort_unless($complaint->society_id === $request->user()->society_id, 404);

        if ($adminOnly) {
            abort_unless($request->user()->hasAnyRole(['society_admin', 'treasurer', 'super_admin']), 403);

            return;
        }

        if ($request->user()->hasRole('member')) {
            abort_unless($complaint->user_id === $request->user()->id, 403);
        }
    }
}
