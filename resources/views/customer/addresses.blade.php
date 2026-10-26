@extends('layouts.app')

@section('title', 'Saved Delivery Addresses - VaishnoMart')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold mb-1">Saved Delivery Addresses</h3>
            <p class="text-muted small">Manage your home, office, and other delivery destinations</p>
        </div>
        <button class="btn btn-vm-primary btn-sm rounded-pill px-3" data-bs-toggle="collapse" data-bs-target="#addAddressForm">
            <i class="bi bi-plus-lg me-1"></i> Add New Address
        </button>
    </div>

    <!-- Add Address Form (Collapsible) -->
    <div class="collapse mb-4 {{ $addresses->isEmpty() ? 'show' : '' }}" id="addAddressForm">
        <div class="card border-0 rounded-4 shadow-sm bg-white p-4">
            <h5 class="fw-bold mb-3">Add New Delivery Location</h5>
            <form action="{{ route('customer.addresses.save') }}" method="POST">
                @csrf
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label small fw-bold">Address Label</label>
                        <select name="label" class="form-select">
                            <option value="Home">Home</option>
                            <option value="Work">Work / Office</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-bold">Recipient Name</label>
                        <input type="text" name="recipient_name" class="form-control" value="{{ auth()->user()->name }}" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-bold">Contact Phone</label>
                        <input type="text" name="recipient_phone" class="form-control" value="{{ auth()->user()->phone }}" required>
                    </div>
                    <div class="col-12">
                        <label class="form-label small fw-bold">Address Line 1 (House/Flat/Building/Street)</label>
                        <input type="text" name="address_line1" class="form-control" placeholder="e.g. Flat 502, Tower 1, Sunshine Heights" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">Address Line 2 / Landmark (Optional)</label>
                        <input type="text" name="address_line2" class="form-control" placeholder="e.g. Near Community Center">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-bold">City</label>
                        <input type="text" name="city" class="form-control" value="New Delhi" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-bold">PIN Code</label>
                        <input type="text" name="postal_code" class="form-control" value="110001" required>
                    </div>
                    <input type="hidden" name="state" value="Delhi">
                    <div class="col-12">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="is_default" value="1" id="checkDefaultAddr">
                            <label class="form-check-label small" for="checkDefaultAddr">
                                Set as default delivery address
                            </label>
                        </div>
                    </div>
                    <div class="col-12">
                        <button type="submit" class="btn btn-vm-primary rounded-pill px-4">Save Address</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Address Cards -->
    <div class="row row-cols-1 row-cols-md-3 g-3">
        @foreach($addresses as $addr)
            <div class="col">
                <div class="card border-0 rounded-4 shadow-sm bg-white p-4 h-100 position-relative">
                    @if($addr->is_default)
                        <span class="position-absolute top-0 end-0 m-3 badge bg-success">Default</span>
                    @endif
                    <div class="fw-bold fs-5 mb-1"><i class="bi bi-geo-alt-fill text-success me-1"></i>{{ $addr->label }}</div>
                    <div class="small fw-semibold text-dark">{{ $addr->recipient_name }}</div>
                    <div class="small text-muted mb-3">{{ $addr->recipient_phone }}</div>
                    <p class="small text-secondary mb-3 flex-grow-1">{{ $addr->formatted_address }}</p>
                    <div class="border-top pt-2 text-end">
                        <form action="{{ route('customer.addresses.delete', $addr->id) }}" method="POST" onsubmit="return confirm('Delete this saved address?');">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-link text-danger p-0">Delete</button>
                        </form>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>
@endsection
