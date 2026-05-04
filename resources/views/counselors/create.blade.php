@extends('layouts.app')
@section('title', 'Add Counselor')
@section('page-title', 'Add Counselor')
@section('content')
<div class="page-header">
    <div><h1>Register New Counselor</h1><p>Create a counselor account and profile</p></div>
    <a href="{{ route('counselors.index') }}" class="btn btn-outline"><i class="ph-bold ph-arrow-left"></i> Back</a>
</div>
<form method="POST" action="{{ route('counselors.store') }}">
    @csrf
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;">
        <div class="card">
            <div class="card-header"><span class="card-title">Account Information</span></div>
            <div class="card-body">
                <div class="grid-2">
                    <div class="form-group">
                        <label>First Name *</label>
                        <input type="text" name="first_name" class="form-control" value="{{ old('first_name') }}" required>
                        @error('first_name') <div class="field-error">{{ $message }}</div> @enderror
                    </div>
                    <div class="form-group">
                        <label>Last Name *</label>
                        <input type="text" name="last_name" class="form-control" value="{{ old('last_name') }}" required>
                        @error('last_name') <div class="field-error">{{ $message }}</div> @enderror
                    </div>
                </div>
                <div class="form-group">
                    <label>Email *</label>
                    <input type="email" name="email" class="form-control" value="{{ old('email') }}" required>
                    @error('email') <div class="field-error">{{ $message }}</div> @enderror
                </div>
                <div class="form-group">
                    <label>Username *</label>
                    <input type="text" name="username" class="form-control" value="{{ old('username') }}" required>
                    @error('username') <div class="field-error">{{ $message }}</div> @enderror
                </div>
                <div class="grid-2">
                    <div class="form-group">
                        <label>Password *</label>
                        <input type="password" name="password" class="form-control" required minlength="8">
                    </div>
                    <div class="form-group">
                        <label>Confirm Password *</label>
                        <input type="password" name="password_confirmation" class="form-control" required>
                    </div>
                </div>
                <div class="form-group">
                    <label>Phone</label>
                    <input type="text" name="phone" class="form-control" value="{{ old('phone') }}">
                </div>
            </div>
        </div>
        <div class="card">
            <div class="card-header"><span class="card-title">Counselor Information</span></div>
            <div class="card-body">
                <div class="form-group">
                    <label>Department</label>
                    <select name="department_id" class="form-control">
                        <option value="">— Select Department —</option>
                        @foreach($departments as $dept)
                        <option value="{{ $dept->department_id }}" {{ old('department_id') == $dept->department_id ? 'selected' : '' }}>{{ $dept->department_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label>Specialization</label>
                    <input type="text" name="specialization" class="form-control" value="{{ old('specialization') }}" placeholder="e.g., Academic and Career Counseling">
                </div>
                <div class="form-group">
                    <label>License Number</label>
                    <input type="text" name="license_number" class="form-control" value="{{ old('license_number') }}">
                </div>
                <div class="form-group">
                    <label>Max Appointments Per Day</label>
                    <input type="number" name="max_appointments_per_day" class="form-control" value="{{ old('max_appointments_per_day', 8) }}" min="1" max="20">
                </div>
                <div class="grid-2">
                    <div class="form-group">
                        <label>Available From</label>
                        <input type="time" name="available_from" class="form-control" value="{{ old('available_from', '08:00') }}">
                    </div>
                    <div class="form-group">
                        <label>Available Until</label>
                        <input type="time" name="available_until" class="form-control" value="{{ old('available_until', '17:00') }}">
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="flex gap-2" style="margin-top:20px;">
        <button type="submit" class="btn btn-primary"><i class="ph-bold ph-user-plus"></i> Register Counselor</button>
        <a href="{{ route('counselors.index') }}" class="btn btn-outline">Cancel</a>
    </div>
</form>
@endsection