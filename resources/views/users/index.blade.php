@extends('layouts.app')
@section('title', 'User Management')
@section('page-title', 'User Management')
@section('content')
<div class="page-header">
    <div><h1>User Management</h1><p>View and manage all system users</p></div>
</div>
<div class="card">
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>User</th>
                    <th>Username</th>
                    <th>Role</th>
                    <th>Email</th>
                    <th>Status</th>
                    <th>Joined</th>
                </tr>
            </thead>
            <tbody>
                @foreach($users as $user)
                <tr>
                    <td>
                        <div class="flex items-center gap-2">
                            <div class="avatar">{{ $user->initials }}</div>
                            <span class="font-semibold">{{ $user->full_name }}</span>
                        </div>
                    </td>
                    <td class="text-muted">{{ $user->username }}</td>
                    <td><span class="badge badge-secondary">{{ $user->role?->role_name }}</span></td>
                    <td class="text-sm">{{ $user->email }}</td>
                    <td>
                        <span class="badge badge-{{ $user->is_active ? 'success' : 'danger' }}">
                            {{ $user->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </td>
                    <td class="text-sm text-muted">{{ $user->created_at->format('M d, Y') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @if($users->hasPages())
    <div style="padding:16px 20px;border-top:1px solid var(--border-light);">
        {{ $users->links() }}
    </div>
    @endif
</div>
@endsection