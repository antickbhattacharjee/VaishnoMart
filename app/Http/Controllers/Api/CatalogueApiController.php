<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\VendorProfile;
use Illuminate\Http\Request;

class CatalogueApiController extends Controller
{
    public function categories()
    {
        $categories = Category::active()->orderBy('display_order')->get();
        return response()->json(['categories' => $categories]);
    }

    public function nearbyVendors(Request $request)
    {
        $lat = (float) $request->get('lat', 28.6315);
        $lng = (float) $request->get('lng', 77.2200);

        $vendors = VendorProfile::with('primaryCategory')
            ->approved()
            ->where('is_open', true)
            ->get()
            ->filter(function ($vendor) use ($lat, $lng) {
                $distance = $vendor->distanceFrom($lat, $lng);
                $vendor->distance_from_user = $distance;
                return $distance <= (float) $vendor->service_radius_km;
            })
            ->sortBy('distance_from_user')
            ->values();

        return response()->json(['vendors' => $vendors]);
    }

    public function products(Request $request)
    {
        $query = Product::with(['vendor', 'category'])->active();

        if ($request->has('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->has('vendor_id')) {
            $query->where('vendor_profile_id', $request->vendor_id);
        }

        if ($request->has('q')) {
            $term = $request->q;
            $query->where('name', 'like', "%{$term}%");
        }

        $products = $query->paginate(20);
        return response()->json($products);
    }
}
