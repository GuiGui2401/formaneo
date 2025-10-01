@extends('admin.layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Edit Product: {{ $product->name }}</h1>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Product Details</h6>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.products.update', $product->id) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="form-group">
                    <label for="name">Product Name</label>
                    <input type="text" class="form-control" id="name" name="name" value="{{ old('name', $product->name) }}" required>
                    @error('name')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>
                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea class="form-control" id="description" name="description" rows="5">{{ old('description', $product->description) }}</textarea>
                    @error('description')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>
                <div class="form-group">
                    <label for="image_url">Image URL</label>
                    <input type="url" class="form-control" id="image_url" name="image_url" value="{{ old('image_url', $product->image_url) }}">
                    @error('image_url')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>
                <div class="form-group">
                    <label for="price">Price (FCFA)</label>
                    <input type="number" step="0.01" class="form-control" id="price" name="price" value="{{ old('price', $product->price) }}" required>
                    @error('price')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>
                <div class="form-group">
                    <label for="promotion_price">Promotion Price (FCFA)</label>
                    <input type="number" step="0.01" class="form-control" id="promotion_price" name="promotion_price" value="{{ old('promotion_price', $product->promotion_price) }}">
                    @error('promotion_price')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>
                <div class="form-group">
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" id="is_on_promotion" name="is_on_promotion" {{ old('is_on_promotion', $product->is_on_promotion) ? 'checked' : '' }}>
                        <label class="form-check-label" for="is_on_promotion">On Promotion</label>
                    </div>
                </div>
                <div class="form-group">
                    <label for="category">Category</label>
                    <select class="form-control" id="category" name="category" required>
                        <option value="">Select Category</option>
                        <option value="formation_pack" {{ old('category', $product->category) == 'formation_pack' ? 'selected' : '' }}>Formation Pack</option>
                        <option value="ebook" {{ old('category', $product->category) == 'ebook' ? 'selected' : '' }}>Ebook</option>
                        <option value="tool" {{ old('category', $product->category) == 'tool' ? 'selected' : '' }}>Tool</option>
                        <option value="template" {{ old('category', $product->category) == 'template' ? 'selected' : '' }}>Template</option>
                    </select>
                    @error('category')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>
                <div class="form-group">
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" id="is_active" name="is_active" {{ old('is_active', $product->is_active) ? 'checked' : '' }}>
                        <label class="form-check-label" for="is_active">Is Active</label>
                    </div>
                </div>
                <div class="form-group">
                    <label for="metadata">Metadata (JSON)</label>
                    <textarea class="form-control" id="metadata" name="metadata" rows="3">{{ old('metadata', json_encode($product->metadata)) }}</textarea>
                    @error('metadata')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>
                <button type="submit" class="btn btn-primary">Update Product</button>
            </form>
        </div>
    </div>
</div>
@endsection
