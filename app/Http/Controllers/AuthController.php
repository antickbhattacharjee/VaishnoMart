<?php

namespace App\Http\Controllers;

use App\Models\Address;
use App\Models\Category;
use App\Models\CustomerProfile;
use App\Models\DeliveryAgentProfile;
use App\Models\User;
use App\Models\VendorProfile;
use App\Models\VendorServiceArea;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::check()) {
            return $this->redirectBasedOnRole(Auth::user());
        }
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'login' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $loginField = filter_var($credentials['login'], FILTER_VALIDATE_EMAIL) ? 'email' : 'phone';

        $user = User::where($loginField, $credentials['login'])->first();

        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            return back()->withErrors([
                'login' => 'The provided credentials do not match our records.',
            ])->onlyInput('login');
        }

        if ($user->status === 'suspended') {
            return back()->withErrors([
                'login' => 'Your account has been suspended. Please contact platform administration.',
            ]);
        }

        Auth::login($user, $request->boolean('remember'));
        $request->session()->regenerate();

        return $this->redirectBasedOnRole($user);
    }

    public function showRegister()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['required', 'string', 'regex:/^[0-9]{10}$/', 'unique:users,phone'],
            'password' => ['required', 'confirmed', Password::min(8)],
            'address_line1' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:100'],
            'postal_code' => ['nullable', 'string', 'max:10'],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'role' => User::ROLE_CUSTOMER,
            'status' => 'active',
            'password' => Hash::make($validated['password']),
            'email_verified_at' => now(),
        ]);

        $address = null;
        if (!empty($validated['address_line1'])) {
            $address = Address::create([
                'user_id' => $user->id,
                'label' => 'Home',
                'recipient_name' => $user->name,
                'recipient_phone' => $user->phone,
                'address_line1' => $validated['address_line1'],
                'city' => $validated['city'] ?? 'Central City',
                'state' => 'State',
                'postal_code' => $validated['postal_code'] ?? '110001',
                'latitude' => 28.6315,
                'longitude' => 77.2200,
                'is_default' => true,
            ]);
        }

        CustomerProfile::create([
            'user_id' => $user->id,
            'default_address_id' => $address?->id,
            'loyalty_points' => 50,
        ]);

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('home')->with('success', 'Welcome to VaishnoMart! Your account has been created.');
    }

    public function showVendorRegister()
    {
        $categories = Category::active()->get();
        return view('auth.vendor-register', compact('categories'));
    }

    public function registerVendor(Request $request)
    {
        $validated = $request->validate([
            'owner_name' => ['required', 'string', 'max:255'],
            'business_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['required', 'string', 'regex:/^[0-9]{10}$/', 'unique:users,phone'],
            'primary_category_id' => ['required', 'exists:categories,id'],
            'address' => ['required', 'string'],
            'service_radius_km' => ['required', 'numeric', 'min:1', 'max:50'],
            'latitude' => ['nullable', 'numeric'],
            'longitude' => ['nullable', 'numeric'],
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);

        $user = User::create([
            'name' => $validated['owner_name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'role' => User::ROLE_VENDOR,
            'status' => 'pending_approval',
            'password' => Hash::make($validated['password']),
        ]);

        $vendor = VendorProfile::create([
            'user_id' => $user->id,
            'primary_category_id' => $validated['primary_category_id'],
            'business_name' => $validated['business_name'],
            'owner_name' => $validated['owner_name'],
            'phone' => $validated['phone'],
            'email' => $validated['email'],
            'address' => $validated['address'],
            'latitude' => $validated['latitude'] ?? 28.6310,
            'longitude' => $validated['longitude'] ?? 77.2180,
            'service_radius_km' => $validated['service_radius_km'],
            'status' => 'approved', // Auto-approved for smooth demonstration
            'is_open' => true,
            'is_accepting_orders' => true,
            'prep_time_minutes' => 15,
            'commission_rate' => 5.00,
        ]);

        VendorServiceArea::create([
            'vendor_profile_id' => $vendor->id,
            'area_name' => 'Store Service Area',
            'radius_km' => $validated['service_radius_km'],
            'is_active' => true,
        ]);

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('vendor.dashboard')->with('success', 'Your vendor store has been registered and activated!');
    }

    public function showAgentRegister()
    {
        return view('auth.agent-register');
    }

    public function registerAgent(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['required', 'string', 'regex:/^[0-9]{10}$/', 'unique:users,phone'],
            'vehicle_type' => ['required', 'string', 'in:bike,scooter,cycle,van'],
            'vehicle_number' => ['required', 'string', 'max:50'],
            'license_number' => ['required', 'string', 'max:50'],
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'role' => User::ROLE_DELIVERY_AGENT,
            'status' => 'active',
            'password' => Hash::make($validated['password']),
        ]);

        DeliveryAgentProfile::create([
            'user_id' => $user->id,
            'vehicle_type' => $validated['vehicle_type'],
            'vehicle_number' => $validated['vehicle_number'],
            'license_number' => $validated['license_number'],
            'is_online' => true,
            'is_available' => true,
            'status' => 'approved',
            'current_latitude' => 28.6310,
            'current_longitude' => 77.2180,
            'service_radius_km' => 15.00,
        ]);

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('agent.dashboard')->with('success', 'Welcome delivery partner! You are now online.');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('info', 'You have been logged out.');
    }

    protected function redirectBasedOnRole(User $user)
    {
        if ($user->isAdmin()) {
            return redirect()->route('admin.dashboard');
        }
        if ($user->isVendor()) {
            return redirect()->route('vendor.dashboard');
        }
        if ($user->isDeliveryAgent()) {
            return redirect()->route('agent.dashboard');
        }
        return redirect()->route('home');
    }
}
