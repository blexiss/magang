@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Edit Inventory Item</h1>

    <form action="{{ route('inventory.update', $item->id) }}" method="POST">
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
@endsection
