@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Inventory List</h1>

    <a href="{{ route('inventory.create') }}" class="btn btn-success mb-3">Add New Item</a>

    @if(session('success'))
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            showNotifyToast(@json(session('success')));
        });
    </script>
    @endif

    <div id="inventory-table-wrapper">
        @if($items->count())
            <table class="table table-bordered table-striped" id="inventory-table">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Quantity</th>
                        <th>Price</th>
                        <th>Location</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="inventory-tbody">
                    @foreach($items as $item)
                        <tr data-id="{{ is_array($item) ? $item['id'] : $item->id }}">
                            <td>{{ is_array($item) ? $item['id'] : $item->id }}</td>
                            <td>{{ is_array($item) ? $item['name'] : $item->name }}</td>
                            <td>{{ is_array($item) ? $item['quantity'] : $item->quantity }}</td>
                            <td>${{ number_format(is_array($item) ? $item['price'] : $item->price, 2) }}</td>
                            <td>{{ is_array($item) ? $item['location'] : $item->location }}</td>
                            <td>
                                <a href="{{ route('inventory.edit', is_array($item) ? $item['id'] : $item->id) }}" class="btn btn-sm btn-warning">Edit</a>

                                <form class="delete-form" action="{{ route('inventory.destroy', is_array($item) ? $item['id'] : $item->id) }}" method="POST" style="display:inline-block">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            {{-- Cache the data in JavaScript --}}
            <script>
                // Save server data to localStorage as backup
                const inventoryData = @json($items);
                localStorage.setItem('cached_inventory', JSON.stringify(inventoryData));
            </script>

        @else

            <div id="cached-inventory"></div>

            <script>
                const cached = localStorage.getItem('cached_inventory');
                if (cached) {
                    const items = JSON.parse(cached);
                    let html = `
                        <table class="table table-bordered table-striped">
                            <thead class="table-dark">
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Quantity</th>
                                    <th>Price</th>
                                    <th>Location</th>
                                </tr>
                            </thead>
                            <tbody>
                    `;

                    items.forEach(item => {
                        html += `
                            <tr data-id="${item.id}">
                                <td>${item.id}</td>
                                <td>${item.name}</td>
                                <td>${item.quantity}</td>
                                <td>$${parseFloat(item.price).toFixed(2)}</td>
                                <td>${item.location}</td>
                            </tr>
                        `;
                    });

                    html += '</tbody></table>';
                    document.getElementById('cached-inventory').innerHTML = html;
                } else {
                    document.getElementById('cached-inventory').innerHTML = '<div class="alert alert-warning">No cached data available.</div>';
                }
            </script>
        @endif
    </div>
</div>

{{-- CSRF token meta for JS --}}
<meta name="csrf-token" content="{{ csrf_token() }}">

<script>
document.addEventListener('DOMContentLoaded', () => {
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    // Offline queue from localStorage
    let offlineQueue = JSON.parse(localStorage.getItem('offlineQueue') || '[]');
    function saveQueue() { localStorage.setItem('offlineQueue', JSON.stringify(offlineQueue)); }

    // Function to send delete request
    async function sendDelete(id) {
        try {
            const res = await fetch(`/inventory/${id}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                }
            });
            return res.ok;
        } catch (err) {
            return false;
        }
    }

    // Attach delete event listeners to all forms
    function attachDeleteListeners() {
        document.querySelectorAll('.delete-form').forEach(form => {
            // Remove any existing event listeners by cloning node (safe practice)
            const newForm = form.cloneNode(true);
            form.parentNode.replaceChild(newForm, form);

            newForm.addEventListener('submit', async function(e) {
                e.preventDefault();

                const confirmed = await showConfirmToast('Delete this item?');
                if (!confirmed) return;

                const action = newForm.getAttribute('action');
                const id = action.split('/').pop();

                const success = await sendDelete(id);
                if (!success) {
                    offlineQueue.push({ type: 'delete', id });
                    saveQueue();
                    alert('You are offline or server unavailable. Delete will sync later.');

                    const row = newForm.closest('tr');
                    if (row) row.remove();
                } else {
                    const row = newForm.closest('tr');
                    if (row) row.remove();
                }
            });
        });
    }

    // Run once on page load
    attachDeleteListeners();

    // Function to process offlineQueue
    async function processQueue() {
        if (offlineQueue.length === 0) return;

        const remaining = [];
        for (const action of offlineQueue) {
            if (action.type === 'delete') {
                const success = await sendDelete(action.id);
                if (!success) {
                    remaining.push(action);
                }
            }
            // other action types here if any
        }
        offlineQueue = remaining;
        saveQueue();
    }

    // Auto-sync every 10s
    setInterval(processQueue, 10000);
    processQueue();
});
</script>
@endsection
