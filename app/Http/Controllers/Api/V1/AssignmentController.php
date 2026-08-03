<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\AssignWorkOrderRequest;
use App\Http\Requests\Api\V1\ReasonRequest;
use App\Http\Resources\Api\V1\AssignmentResource;
use App\Models\User;
use App\Modules\Assignment\Models\Assignment;
use App\Modules\Assignment\Services\AssignmentService;
use App\Modules\WorkOrder\Models\WorkOrder;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class AssignmentController extends Controller
{
    public function store(
        AssignWorkOrderRequest $request,
        WorkOrder $workOrder,
        AssignmentService $assignments,
    ): AnonymousResourceCollection {
        $this->authorize('assign', $workOrder);

        /** @var User $actor */
        $actor = $request->user();

        return AssignmentResource::collection(
            $assignments->assignMany($workOrder, $request->validated('technician_ids'), $actor),
        );
    }

    public function accept(Assignment $assignment, AssignmentService $assignments): AssignmentResource
    {
        $this->authorize('respond', $assignment);

        /** @var User $actor */
        $actor = request()->user();

        return new AssignmentResource($assignments->accept($assignment, $actor));
    }

    public function reject(ReasonRequest $request, Assignment $assignment, AssignmentService $assignments): AssignmentResource
    {
        $this->authorize('respond', $assignment);

        /** @var User $actor */
        $actor = $request->user();

        return new AssignmentResource($assignments->reject($assignment, $actor, $request->validated('reason')));
    }
}
