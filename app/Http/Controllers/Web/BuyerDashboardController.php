<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Deal;
use App\Models\Favorite;
use App\Models\Property;
use App\Services\NotificationService;
use Illuminate\Http\Request;

class BuyerDashboardController extends Controller
{
    public function __construct(protected NotificationService $notificationService)
    {
    }

    public function index(Request $request)
    {
        $user = $request->user();

        $stats = [
            'favorites' => Favorite::where('user_id', $user->id)->count(),
            'appointments' => Appointment::where('buyer_id', $user->id)->count(),
            'confirmed' => Appointment::where('buyer_id', $user->id)->where('status', 'confirmed')->count(),
            'active_deals' => Deal::where('buyer_id', $user->id)->where('status', 'active')->count(),
        ];

        $favorites = Favorite::with(['property.propertyType', 'property.area', 'property.city', 'property.images'])
            ->where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->get();

        $appointments = Appointment::with(['property', 'seller'])
            ->where('buyer_id', $user->id)
            ->orderByDesc('scheduled_date')
            ->get();

        $deals = Deal::with(['property', 'seller'])
            ->where('buyer_id', $user->id)
            ->orderByDesc('created_at')
            ->get();

        $recommended = Property::with(['propertyType', 'area', 'city', 'images'])
            ->where('status', 'published')
            ->orderByDesc('is_featured')
            ->orderByDesc('views_count')
            ->take(3)
            ->get();

        return view('dashboard.buyer', compact('stats', 'favorites', 'appointments', 'deals', 'recommended'));
    }

    public function requestAppointment(Request $request)
    {
        $validated = $request->validate([
            'property_id' => 'required|exists:properties,id',
            'scheduled_date' => 'required|date|after:today',
            'scheduled_time' => 'nullable|date_format:H:i',
            'buyer_notes' => 'nullable|string|max:1000',
        ]);

        $property = Property::findOrFail($validated['property_id']);
        $user = $request->user();

        if ((int) $property->seller_id === (int) $user->id) {
            return back()->withErrors(['property_id' => 'You cannot request a visit to your own property.']);
        }

        $validated['buyer_id'] = $user->id;
        $validated['seller_id'] = $property->seller_id;
        $validated['status'] = 'requested';

        $appointment = Appointment::create($validated);

        $this->notificationService->sendInApp(
            $property->seller_id,
            'appointment_request',
            'New Visit Request',
            $user->name . ' wants to visit ' . $property->title
        );

        return redirect()->route('buyer.dashboard')->with('success', 'Visit request sent. The seller will confirm shortly.');
    }

    public function cancelAppointment(Request $request, Appointment $appointment)
    {
        if ((int) $appointment->buyer_id !== (int) $request->user()->id) {
            abort(403);
        }

        $appointment->update(['status' => 'cancelled']);

        $this->notificationService->sendInApp(
            $appointment->seller_id,
            'appointment_cancelled',
            'Visit Cancelled',
            optional($appointment->property)->title . ' visit was cancelled by the buyer.'
        );

        return redirect()->route('buyer.dashboard')->with('success', 'Appointment cancelled.');
    }
}
