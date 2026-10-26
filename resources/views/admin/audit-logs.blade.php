@extends('layouts.admin')

@section('title', 'Platform Audit & Security Logs')

@section('content')
<div class="mb-4 d-flex align-items-center justify-content-between flex-wrap gap-2">
    <div>
        <h3 class="fw-bold mb-1">Audit & Security Logs</h3>
        <p class="text-muted text-sm mb-0">Immutable records of administrative decisions, role modifications, store status changes, and critical operations.</p>
    </div>
</div>

<div class="admin-card p-4">
    @if($logs->isEmpty())
        <div class="text-center py-5 text-muted">
            <i class="bi bi-journal-text fs-1 d-block mb-2 text-secondary"></i>
            <h5>No audit logs found</h5>
            <p class="text-xs mb-0">System events and status updates will be logged here automatically.</p>
        </div>
    @else
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>Timestamp</th>
                        <th>User / Operator</th>
                        <th>Action</th>
                        <th>Entity Target</th>
                        <th>Old Values</th>
                        <th>New Values</th>
                        <th>Client IP</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($logs as $log)
                        <tr>
                            <td class="text-xs text-muted">
                                {{ $log->created_at->format('d M Y, h:i:s A') }}
                            </td>
                            <td>
                                <div class="fw-semibold">{{ $log->user->name ?? 'System' }}</div>
                                <small class="text-muted text-xs">{{ $log->user->email ?? 'Automated' }}</small>
                            </td>
                            <td>
                                <span class="badge bg-primary-subtle text-primary">
                                    {{ $log->action }}
                                </span>
                            </td>
                            <td>
                                <span class="text-xs font-monospace">
                                    {{ class_basename($log->auditable_type) }} #{{ $log->auditable_id }}
                                </span>
                            </td>
                            <td>
                                <code class="text-xs text-secondary">{{ json_encode($log->old_values) }}</code>
                            </td>
                            <td>
                                <code class="text-xs text-success">{{ json_encode($log->new_values) }}</code>
                            </td>
                            <td class="text-xs text-muted">
                                {{ $log->ip_address ?? '127.0.0.1' }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $logs->links() }}
        </div>
    @endif
</div>
@endsection
