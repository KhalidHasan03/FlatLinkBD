<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Deal;
use App\Models\Property;
use App\Models\User;
use App\Services\DealService;
use App\Services\CommissionService;
use Illuminate\Http\Request;

class DealController extends Controller
{
    protected DealService $dealService;
    protected CommissionService $commissionService;

    public function __construct(DealService $dealService, CommissionService $commissionService)
    {
        $this->dealService = $dealService;
        $this->commissionService = $commissionService;
    }

    public function index(Request $request)
    {
        $query = Deal::with(['property', 'buyer', 'seller', 'documents']);
        $user = $request->user();
        if ($user->isBuyer()) $query->where('buyer_id', $user->id);
        elseif ($user->isSeller()) $query->where('seller_id', $user->id);
        if ($request->stage) $query->where('stage', $request->stage);
        if ($request->status) $query->where('status', $request->status);
        return response()->json($query->orderBy('created_at', 'desc')->paginate(20));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'property_id' => 'required|exists:properties,id',
            'buyer_id' => 'required|exists:users,id',
            'notes' => 'nullable|string',
        ]);

        $property = Property::findOrFail($validated['property_id']);
        $buyer = User::findOrFail($validated['buyer_id']);

        if (!$buyer->isBuyer()) {
            return response()->json(['message' => 'The selected user is not a buyer.'], 422);
        }

        if ((int) $property->seller_id === (int) $buyer->id) {
            return response()->json(['message' => 'A seller cannot open a deal on their own property.'], 422);
        }

        $deal = Deal::firstOrCreate(
            [
                'property_id' => $property->id,
                'buyer_id' => $buyer->id,
            ],
            [
                'seller_id' => $property->seller_id,
                'stage' => 'inquiry',
                'status' => 'active',
                'deal_date' => now()->toDateString(),
                'notes' => $validated['notes'] ?? null,
            ]
        );

        return response()->json($deal->load(['property', 'buyer', 'seller']), $deal->wasRecentlyCreated ? 201 : 200);
    }

    public function show(Deal $deal)
    {
        return response()->json($deal->load(['property', 'buyer', 'seller', 'documents']));
    }

    public function updateStage(Request $request, Deal $deal)
    {
        $validated = $request->validate([
            'stage' => 'required|in:inquiry,visit_done,negotiation,deal_closed,cancelled',
            'agreed_price' => 'nullable|numeric|min:0',
            'commission_type' => 'nullable|in:percentage,fixed',
            'commission_value' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
            'admin_notes' => 'nullable|string',
        ]);
        return response()->json($this->dealService->updateStage($deal, $validated['stage'], $validated));
    }

    public function recordCommission(Request $request, Deal $deal)
    {
        $validated = $request->validate([
            'commission_type' => 'required|in:percentage,fixed',
            'commission_value' => 'required|numeric|min:0',
        ]);
        return response()->json($this->commissionService->record($deal, $validated['commission_type'], $validated['commission_value']));
    }
}
