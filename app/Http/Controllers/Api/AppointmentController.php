<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Property;
use App\Services\NotificationService;
use Illuminate\Http\Request;

class AppointmentController extends Controller
{
    protected NotificationService $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    public function index(Request $request)
    {
        $query = Appointment::with(['property', 'buyer', 'seller']);
        $user = $request->user();
        if ($user->isBuyer()) $query->where('buyer_id', $user->id);
        elseif ($user->isSeller()) $query->where('seller_id', $user->id);
        return response()->json($query->orderBy('scheduled_date', 'desc')->paginate(20));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'property_id' => 'required|exists:properties,id',
            'scheduled_date' => 'required|date|after:today',
            'scheduled_time' => 'nullable',
            'buyer_notes' => 'nullable|string',
        ]);

        $property = Property::findOrFail($validated['property_id']);

        if ((int) $property->seller_id === (int) auth()->id()) {
            return response()->json(['message' => 'You cannot request a visit to your own property.'], 422);
        }

        $validated['buyer_id'] = auth()->id();
        $validated['seller_id'] = $property->seller_id;
        $validated['status'] = 'requested';

        $appointment = Appointment::create($validated);

        $this->notificationService->sendInApp(
            $property->seller_id,
            'appointment_request',
            'New Visit Request',
            auth()->user()->name . ' wants to visit ' . $property->title
        );

        return response()->json($appointment->load(['property', 'buyer', 'seller']), 201);
    }

    public function show(Appointment $appointment)
    {
        $this->authorizeAccess($appointment);

        return response()->json($appointment->load(['property', 'buyer', 'seller']));
    }

    public function update(Request $request, Appointment $appointment)
    {
        $this->authorizeAccess($appointment);

        $validated = $request->validate([
            'status' => 'sometimes|in:requested,confirmed,completed,cancelled,rescheduled',
            'admin_notes' => 'nullable|string',
            'scheduled_date' => 'sometimes|date',
            'scheduled_time' => 'nullable',
        ]);

        $appointment->update($validated);

        if (isset($validated['status'])) {
            $this->notificationService->sendInApp(
                $appointment->buyer_id,
                'appointment_' . $validated['status'],
                'Visit ' . ucfirst($validated['status']),
                'Your visit request for ' . optional($appointment->property)->title . ' is now ' . $validated['status'] . '.'
            );
        }

        return response()->json($appointment->fresh()->load(['property', 'buyer', 'seller']));
    }

    public function destroy(Appointment $appointment)
    {
        $this->authorizeAccess($appointment);
        $appointment->delete();

        return response()->json(['message' => 'Appointment deleted.']);
    }

    protected function authorizeAccess(Appointment $appointment): void
    {
        $user = auth()->user();

        if ($user->isSuperAdmin() || $user->isStaff()) {
            return;
        }

        if ((int) $appointment->buyer_id !== (int) $user->id && (int) $appointment->seller_id !== (int) $user->id) {
            abort(403, 'Forbidden.');
        }
    }
}
