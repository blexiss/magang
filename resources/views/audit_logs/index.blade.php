@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Audit Log</h1>

    @if(isset($offline))
        <div class="alert alert-warning">
            Showing cached audit logs — database connection is offline.
        </div>
    @endif

    @if($logs->isEmpty())
        <div class="alert alert-info">No audit logs found.</div>
    @else
        @php
            // Removed 'use' statement - not allowed in Blade

            function actionIcon($action) {
                $actionLower = strtolower($action);
                if (str_contains($actionLower, 'deleted')) {
                    return '<i class="bi bi-trash-fill text-danger" title="Deleted"></i> ';
                } elseif (str_contains($actionLower, 'created')) {
                    return '<i class="bi bi-plus-circle-fill text-success" title="Created"></i> ';
                } elseif (str_contains($actionLower, 'changed') || str_contains($actionLower, 'updated')) {
                    return '<i class="bi bi-pencil-fill text-warning" title="Updated"></i> ';
                }
                return '<i class="bi bi-info-circle-fill text-secondary" title="Info"></i> ';
            }
        @endphp

        <table class="table table-striped table-bordered">
            <thead class="table-dark">
                <tr>
                    <th>Time</th>
                    <th>Item Name / ID</th>
                    <th>Action</th>
                    <th>Performed By</th>
                </tr>
            </thead>
            <tbody>
                @foreach($logs as $log)
                    @php
                        $created = is_object($log->created_at)
                            ? $log->created_at
                            : \Carbon\Carbon::parse($log->created_at);
                    @endphp
                    <tr>
                        <td title="{{ $created->format('M d, Y h:i:s A') }}">
                            {{ $created->format('M d, Y h:i:s A') }} <br>
                            <small class="text-muted">{{ $created->diffForHumans() }}</small>
                        </td>
                        <td>
                            @if(property_exists($log, 'inventory') && $log->inventory)
                                {{ $log->inventory->name ?? 'Unnamed' }} (ID: {{ $log->inventory->id ?? '?' }})
                            @else
                                <em>Deleted Item (ID: {{ $log->inventory_id ?? 'N/A' }})</em>
                            @endif
                        </td>
                        <td>{!! actionIcon($log->action) !!} {{ ucfirst($log->action) }}</td>
                        <td>{{ $log->user ?? 'Unknown' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <a href="{{ route('inventory.index') }}" class="btn btn-secondary mt-3">Back to Inventory</a>
</div>
@endsection
