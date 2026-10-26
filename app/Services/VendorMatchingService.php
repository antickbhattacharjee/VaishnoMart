<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Setting;
use App\Models\VendorAssignment;
use App\Models\VendorProfile;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class VendorMatchingService
{
    /**
     * Find all eligible vendors for an order ranked by distance and feasibility.
     *
     * @param float $deliveryLat Customer latitude
     * @param float $deliveryLng Customer longitude
     * @param array $items Array of ['product_id' => int, 'quantity' => int]
     * @param int|null $preferredVendorId
     * @param array $excludedVendorIds
     * @return array Array of ['vendor' => VendorProfile, 'distance_km' => float, 'score' => float]
     */
    public function findRankedVendors(
        float $deliveryLat,
        float $deliveryLng,
        array $items,
        ?int $preferredVendorId = null,
        array $excludedVendorIds = []
    ): array {
        $productIds = array_column($items, 'product_id');

        // Build query of active vendors who are open and accepting orders
        $vendorsQuery = VendorProfile::with(['products' => function ($q) use ($productIds) {
            $q->whereIn('id', $productIds);
        }])
            ->approved()
            ->where('is_open', true)
            ->where('is_accepting_orders', true);

        if (!empty($excludedVendorIds)) {
            $vendorsQuery->whereNotIn('id', $excludedVendorIds);
        }

        if ($preferredVendorId) {
            // When order was placed directly for a specific vendor's cart
            $vendorsQuery->where('id', $preferredVendorId);
        }

        $vendors = $vendorsQuery->get();
        $ranked = [];

        foreach ($vendors as $vendor) {
            // 1. Check distance and serviceability radius
            $distance = $vendor->distanceFrom($deliveryLat, $deliveryLng);
            if ($distance > (float) $vendor->service_radius_km) {
                continue;
            }

            // 2. Verify stock sufficiency for all order items
            $canFulfilAll = true;
            foreach ($items as $item) {
                $product = Product::where('vendor_profile_id', $vendor->id)
                    ->where(function ($q) use ($item) {
                        $q->where('id', $item['product_id']);
                    })
                    ->first();

                if (!$product || !$product->is_active || !$product->is_available || $product->stock_quantity < $item['quantity']) {
                    $canFulfilAll = false;
                    break;
                }
            }

            if (!$canFulfilAll) {
                continue;
            }

            // 3. Compute ranking score: lower distance is better, lower prep time is better
            // Rank score formula: distance (weight 70%) + prep time in hours (weight 30%)
            $prepTimeHours = ($vendor->prep_time_minutes ?? 15) / 60;
            $score = ($distance * 0.7) + ($prepTimeHours * 0.3);

            $ranked[] = [
                'vendor' => $vendor,
                'distance_km' => $distance,
                'score' => $score,
            ];
        }

        // Sort ascending by score (nearest / best ranked first)
        usort($ranked, function ($a, $b) {
            return $a['score'] <=> $b['score'];
        });

        return $ranked;
    }

    /**
     * Offer the order to the next eligible vendor.
     */
    public function assignNextVendor(Order $order): ?VendorAssignment
    {
        $deliveryAddress = $order->deliveryAddress;
        if (!$deliveryAddress || $deliveryAddress->latitude === null || $deliveryAddress->longitude === null) {
            return null;
        }

        // Get previously rejected or expired assignments for this order
        $excludedVendorIds = VendorAssignment::where('order_id', $order->id)
            ->pluck('vendor_profile_id')
            ->toArray();

        $items = $order->items->map(function (OrderItem $item) {
            return [
                'product_id' => $item->product_id,
                'quantity' => $item->quantity,
            ];
        })->toArray();

        $ranked = $this->findRankedVendors(
            (float) $deliveryAddress->latitude,
            (float) $deliveryAddress->longitude,
            $items,
            null,
            $excludedVendorIds
        );

        if (empty($ranked)) {
            return null;
        }

        $nextCandidate = $ranked[0];
        $timeoutMinutes = (int) env('VENDOR_ACCEPTANCE_TIMEOUT_MINUTES', 5);
        $rankIndex = count($excludedVendorIds) + 1;

        return DB::transaction(function () use ($order, $nextCandidate, $timeoutMinutes, $rankIndex) {
            // Update order's current vendor pointer
            $order->update([
                'vendor_profile_id' => $nextCandidate['vendor']->id,
                'status' => Order::STATUS_PENDING_VENDOR,
                'vendor_acceptance_deadline' => Carbon::now()->addMinutes($timeoutMinutes),
            ]);

            return VendorAssignment::create([
                'order_id' => $order->id,
                'vendor_profile_id' => $nextCandidate['vendor']->id,
                'status' => 'offered',
                'distance_km' => $nextCandidate['distance_km'],
                'rank' => $rankIndex,
                'offered_at' => Carbon::now(),
                'expires_at' => Carbon::now()->addMinutes($timeoutMinutes),
            ]);
        });
    }

    /**
     * Process expired vendor offers and attempt reassignment.
     */
    public function processExpiredVendorOffers(): int
    {
        $expiredAssignments = VendorAssignment::where('status', 'offered')
            ->where('expires_at', '<', Carbon::now())
            ->get();

        $reassignedCount = 0;

        foreach ($expiredAssignments as $assignment) {
            DB::transaction(function () use ($assignment, &$reassignedCount) {
                $assignment->update([
                    'status' => 'expired',
                    'responded_at' => Carbon::now(),
                    'rejection_reason' => 'Vendor acceptance window expired automatically.',
                ]);

                $order = $assignment->order;
                if ($order && $order->status === Order::STATUS_PENDING_VENDOR) {
                    $nextAssignment = $this->assignNextVendor($order);
                    if (!$nextAssignment) {
                        // No more eligible vendors available
                        $order->update([
                            'status' => Order::STATUS_CANCELLED,
                            'cancellation_reason' => 'No nearby vendor was available to fulfill the order at this time.',
                            'cancelled_at' => Carbon::now(),
                        ]);
                    } else {
                        $reassignedCount++;
                    }
                }
            });
        }

        return $reassignedCount;
    }
}
