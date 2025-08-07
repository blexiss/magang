@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Edit Inventory Item</h1>

    <form id="edit-inventory-form" action="{{ route('inventory.update', $item->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="mb-3">
            <label for="name" class="form-label">Item Name</label>
            <input type="text" class="form-control" id="name" name="name" value="{{ old('name', $item->name) }}" required>
            @error('name') <div class="text-danger">{{ $message }}</div> @enderror
        </div>

        <div class="mb-3">
            <label for="quantity" class="form-label">Quantity</label>
            <input type="number" min="0" class="form-control" id="quantity" name="quantity" value="{{ old('quantity', $item->quantity) }}" required>
            @error('quantity') <div class="text-danger">{{ $message }}</div> @enderror
        </div>

        <div class="mb-3">
            <label for="price" class="form-label">Price (USD)</label>
            <input type="number" step="0.01" min="0" class="form-control" id="price" name="price" value="{{ old('price', $item->price) }}" required>
            @error('price') <div class="text-danger">{{ $message }}</div> @enderror
        </div>

        <div class="mb-3">
            <label for="location" class="form-label">Location</label>
            <input type="text" class="form-control" id="location" name="location" value="{{ old('location', $item->location) }}" required>
            @error('location') <div class="text-danger">{{ $message }}</div> @enderror
        </div>

        <button type="submit" class="btn btn-primary">Update Item</button>
        <a href="{{ route('inventory.index') }}" class="btn btn-secondary">Cancel</a>
    </form>
</div>

<meta name="csrf-token" content="{{ csrf_token() }}">

<script>
document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('edit-inventory-form');
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    // Load offline updates queue or create empty array
    let offlineQueue = JSON.parse(localStorage.getItem('offlineQueue') || '[]');

    function saveQueue() {
        localStorage.setItem('offlineQueue', JSON.stringify(offlineQueue));
    }

    async function sendUpdate(id, data) {
        try {
            const response = await fetch(`/inventory/${id}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                },
                body: JSON.stringify(data),
            });

            if (!response.ok) throw new Error('Network response not ok');

            const respData = await response.json();
            if (respData.success === false) throw new Error(respData.message || 'Update failed');

            console.log(`Updated item ${id} on server.`);
            return true;
        } catch (err) {
            console.warn(`Failed to update item ${id} on server:`, err);
            return false;
        }
    }

    form.addEventListener('submit', async (e) => {
        e.preventDefault();

        // Gather form data
        const formData = {
            name: form.name.value,
            quantity: parseInt(form.quantity.value),
            price: parseFloat(form.price.value),
            location: form.location.value,
        };

        const itemId = {{ $item->id }};

        // Try sending update immediately
        const success = await sendUpdate(itemId, formData);

        if (!success) {
            // Queue update for later sync
            offlineQueue.push({ type: 'update', id: itemId, data: formData });
            saveQueue();

            alert('You appear offline or server unavailable. Changes will sync later.');

            // Redirect back to index immediately or update UI directly
            window.location.href = '{{ route("inventory.index") }}';
        } else {
            // Success - redirect to index
            window.location.href = '{{ route("inventory.index") }}?success=Item updated successfully';
        }
    });

    // Optionally, on this edit page you can also add sync attempt for queue (similar to index.js)
});
</script>
@endsection
