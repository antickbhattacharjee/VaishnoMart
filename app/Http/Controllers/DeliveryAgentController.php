<?php

namespace App\Http\Controllers;

use App\Models\DeliveryAgentEarning;
use App\Models\DeliveryAgentProfile;
use App\Models\DeliveryAssignment;
use App\Models\Order;
use App\Services\DeliveryAssignmentService;
use App\Services\OrderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DeliveryAgentController extends Controller
{
    public function __construct(
        protected DeliveryAssignmentService $deliveryService,
        protected OrderService $orderService
    ) {}

    protected function getAgentProfile(): DeliveryAgentProfile
    {
        $profile = Auth::user()->deliveryAgentProfile;
        if (!$profile) {
            abort(403, 'No delivery agent profile associated with this account.');
        }
        return $profile;
    }

    public function dashboard()
    {
        $agent = Auth::user();
        $profile = $this->getAgentProfile();

        // Check if agent currently has an active delivery in progress
        $currentOrder = Order::with(['vendor', 'customer', 'items', 'deliveryAddress', 'pickupAddress'])
            ->where('delivery_agent_id', $agent->id)
            ->whereIn('status', [Order::STATUS_DELIVERY_ASSIGNED, Order::STATUS_PICKED_UP, Order::STATUS_OUT_FOR_DELIVERY])
            ->first();

        // Available requests open for acceptance
        $availableAssignments = DeliveryAssignment::with(['order.vendor', 'order.deliveryAddress'])
            ->where('delivery_agent_id', $agent->id)
            ->where('status', 'offered')
            ->whereHas('order', function ($q) {
                $q->whereIn('status', [Order::STATUS_READY_FOR_PICKUP, Order::STATUS_DELIVERY_REQUESTED]);
            })
            ->latest('id')
            ->get();

        $todayEarnings = DeliveryAgentEarning::where('delivery_agent_id', $agent->id)
            ->whereDate('created_at', today())
            ->sum('total_earning');

        $completedCount = Order::where('delivery_agent_id', $agent->id)
            ->where('status', Order::STATUS_DELIVERED)
            ->count();

        return view('agent.dashboard', compact(
            'agent',
            'profile',
            'currentOrder',
            'availableAssignments',
            'todayEarnings',
            'completedCount'
        ));
    }

    public function toggleOnline(Request $request)
    {
        $profile = $this->getAgentProfile();
        $profile->is_online = !$profile->is_online;
        $profile->is_available = $profile->is_online;
        $profile->save();

        $statusText = $profile->is_online ? 'online and ready for requests' : 'offline';
        return back()->with('info', "You are now {$statusText}.");
    }

    public function acceptDelivery(int $orderId)
    {
        $agent = Auth::user();
        $order = Order::findOrFail($orderId);

        $result = $this->deliveryService->acceptDelivery($order, $agent);

        if (!$result['success']) {
            return back()->withErrors(['accept' => $result['message']]);
        }

        return redirect()->route('agent.current-order')
            ->with('success', $result['message']);
    }

    public function currentOrder()
    {
        $agent = Auth::user();
        $order = Order::with(['vendor', 'customer', 'items.product', 'deliveryAddress', 'pickupAddress'])
            ->where('delivery_agent_id', $agent->id)
            ->whereIn('status', [Order::STATUS_DELIVERY_ASSIGNED, Order::STATUS_PICKED_UP, Order::STATUS_OUT_FOR_DELIVERY])
            ->first();

        if (!$order) {
            return redirect()->route('agent.dashboard')->with('info', 'No active delivery at the moment.');
        }

        return view('agent.current-order', compact('agent', 'order'));
    }

    public function verifyPickup(Request $request, int $orderId)
    {
        $validated = $request->validate([
            'pickup_otp' => ['required', 'string', 'min:4', 'max:8'],
        ]);

        $agent = Auth::user();
        $order = Order::where('delivery_agent_id', $agent->id)->findOrFail($orderId);

        $result = $this->orderService->verifyPickup($order, $agent, $validated['pickup_otp']);

        if (!$result['success']) {
            return back()->withErrors(['pickup_otp' => $result['message']]);
        }

        return redirect()->route('agent.current-order')->with('success', $result['message']);
    }

    public function verifyDelivery(Request $request, int $orderId)
    {
        $validated = $request->validate([
            'delivery_otp' => ['required', 'string', 'min:4', 'max:8'],
        ]);

        $agent = Auth::user();
        $order = Order::where('delivery_agent_id', $agent->id)->findOrFail($orderId);

        $result = $this->orderService->verifyDelivery($order, $agent, $validated['delivery_otp']);

        if (!$result['success']) {
            return back()->withErrors(['delivery_otp' => $result['message']]);
        }

        return redirect()->route('agent.dashboard')->with('success', $result['message']);
    }

    public function resendOtp(int $orderId)
    {
        $agent = Auth::user();
        $order = Order::where('delivery_agent_id', $agent->id)->findOrFail($orderId);

        if ($order->status === Order::STATUS_OUT_FOR_DELIVERY) {
            $result = $this->orderService->resendDeliveryOtp($order, $agent);
        } else {
            $result = $this->orderService->resendPickupOtp($order, $agent);
        }

        if (!$result['success']) {
            return back()->withErrors(['error' => $result['message']]);
        }

        return back()->with('success', $result['message']);
    }

    public function history()
    {
        $agent = Auth::user();
        $orders = Order::with(['vendor', 'deliveryAddress'])
            ->where('delivery_agent_id', $agent->id)
            ->where('status', Order::STATUS_DELIVERED)
            ->latest('id')
            ->paginate(15);

        return view('agent.history', compact('agent', 'orders'));
    }

    public function earnings()
    {
        $agent = Auth::user();
        $earnings = DeliveryAgentEarning::with('order')
            ->where('delivery_agent_id', $agent->id)
            ->latest('id')
            ->paginate(15);

        $totalEarned = DeliveryAgentEarning::where('delivery_agent_id', $agent->id)->sum('total_earning');

        return view('agent.earnings', compact('agent', 'earnings', 'totalEarned'));
    }
}
