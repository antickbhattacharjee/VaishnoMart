@extends('layouts.admin')

@section('title', 'Manage Product Categories')

@section('content')
<div class="mb-4 d-flex align-items-center justify-content-between flex-wrap gap-2">
    <div>
        <h3 class="fw-bold mb-1">Product Categories & Segments</h3>
        <p class="text-muted text-sm mb-0">Dynamic multi-category catalog configuration for drinking water, cloud kitchens, grocery, dry fruits, etc.</p>
    </div>
    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addCategoryModal">
        <i class="bi bi-plus-lg me-1"></i> Add Category
    </button>
</div>

<div class="row g-4">
    <div class="col-12">
        <div class="admin-card p-4">
            <h5 class="fw-bold mb-3">All Active Business Categories</h5>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Order</th>
                            <th>Icon</th>
                            <th>Category Name</th>
                            <th>Slug</th>
                            <th>Description</th>
                            <th>Live Products</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($categories as $category)
                            <tr>
                                <td class="fw-bold text-muted">{{ $category->display_order }}</td>
                                <td>
                                    <div class="rounded-3 bg-light border p-2 d-inline-flex align-items-center justify-content-center text-primary fs-5" style="width: 40px; height: 40px;">
                                        <i class="bi {{ $category->icon ?? 'bi-tag-fill' }}"></i>
                                    </div>
                                </td>
                                <td class="fw-bold">
                                    <a href="{{ route('category.show', $category->slug) }}" target="_blank" class="text-decoration-none">
                                        {{ $category->name }}
                                    </a>
                                </td>
                                <td><code>{{ $category->slug }}</code></td>
                                <td class="text-muted text-xs">{{ Str::limit($category->description, 50) }}</td>
                                <td>
                                    <span class="badge bg-primary-subtle text-primary">
                                        {{ $category->products_count }} Products
                                    </span>
                                </td>
                                <td>
                                    @if($category->is_active)
                                        <span class="badge bg-success">Active</span>
                                    @else
                                        <span class="badge bg-secondary">Disabled</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-4 text-muted">No categories created yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Add Category Modal -->
<div class="modal fade" id="addCategoryModal" tabindex="-1" aria-labelledby="addCategoryModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('admin.categories.store') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="addCategoryModalLabel">Add New Business Category</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-xs">Category Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" required placeholder="e.g. Organic Dairy & Milk">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-xs">Bootstrap Icon Class</label>
                        <input type="text" name="icon" class="form-control" placeholder="e.g. bi-cup-hot, bi-droplet-half, bi-bag-heart">
                        <small class="text-muted text-xs">See <a href="https://icons.getbootstrap.com" target="_blank">Bootstrap Icons</a></small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-xs">Display Order</label>
                        <input type="number" name="display_order" class="form-control" value="0">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-xs">Description</label>
                        <textarea name="description" class="form-control" rows="3" placeholder="Category description for customer storefront..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-sm">Save Category</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
