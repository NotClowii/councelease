@extends('layouts.app')

@section('title', 'Add Student')
@section('page-title', 'Add Student')
@section('breadcrumb', 'Students / New')

@section('content')
<div class="page-header">
    <div>
        <h1>Register New Student</h1>
        <p>Create a student account and profile</p>
    </div>
    <a href="{{ route('students.index') }}" class="btn btn-outline">
        <i class="ph-bold ph-arrow-left"></i> Back
    </a>
</div>

<form method="POST" action="{{ route('students.store') }}">
    @csrf
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;align-items:start;">

        {{-- Account Info --}}
        <div class="card">
            <div class="card-header"><span class="card-title">Account Information</span></div>
            <div class="card-body">
                <div class="grid-2">
                    <div class="form-group">
                        <label>First Name *</label>
                        <input type="text" name="first_name" class="form-control {{ $errors->has('first_name') ? 'is-invalid' : '' }}"
                            value="{{ old('first_name') }}" required>
                        @error('first_name') <div class="field-error">{{ $message }}</div> @enderror
                    </div>
                    <div class="form-group">
                        <label>Last Name *</label>
                        <input type="text" name="last_name" class="form-control {{ $errors->has('last_name') ? 'is-invalid' : '' }}"
                            value="{{ old('last_name') }}" required>
                        @error('last_name') <div class="field-error">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div class="form-group">
                    <label>Email Address *</label>
                    <input type="email" name="email" class="form-control {{ $errors->has('email') ? 'is-invalid' : '' }}"
                        value="{{ old('email') }}" required placeholder="student@school.edu">
                    @error('email') <div class="field-error">{{ $message }}</div> @enderror
                </div>

                <div class="form-group">
                    <label>Username *</label>
                    <input type="text" name="username" class="form-control {{ $errors->has('username') ? 'is-invalid' : '' }}"
                        value="{{ old('username') }}" required>
                    @error('username') <div class="field-error">{{ $message }}</div> @enderror
                </div>

                <div class="grid-2">
                    <div class="form-group">
                        <label>Password *</label>
                        <input type="password" name="password" class="form-control" required minlength="8">
                        @error('password') <div class="field-error">{{ $message }}</div> @enderror
                    </div>
                    <div class="form-group">
                        <label>Confirm Password *</label>
                        <input type="password" name="password_confirmation" class="form-control" required>
                    </div>
                </div>

                <div class="form-group">
                    <label>Phone Number</label>
                    <input type="text" name="phone" class="form-control" value="{{ old('phone') }}" placeholder="+63...">
                </div>
            </div>
        </div>

        {{-- Academic Info --}}
        <div class="card">
            <div class="card-header"><span class="card-title">Academic Information</span></div>
            <div class="card-body">
                <div class="form-group">
                    <label>Student ID Number *</label>
                    <input type="text" name="student_num" class="form-control {{ $errors->has('student_num') ? 'is-invalid' : '' }}"
                        value="{{ old('student_num') }}" required placeholder="e.g., 2024-00001">
                    @error('student_num') <div class="field-error">{{ $message }}</div> @enderror
                </div>

                <div class="form-group">
                    <label>Course / Program *</label>
                    <input type="text" name="course" class="form-control {{ $errors->has('course') ? 'is-invalid' : '' }}"
                        value="{{ old('course') }}" required placeholder="e.g., BS Information Technology">
                    @error('course') <div class="field-error">{{ $message }}</div> @enderror
                </div>

                <div class="grid-2">
                    <div class="form-group">
                        <label>Year Level *</label>
                        <select name="year_level" class="form-control" required>
                            <option value="">Select</option>
                            @foreach([1=>'1st Year',2=>'2nd Year',3=>'3rd Year',4=>'4th Year',5=>'5th Year'] as $val=>$label)
                            <option value="{{ $val }}" {{ old('year_level') == $val ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Section</label>
                        <input type="text" name="section" class="form-control" value="{{ old('section') }}" placeholder="e.g., A">
                    </div>
                </div>

                <div class="form-group">
                    <label>Department</label>
                    <select name="department_id" class="form-control">
                        <option value="">— Select Department —</option>
                        @foreach($departments as $dept)
                        <option value="{{ $dept->department_id }}" {{ old('department_id') == $dept->department_id ? 'selected' : '' }}>
                            {{ $dept->department_name }}
                        </option>
                        @endforeach
                    </select>
                </div>

                <div class="grid-2">
                    <div class="form-group">
                        <label>Birthdate</label>
                        <input type="date" name="birthdate" class="form-control" value="{{ old('birthdate') }}">
                    </div>
                    <div class="form-group">
                        <label>Gender</label>
                        <select name="gender" class="form-control">
                            <option value="">Select</option>
                            <option value="male" {{ old('gender') === 'male' ? 'selected' : '' }}>Male</option>
                            <option value="female" {{ old('gender') === 'female' ? 'selected' : '' }}>Female</option>
                            <option value="prefer_not_to_say" {{ old('gender') === 'prefer_not_to_say' ? 'selected' : '' }}>Prefer Not to Say</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        {{-- Guardian Info --}}
        <div class="card" style="grid-column:span 2;">
            <div class="card-header"><span class="card-title">Guardian / Emergency Contact</span></div>
            <div class="card-body">
                <div class="grid-3">
                    <div class="form-group">
                        <label>Guardian Name</label>
                        <input type="text" name="guardian_name" class="form-control" value="{{ old('guardian_name') }}">
                    </div>
                    <div class="form-group">
                        <label>Guardian Contact</label>
                        <input type="text" name="guardian_contact" class="form-control" value="{{ old('guardian_contact') }}">
                    </div>
                    <div class="form-group">
                        <label>Home Address</label>
                        <input type="text" name="address" class="form-control" value="{{ old('address') }}">
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="flex gap-2" style="margin-top:20px;">
        <button type="submit" class="btn btn-primary">
            <i class="ph-bold ph-user-plus"></i> Register Student
        </button>
        <a href="{{ route('students.index') }}" class="btn btn-outline">Cancel</a>
    </div>
</form>
@endsection
