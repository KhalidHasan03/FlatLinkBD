<?php
namespace App\Services;

use App\Models\Deal;
use Illuminate\Support\Facades\DB;

class CommissionService
{
    public function calculate(?float $price, ?string $type, ?float $value): ?float
    {
        if (!$price || !$type || !$value) return null;
        return $type === 'percentage' ? ($price * $value / 100) : $value;
    }

    public function record(Deal $deal, string $type, float $value): Deal
    {
        $amount = $this->calculate($deal->agreed_price, $type, $value);
        $deal->update([
            'commission_type' => $type,
            'commission_value' => $value,
            'commission_amount' => $amount,
        ]);
        return $deal->fresh();
    }

    public function getReport($startDate = null, $endDate = null)
    {
        $query = Deal::where('stage', 'deal_closed')->where('status', 'closed');
        if ($startDate) $query->whereDate('close_date', '>=', $startDate);
        if ($endDate) $query->whereDate('close_date', '<=', $endDate);
        return $query->with(['property', 'buyer', 'seller'])->get();
    }
}
