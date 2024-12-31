{{-- resources/views/conversations/create.blade.php --}}
@extends('layouts.app')

@section('content')
    <h1>Create New Conversation</h1>

    <form action="{{ route('conversations.store') }}" method="POST">
        @csrf
        <div class="form-group">
            <label for="title">Conversation Title</label>
            <input type="text" name="title" id="title" class="form-control" required>
        </div>
        <div class="form-group">
            <label for="user_id">User</label>
            <select name="user_id" id="user_id" class="form-control">
                @foreach ($users as $user)
                    <option value="{{ $user->id }}">{{ $user->name }}</option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="btn btn-primary mt-3">Create Conversation</button>
    </form>
@endsection
