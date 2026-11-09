<?php
namespace App\Services;

use App\Models\Deal;
use App\Models\ActivityLog;

class DealService
{
    public function updateStage(Deal $deal, string $stage, ?array $data = []): Deal
    {
        unset($data['stage']);

        $updateData = array_merge($data, ['stage' => $stage]);

        $updateData['status'] = match ($stage) {
            'deal_closed' => 'closed',
            'cancelled' => 'cancelled',
            default => 'active',
        };

        if (empty($deal->deal_date)) {
            $updateData['deal_date'] = now()->toDateString();
        }

        if ($stage === 'deal_closed') {
            $price = $updateData['agreed_price'] ?? $deal->agreed_price;
            if ($price !== null) {
                $updateData['commission_amount'] = $this->calculateCommission(
                    (float) $price,
                    $updateData['commission_type'] ?? $deal->commission_type,
                    isset($updateData['commission_value']) ? (float) $updateData['commission_value'] : $deal->commission_value
                );
            }
            $updateData['close_date'] = $updateData['close_date'] ?? now()->toDateString();
        }

        $deal->update($updateData);

        ActivityLog::create([
            'user_id' => auth()->id(),
            'loggable_type' => Deal::class,
            'loggable_id' => $deal->id,
            'action' => 'stage_' . $stage,
            'description' => "Deal stage changed to {$stage}",
        ]);

        return $deal->fresh()->load(['property', 'buyer', 'seller', 'documents']);
    }

    public function calculateCommission(?float $price, ?string $type, ?float $value): ?float
    {
        if (!$price || !$type || !$value) return null;
        return $type === 'percentage' ? ($price * $value / 100) : $value;
    }
}
