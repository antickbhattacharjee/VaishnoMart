<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\VendorProfile;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index(Request $request)
    {
        // Default coordinates: Connaught Place, Central Delhi (28.6315, 77.2200)
        $userLat = (float) $request->get('lat', session('customer_lat', 28.6315));
        $userLng = (float) $request->get('lng', session('customer_lng', 77.2200));

        session(['customer_lat' => $userLat, 'customer_lng' => $userLng]);

        $categories = Category::active()->orderBy('display_order')->get();

        // Nearby approved vendors
        $allVendors = VendorProfile::with('primaryCategory')
            ->approved()
            ->where('is_open', true)
            ->get();

        $nearbyVendors = $allVendors->filter(function ($vendor) use ($userLat, $userLng) {
            $distance = $vendor->distanceFrom($userLat, $userLng);
            $vendor->distance_from_user = $distance;
            return $distance <= (float) $vendor->service_radius_km;
        })->sortBy('distance_from_user')->values();

        // Featured category products
        $waterCategory = $categories->firstWhere('slug', 'drinking-water');
        $waterProducts = $waterCategory ? Product::with(['vendor', 'category'])
            ->active()
            ->where('category_id', $waterCategory->id)
            ->take(4)
            ->get() : collect();

        $foodCategory = $categories->firstWhere('slug', 'cloud-kitchens');
        $foodProducts = $foodCategory ? Product::with(['vendor', 'category'])
            ->active()
            ->where('category_id', $foodCategory->id)
            ->take(4)
            ->get() : collect();

        $dryFruitsCategory = $categories->firstWhere('slug', 'dry-fruits');
        $dryFruitsProducts = $dryFruitsCategory ? Product::with(['vendor', 'category'])
            ->active()
            ->where('category_id', $dryFruitsCategory->id)
            ->take(4)
            ->get() : collect();

        $groceryCategory = $categories->firstWhere('slug', 'grocery');
        $groceryProducts = $groceryCategory ? Product::with(['vendor', 'category'])
            ->active()
            ->where('category_id', $groceryCategory->id)
            ->take(4)
            ->get() : collect();

        $popularProducts = Product::with(['vendor', 'category'])
            ->active()
            ->inStock()
            ->take(8)
            ->get();

        return view('home', compact(
            'categories',
            'nearbyVendors',
            'waterProducts',
            'foodProducts',
            'dryFruitsProducts',
            'groceryProducts',
            'popularProducts',
            'userLat',
            'userLng'
        ));
    }

    public function setLocation(Request $request)
    {
        $validated = $request->validate([
            'lat' => ['required', 'numeric'],
            'lng' => ['required', 'numeric'],
            'location_name' => ['nullable', 'string'],
        ]);

        session([
            'customer_lat' => (float) $validated['lat'],
            'customer_lng' => (float) $validated['lng'],
            'customer_location_name' => $validated['location_name'] ?? 'Selected Location',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Delivery location updated.',
            'location' => session('customer_location_name'),
        ]);
    }

    public function category(string $slug)
    {
        $category = Category::where('slug', $slug)->active()->firstOrFail();

        $products = Product::with(['vendor', 'category'])
            ->active()
            ->where('category_id', $category->id)
            ->paginate(12);

        $vendors = VendorProfile::approved()
            ->where('primary_category_id', $category->id)
            ->get();

        return view('category', compact('category', 'products', 'vendors'));
    }

    public function vendor(int $id)
    {
        $vendor = VendorProfile::with(['primaryCategory', 'reviews.customer'])
            ->approved()
            ->findOrFail($id);

        $products = Product::with('category')
            ->where('vendor_profile_id', $vendor->id)
            ->active()
            ->paginate(12);

        $userLat = (float) session('customer_lat', 28.6315);
        $userLng = (float) session('customer_lng', 77.2200);
        $distance = $vendor->distanceFrom($userLat, $userLng);

        return view('vendor-detail', compact('vendor', 'products', 'distance'));
    }

    public function product(string $slug)
    {
        $product = Product::with(['vendor.primaryCategory', 'category', 'variants', 'images'])
            ->where('slug', $slug)
            ->active()
            ->firstOrFail();

        $relatedProducts = Product::with(['vendor', 'category'])
            ->where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->active()
            ->take(4)
            ->get();

        return view('product-detail', compact('product', 'relatedProducts'));
    }

    public function search(Request $request)
    {
        $query = trim($request->get('q', ''));
        $categoryId = $request->get('category');
        $maxPrice = $request->get('max_price');

        $productsQuery = Product::with(['vendor', 'category'])->active();

        if (!empty($query)) {
            $productsQuery->where(function ($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                    ->orWhere('description', 'like', "%{$query}%");
            });
        }

        if (!empty($categoryId)) {
            $productsQuery->where('category_id', $categoryId);
        }

        if (!empty($maxPrice)) {
            $productsQuery->where('base_price', '<=', (float) $maxPrice);
        }

        $products = $productsQuery->paginate(12)->withQueryString();

        $vendors = VendorProfile::approved()
            ->when(!empty($query), function ($q) use ($query) {
                $q->where('business_name', 'like', "%{$query}%");
            })
            ->take(6)
            ->get();

        $categories = Category::active()->get();

        return view('search', compact('products', 'vendors', 'categories', 'query'));
    }
}
