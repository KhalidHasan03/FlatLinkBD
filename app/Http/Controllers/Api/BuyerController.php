<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Deal;
use App\Models\Favorite;
use App\Models\Property;
use App\Services\NotificationService;
use Illuminate\Http\Request;

class BuyerController extends Controller
{
    protected NotificationService $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    public function dashboard()
    {
        $user = auth()->user();
        return response()->json([
            'total_appointments' => Appointment::where('buyer_id', $user->id)->count(),
            'upcoming_appointments' => Appointment::where('buyer_id', $user->id)->where('status', 'confirmed')->count(),
            'active_deals' => Deal::where('buyer_id', $user->id)->where('status', 'active')->count(),
            'favorite_count' => Favorite::where('user_id', $user->id)->count(),
        ]);
    }

    public function appointments()
    {
        return response()->json(
            Appointment::with(['property', 'seller'])
                ->where('buyer_id', auth()->id())
                ->orderBy('scheduled_date', 'desc')
                ->get()
        );
    }

    public function requestAppointment(Request $request)
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
        return response()->json($appointment->load(['property', 'seller']), 201);
    }

    public function deals()
    {
        return response()->json(
            Deal::with(['property', 'seller'])
                ->where('buyer_id', auth()->id())
                ->orderBy('created_at', 'desc')
                ->get()
        );
    }
}
