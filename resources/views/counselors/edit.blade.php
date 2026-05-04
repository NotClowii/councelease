@extends('layouts.app')
@section('title', 'Edit Counselor')
@section('page-title', 'Edit Counselor')
@section('content')
<div class="page-header">
    <div><h1>Edit: {{ $counselor->user->full_name }}</h1></div>
    <a href="{{ route('counselors.show', $counselor) }}" class="btn btn-outline"><i class="ph-bold ph-arrow-left"></i> Back</a>
</div>
<form method="POST" action="{{ route('counselors.update', $counselor) }}">
    @csrf @method('PUT')
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;">
        <div class="card">
            <div class="card-header"><span class="card-title">Personal Information</span></div>
            <div class="card-body">
                <div class="grid-2">
                    <div class="form-group">
                        <label>First Name *</label>
                        <input type="text" name="first_name" class="form-control" value="{{ old('first_name', $counselor->user->first_name) }}" required>
                    </div>
                    <div class="form-group">
                        <label>Last Name *</label>
                        <input type="text" name="last_name" class="form-control" value="{{ old('last_name', $counselor->user->last_name) }}" required>
                    </div>
                </div>
                <div class="form-group">
                    <label>Phone</label>
                    <input type="text" name="phone" class="form-control" value="{{ old('phone', $counselor->user->phone) }}">
                </div>
            </div>
        </div>
        <div class="card">
            <div class="card-header"><span class="card-title">Counselor Information</span></div>
            <div class="card-body">
                <div class="form-group">
                    <label>Department</label>
                    <select name="department_id" class="form-control">
                        <option value="">— Select —</option>
                        @foreach($departments as $dept)
                        <option value="{{ $dept->department_id }}" {{ old('department_id', $counselor->department_id) == $dept->department_id ? 'selected' : '' }}>{{ $dept->department_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label>Specialization</label>
                    <input type="text" name="specialization" class="form-control" value="{{ old('specialization', $counselor->specialization) }}">
                </div>
                <div class="form-group">
                    <label>License Number</label>
                    <input type="text" name="license_number" class="form-control" value="{{ old('license_number', $counselor->license_number) }}">
                </div>
                <div class="form-group">
                    <label>Max Appointments Per Day</label>
                    <input type="number" name="max_appointments_per_day" class="form-control" value="{{ old('max_appointments_per_day', $counselor->max_appointments_per_day) }}" min="1" max="20">
                </div>
                <div class="grid-2">
                    <div class="form-group">
                        <label>Available From</label>
                        <input type="time" name="available_from" class="form-control" value="{{ old('available_from', substr($counselor->available_from, 0, 5)) }}">
                    </div>
                    <div class="form-group">
                        <label>Available Until</label>
                        <input type="time" name="available_until" class="form-control" value="{{ old('available_until', substr($counselor->available_until, 0, 5)) }}">
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="flex gap-2" style="margin-top:20px;">
        <button type="submit" class="btn btn-primary"><i class="ph-bold ph-floppy-disk"></i> Save Changes</button>
        <a href="{{ route('counselors.show', $counselor) }}" class="btn btn-outline">Cancel</a>
    </div>
</form>
@endsection