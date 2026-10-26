<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Http\Request;

class OrderApiController extends Controller
{
    public function __construct(
        protected OrderService $orderService
    ) {}

    public function customerOrders(Request $request)
    {
        $orders = Order::with(['vendor', 'items'])
            ->where('customer_id', $request->user()->id)
            ->latest('id')
            ->paginate(10);

        return response()->json($orders);
    }

    public function show(Request $request, int $id)
    {
        $user = $request->user();
        $order = Order::with(['vendor', 'deliveryAgent', 'items', 'deliveryAddress', 'statusHistory'])
            ->where(function ($q) use ($user) {
                if ($user->isCustomer()) {
                    $q->where('customer_id', $user->id);
                } elseif ($user->isVendor()) {
                    $q->where('vendor_profile_id', $user->vendorProfile?->id);
                } elseif ($user->isDeliveryAgent()) {
                    $q->where('delivery_agent_id', $user->id);
                }
            })
            ->findOrFail($id);

        return response()->json(['order' => $order]);
    }
}
