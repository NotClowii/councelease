@extends('layouts.app')
@section('title', 'Settings')
@section('page-title', 'Settings')
@section('content')
<div class="page-header">
    <div><h1>System Settings</h1><p>Configure system-wide settings and preferences</p></div>
</div>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;">
    <div class="card">
        <div class="card-header"><span class="card-title">General Settings</span></div>
        <div class="card-body">
            <div class="form-group">
                <label>System Name</label>
                <input type="text" class="form-control" value="CounselEase" readonly>
                <div class="form-hint">Change via APP_NAME in environment variables</div>
            </div>
            <div class="form-group">
                <label>Environment</label>
                <input type="text" class="form-control" value="{{ config('app.env') }}" readonly>
            </div>
            <div class="form-group">
                <label>Application URL</label>
                <input type="text" class="form-control" value="{{ config('app.url') }}" readonly>
            </div>
        </div>
    </div>
    <div class="card">
        <div class="card-header"><span class="card-title">Database Info</span></div>
        <div class="card-body">
            <div class="form-group">
                <label>Connection</label>
                <input type="text" class="form-control" value="{{ config('database.default') }}" readonly>
            </div>
            <div class="form-group">
                <label>Database</label>
                <input type="text" class="form-control" value="{{ config('database.connections.' . config('database.default') . '.database') }}" readonly>
            </div>
        </div>
    </div>
    <div class="card" style="grid-column:span 2;">
        <div class="card-header"><span class="card-title">Roles & Permissions Overview</span></div>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Module</th><th>Student</th><th>Counselor</th><th>Office Staff</th><th>School Admin</th><th>System Admin</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach([
                        ['Authentication','✅','✅','✅','✅','✅'],
                        ['Student Profile','View Own','View','Full Access','View','Full Access'],
                        ['Appointments','Book/View Own','Approve/Manage','Full Access','View','Full Access'],
                        ['Sessions','View Own','Manage','View','View','Full Access'],
                        ['Case Notes','❌','Own Only','❌','❌','Full Access'],
                        ['Reports','❌','Limited','✅','✅','Full Access'],
                        ['System Settings','❌','❌','❌','❌','✅'],
                    ] as $row)
                    <tr>
                        <td class="font-semibold">{{ $row[0] }}</td>
                        @foreach(array_slice($row,1) as $cell)
                        <td class="text-sm">{{ $cell }}</td>
                        @endforeach
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection