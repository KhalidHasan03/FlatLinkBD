<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Property;
use App\Models\Deal;
use App\Models\Appointment;
use App\Models\User;
use App\Models\Contact;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminDashboardController extends Controller
{
    public function stats()
    {
        return response()->json([
            'total_properties' => Property::count(),
            'pending_verification' => Property::where('verification_status', 'pending')->count(),
            'published_properties' => Property::where('status', 'published')->count(),
            'total_deals' => Deal::count(),
            'active_deals' => Deal::where('status', 'active')->count(),
            'closed_deals' => Deal::where('stage', 'deal_closed')->count(),
            'total_sellers' => User::where('role', 'seller')->count(),
            'total_buyers' => User::where('role', 'buyer')->count(),
            'pending_appointments' => Appointment::where('status', 'requested')->count(),
            'total_commission' => Deal::where('stage', 'deal_closed')->sum('commission_amount'),
            'unread_messages' => Contact::where('is_read', false)->count(),
        ]);
    }

    public function recentActivity()
    {
        $activities = ActivityLog::with('user')
            ->orderBy('created_at', 'desc')
            ->take(20)
            ->get();
        return response()->json($activities);
    }

    public function propertiesByStatus()
    {
        return response()->json([
            'draft' => Property::where('status', 'draft')->count(),
            'published' => Property::where('status', 'published')->count(),
            'archived' => Property::where('status', 'archived')->count(),
        ]);
    }

    public function dealsByStage()
    {
        return response()->json([
            'inquiry' => Deal::where('stage', 'inquiry')->count(),
            'visit_done' => Deal::where('stage', 'visit_done')->count(),
            'negotiation' => Deal::where('stage', 'negotiation')->count(),
            'deal_closed' => Deal::where('stage', 'deal_closed')->count(),
            'cancelled' => Deal::where('stage', 'cancelled')->count(),
        ]);
    }

    public function monthlyCommission(Request $request)
    {
        $year = $request->get('year', date('Y'));
        $months = [];
        for ($i = 1; $i <= 12; $i++) {
            $months[] = Deal::where('stage', 'deal_closed')
                ->whereYear('close_date', $year)
                ->whereMonth('close_date', $i)
                ->sum('commission_amount');
        }
        return response()->json(['year' => $year, 'months' => $months]);
    }
}
