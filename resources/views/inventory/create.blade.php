@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Add New Inventory Item</h1>

    <form id="inventory-form" action="{{ route('inventory.store') }}" method="POST">
        @csrf

        <div class="mb-3">
            <label for="name" class="form-label">Item Name</label>
            <input type="text" class="form-control" id="name" name="name" value="{{ old('name') }}" required>
            @error('name') <div class="text-danger">{{ $message }}</div> @enderror
        </div>

        <div class="mb-3">
            <label for="quantity" class="form-label">Quantity</label>
            <input type="number" min="0" class="form-control" id="quantity" name="quantity" value="{{ old('quantity') }}" required>
            @error('quantity') <div class="text-danger">{{ $message }}</div> @enderror
        </div>

        <div class="mb-3">
            <label for="price" class="form-label">Price (USD)</label>
            <input type="number" step="0.01" min="0" class="form-control" id="price" name="price" value="{{ old('price') }}" required>
            @error('price') <div class="text-danger">{{ $message }}</div> @enderror
        </div>

        <div class="mb-3">
            <label for="location" class="form-label">Location</label>
            <input type="text" class="form-control" id="location" name="location" value="{{ old('location') }}" required>
            @error('location') <div class="text-danger">{{ $message }}</div> @enderror
        </div>

        <button type="submit" class="btn btn-success">Add Item</button>
        <a href="{{ route('inventory.index') }}" class="btn btn-secondary">Cancel</a>
    </form>
</div>

<script>
    document.getElementById('inventory-form').addEventListener('submit', async function(e) {
        e.preventDefault();

        const form = e.target;
        const formData = new FormData(form);
        const data = {};
        formData.forEach((value, key) => data[key] = value);

        try {
            const response = await fetch(form.action, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify(data)
            });

            if (!response.ok) throw new Error("Server error");

            alert('Item successfully added!');
            window.location.href = "{{ route('inventory.index') }}";

        } catch (err) {
            const queue = JSON.parse(localStorage.getItem('pending_inventory') || '[]');
            queue.push({ action: 'create', data });
            localStorage.setItem('pending_inventory', JSON.stringify(queue));
            alert('⚠️ Offline. Item saved locally and will sync when online.');
            window.location.href = "{{ route('inventory.index') }}";
        }
    });
</script>
@endsection
