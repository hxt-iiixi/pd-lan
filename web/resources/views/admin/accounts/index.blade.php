@extends('layouts.app')
@section('title', 'Accounts')
@if(auth()->check() && auth()->user()->is_admin)
@section('content')

<style>
    .section-title {
    font-size: 18px;
    font-weight: 600;
    color: #8b5e3c;
    margin-top: 30px;
    padding-left: 10px;
    border-left: 4px solid #8b5e3c;
}
table {
    width: 100%;
    border-collapse: collapse;
    font-size: 14px;
}

th, td {
    border: 1px solid #ddd;
    padding: 10px 12px;
    text-align: left;
}

thead {
    background-color: #f9f9f9;
    font-weight: 600;
}
.status-online {
    color: green;
}

.status-offline {
    color: gray;
}

.page-section {
    background: #ffffff;
    padding: 25px;
    border-radius: 16px;
    box-shadow: 0 3px 10px rgba(0, 0, 0, 0.04);
    margin-bottom: 30px;
}

.page-section h4 {
    font-size: 18px;
    font-weight: 600;
    margin-bottom: 15px;
    padding-left: 10px;
    border-left: 4px solid #8b5e3c;
    color: #8b5e3c;
}

/* Styled Buttons */
.btn-sm {
    padding: 6px 14px;
    font-size: 13px;
    font-weight: 600;
    border-radius: 8px;
    transition: all 0.3s ease;
    font-family: 'Lexend', sans-serif;
}

/* Approve Button */
.btn-success {
    background-color: #10b981;
    border: none;
    color: white;
}
.btn-success:hover {
    background-color: #059669;
    transform: scale(1.03);
}

/* Reject / Remove Button */
.btn-danger {
    background-color: #ef4444;
    border: none;
    color: white;
}
.btn-danger:hover {
    background-color: #b91c1c;
    transform: scale(1.03);
}

.action-buttons {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 8px;
    flex-wrap: wrap; /* Optional: handles small screen wrap */
}

.action-buttons form {
    display: inline-block; /* ensures buttons stay side-by-side */
    margin: 0;
}

</style>


<div class="page-section">
    <h4>Registered Accounts</h4>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @elseif (session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <table class="table">
        <thead>
            <tr>
                <th>Name</th>
                <th>Email</th>
                <th>Admin</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($users as $user)
            <tr>
                <td>{{ $user->name }}</td>
                <td>{{ $user->email }}</td>
                <td>{{ $user->is_admin ? 'Yes' : 'No' }}</td>
                <td>
                    @if ($user->is_active)
                        <span style="color: green;">● Online</span>
                    @else
                        <span style="color: gray;">● Offline</span>
                    @endif
                </td>


                <td>
                    @if (!$user->is_admin)
                    <form method="POST" action="{{ route('admin.accounts.delete', $user->id) }}">
                        @csrf
                        @method('DELETE')
                        <button class="btn btn-sm btn-danger" onclick="return confirm('Delete this user?')">Remove</button>
                    </form>
                    @else
                        <span class="text-muted">Protected</span>
                    @endif
                </td>
           
            @endforeach
            </tr>
        </tbody>
    </table>
    <h3 class="section-title">Pending Approvals</h3>
    <table class="table table-bordered table-hover align-middle mt-3">
        <thead class="table-light">
            <tr>
                <th>Name</th>
                <th>Email</th>
                <th class="text-center">Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($pendingUsers as $pending)
            <tr>
                <td>{{ $pending->name }}</td>
                <td>{{ $pending->email }}</td>
               <td class="text-center">
                    <div class="action-buttons">
                        <form method="POST" action="{{ route('admin.accounts.approve', $pending->id) }}">
                            @csrf
                            <button type="submit" class="btn btn-success btn-sm">Approve</button>
                        </form>
                        <form method="POST" action="{{ route('admin.accounts.reject', $pending->id) }}">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm">Reject</button>
                        </form>
                    </div>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

</div>
@endsection
@endif