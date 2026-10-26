<?php

namespace App\Services;

use App\Models\DeliveryAgentProfile;
use App\Models\DeliveryAssignment;
use App\Models\Order;
use App\Models\OrderStatusHistory;
use App\Models\SystemNotification;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DeliveryAssignmentService
{
    /**
     * Request delivery for an order by broadcasting requests to eligible nearby agents.
     */
    public function requestDeliveryAgents(Order $order): int
    {
        $vendor = $order->vendor;
        if (!$vendor) {
            return 0;
        }

        // Find approved, online, available agents
        $agentProfiles = DeliveryAgentProfile::where('status', 'approved')
            ->where('is_online', true)
            ->where('is_available', true)
            ->get();

        $offeredCount = 0;

        foreach ($agentProfiles as $profile) {
            $distance = $profile->distanceFrom((float) $vendor->latitude, (float) $vendor->longitude);

            // If coordinates exist, ensure agent is within their serviceable radius
            if ($distance !== null && $distance > (float) $profile->service_radius_km) {
                continue;
            }

            // Create delivery offer if not already offered
            $exists = DeliveryAssignment::where('order_id', $order->id)
                ->where('delivery_agent_id', $profile->user_id)
                ->exists();

            if (!$exists) {
                DeliveryAssignment::create([
                    'order_id' => $order->id,
                    'delivery_agent_id' => $profile->user_id,
                    'status' => 'offered',
                    'distance_km' => $distance,
                    'offered_at' => Carbon::now(),
                ]);

                SystemNotification::send(
                    $profile->user_id,
                    'New Delivery Request Available',
                    "Order #{$order->order_number} is ready for pickup at {$vendor->business_name}.",
                    'order_update',
                    "/agent/deliveries"
                );

                $offeredCount++;
            }
        }

        $order->update([
            'status' => Order::STATUS_DELIVERY_REQUESTED,
            'delivery_requested_at' => Carbon::now(),
        ]);

        return $offeredCount;
    }

    /**
     * Atomic acceptance by a delivery agent.
     * Prevents race condition where multiple agents try to accept the same delivery.
     */
    public function acceptDelivery(Order $order, User $agent): array
    {
        return DB::transaction(function () use ($order, $agent) {
            // Re-fetch order with row locking to ensure atomic assignment
            $lockedOrder = Order::where('id', $order->id)->lockForUpdate()->first();

            if ($lockedOrder->delivery_agent_id !== null && $lockedOrder->delivery_agent_id !== $agent->id) {
                return [
                    'success' => false,
                    'message' => 'This delivery assignment has already been accepted by another delivery partner.',
                ];
            }

            if (!in_array($lockedOrder->status, [Order::STATUS_READY_FOR_PICKUP, Order::STATUS_DELIVERY_REQUESTED], true)) {
                return [
                    'success' => false,
                    'message' => 'This order is not currently open for delivery acceptance.',
                ];
            }

            // Mark agent assignment as accepted
            DeliveryAssignment::updateOrCreate(
                ['order_id' => $lockedOrder->id, 'delivery_agent_id' => $agent->id],
                [
                    'status' => 'accepted',
                    'responded_at' => Carbon::now(),
                ]
            );

            // Cancel any other open offers for this order
            DeliveryAssignment::where('order_id', $lockedOrder->id)
                ->where('delivery_agent_id', '!=', $agent->id)
                ->where('status', 'offered')
                ->update(['status' => 'cancelled']);

            // Update order status
            $lockedOrder->update([
                'delivery_agent_id' => $agent->id,
                'status' => Order::STATUS_DELIVERY_ASSIGNED,
                'delivery_assigned_at' => Carbon::now(),
            ]);

            // Mark agent as busy (optional: depending on agent capacity policy)
            if ($agent->deliveryAgentProfile) {
                $agent->deliveryAgentProfile->update(['is_available' => false]);
            }

            // Log event history
            OrderStatusHistory::create([
                'order_id' => $lockedOrder->id,
                'previous_status' => Order::STATUS_DELIVERY_REQUESTED,
                'new_status' => Order::STATUS_DELIVERY_ASSIGNED,
                'actor_id' => $agent->id,
                'actor_role' => User::ROLE_DELIVERY_AGENT,
                'comment' => "Delivery accepted by agent {$agent->name}.",
            ]);

            // Notify customer and vendor
            SystemNotification::send(
                $lockedOrder->customer_id,
                'Delivery Partner Assigned',
                "{$agent->name} has been assigned to pick up and deliver your order #{$lockedOrder->order_number}.",
                'order_update',
                "/customer/orders/{$lockedOrder->id}"
            );

            if ($lockedOrder->vendor && $lockedOrder->vendor->user_id) {
                SystemNotification::send(
                    $lockedOrder->vendor->user_id,
                    'Delivery Partner Assigned',
                    "Delivery agent {$agent->name} is on the way to pick up order #{$lockedOrder->order_number}.",
                    'order_update',
                    "/vendor/orders/{$lockedOrder->id}"
                );
            }

            return [
                'success' => true,
                'message' => 'Delivery accepted successfully. Please proceed to the store for pickup.',
            ];
        });
    }
}
