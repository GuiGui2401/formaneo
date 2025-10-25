@extends('admin.layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Envoyer une notification à tous les utilisateurs</h4>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success" role="alert">
                            {{ session('success') }}
                        </div>
                    @endif

                    <form action="{{ route('admin.notifications.send') }}" method="POST">
                        @csrf
                        <div class="form-group">
                            <label for="title">Titre</label>
                            <input type="text" class="form-control" id="title" name="title" required>
                        </div>
                        <div class="form-group">
                            <label for="body">Message</label>
                            <textarea class="form-control" id="body" name="body" rows="5" required></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary">Envoyer la notification</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
