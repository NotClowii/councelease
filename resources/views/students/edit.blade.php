@extends('layouts.app')

@section('title', 'Edit Student')
@section('page-title', 'Edit Student Profile')

@section('content')
<div class="page-header">
    <div>
        <h1>Edit: {{ $student->user->full_name }}</h1>
        <p>Update student profile information</p>
    </div>
    <a href="{{ route('students.show', $student) }}" class="btn btn-outline">
        <i class="ph-bold ph-arrow-left"></i> Back
    </a>
</div>

<form method="POST" action="{{ route('students.update', $student) }}">
    @csrf
    @method('PUT')

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;">
        <div class="card">
            <div class="card-header"><span class="card-title">Personal Information</span></div>
            <div class="card-body">
                <div class="grid-2">
                    <div class="form-group">
                        <label>First Name *</label>
                        <input type="text" name="first_name" class="form-control" value="{{ old('first_name', $student->user->first_name) }}" required>
                    </div>
                    <div class="form-group">
                        <label>Last Name *</label>
                        <input type="text" name="last_name" class="form-control" value="{{ old('last_name', $student->user->last_name) }}" required>
                    </div>
                </div>
                <div class="form-group">
                    <label>Phone</label>
                    <input type="text" name="phone" class="form-control" value="{{ old('phone', $student->user->phone) }}">
                </div>
                <div class="grid-2">
                    <div class="form-group">
                        <label>Birthdate</label>
                        <input type="date" name="birthdate" class="form-control" value="{{ old('birthdate', $student->birthdate?->format('Y-m-d')) }}">
                    </div>
                    <div class="form-group">
                        <label>Gender</label>
                        <select name="gender" class="form-control">
                            <option value="">Select</option>
                            <option value="male" {{ old('gender', $student->gender) === 'male' ? 'selected' : '' }}>Male</option>
                            <option value="female" {{ old('gender', $student->gender) === 'female' ? 'selected' : '' }}>Female</option>
                            <option value="prefer_not_to_say" {{ old('gender', $student->gender) === 'prefer_not_to_say' ? 'selected' : '' }}>Prefer Not to Say</option>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label>Guardian Name</label>
                    <input type="text" name="guardian_name" class="form-control" value="{{ old('guardian_name', $student->guardian_name) }}">
                </div>
                <div class="form-group">
                    <label>Guardian Contact</label>
                    <input type="text" name="guardian_contact" class="form-control" value="{{ old('guardian_contact', $student->guardian_contact) }}">
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header"><span class="card-title">Academic Information</span></div>
            <div class="card-body">
                <div class="form-group">
                    <label>Course / Program *</label>
                    <input type="text" name="course" class="form-control" value="{{ old('course', $student->course) }}" required>
                </div>
                <div class="grid-2">
                    <div class="form-group">
                        <label>Year Level *</label>
                        <select name="year_level" class="form-control" required>
                            @foreach([1=>'1st Year',2=>'2nd Year',3=>'3rd Year',4=>'4th Year',5=>'5th Year'] as $val=>$label)
                            <option value="{{ $val }}" {{ old('year_level', $student->year_level) == $val ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Section</label>
                        <input type="text" name="section" class="form-control" value="{{ old('section', $student->section) }}">
                    </div>
                </div>
                <div class="form-group">
                    <label>Department</label>
                    <select name="department_id" class="form-control">
                        <option value="">— Select —</option>
                        @foreach($departments as $dept)
                        <option value="{{ $dept->department_id }}" {{ old('department_id', $student->department_id) == $dept->department_id ? 'selected' : '' }}>
                            {{ $dept->department_name }}
                        </option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
    </div>

    <div class="flex gap-2" style="margin-top:20px;">
        <button type="submit" class="btn btn-primary">
            <i class="ph-bold ph-floppy-disk"></i> Save Changes
        </button>
        <a href="{{ route('students.show', $student) }}" class="btn btn-outline">Cancel</a>
    </div>
</form>
@endsection
