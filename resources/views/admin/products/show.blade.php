@extends('admin.layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Product Details: {{ $product->name }}</h1>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Product Information</h6>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <p><strong>ID:</strong> {{ $product->id }}</p>
                    <p><strong>Name:</strong> {{ $product->name }}</p>
                    <p><strong>Slug:</strong> {{ $product->slug }}</p>
                    <p><strong>Description:</strong> {{ $product->description }}</p>
                    <p><strong>Image URL:</strong> {{ $product->image_url }}</p>
                    <p><strong>Price:</strong> {{ number_format($product->price, 2) }} FCFA</p>
                    <p><strong>Promotion Price:</strong> {{ $product->promotion_price ? number_format($product->promotion_price, 2) . ' FCFA' : 'N/A' }}</p>
                    <p><strong>On Promotion:</strong> {{ $product->is_on_promotion ? 'Yes' : 'No' }}</p>
                    <p><strong>Category:</strong> {{ $product->category }}</p>
                    <p><strong>Is Active:</strong> {{ $product->is_active ? 'Yes' : 'No' }}</p>
                    <p><strong>Created At:</strong> {{ $product->created_at }}</p>
                    <p><strong>Updated At:</strong> {{ $product->updated_at }}</p>
                </div>
                <div class="col-md-6">
                    @if ($product->image_url)
                        <img src="{{ $product->image_url }}" alt="{{ $product->name }}" class="img-fluid">
                    @else
                        <p>No image available</p>
                    @endif
                    <h5 class="mt-3">Metadata:</h5>
                    <pre>{{ json_encode($product->metadata, JSON_PRETTY_PRINT) }}</pre>
                </div>
            </div>
            <a href="{{ route('admin.products.edit', $product->id) }}" class="btn btn-primary">Edit Product</a>
            <a href="{{ route('admin.products.index') }}" class="btn btn-secondary">Back to List</a>
        </div>
    </div>
</div>
@endsection
