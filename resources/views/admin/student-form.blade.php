@extends('layouts.admin')

@section('title', $formMode === 'create' ? 'Add Student' : 'Edit Student')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/admin-users.css') }}">
    <link rel="stylesheet" href="{{ asset('css/admin-lecturer-form.css') }}">
@endpush

@section('content')
<div class="users-page">
    <div class="users-page__header">
        <div>
            <h1 class="users-page__title">{{ $formMode === 'create' ? 'Add Student' : 'Edit Student' }}</h1>
            <div class="users-page__breadcrumb">
                <span class="users-page__breadcrumb-accent">User Management</span>
                <span>&gt;</span>
                <a href="{{ route('admin.students') }}" class="lecturer-form-page__link">Students</a>
                <span>&gt;</span>
                <span>{{ $formMode === 'create' ? 'Add' : 'Edit' }}</span>
            </div>
        </div>
    </div>

    <section class="lecturer-form-card">
        @if ($errors->any())
            <div class="lecturer-form-alert lecturer-form-alert--error">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ $formMode === 'create' ? route('admin.students.store') : route('admin.students.update', $student) }}" class="lecturer-form">
            @csrf
            @if ($formMode === 'edit')
                @method('PUT')
            @endif

            <div class="lecturer-form__grid">
                <label class="lecturer-form__field">
                    <span>Tahfiz ID</span>
                    <input type="text" name="tahfiz_id" value="{{ old('tahfiz_id', $student->tahfiz_id) }}" placeholder="Enter student ID" required>
                </label>

                <label class="lecturer-form__field">
                    <span>Full Name</span>
                    <input type="text" name="name" value="{{ old('name', $student->name) }}" placeholder="Enter student name" required>
                </label>

                <label class="lecturer-form__field">
                    <span>Email Address</span>
                    <input type="email" name="email" value="{{ old('email', $student->email) }}" placeholder="Enter student email" required>
                </label>

                <label class="lecturer-form__field">
                    <span>Phone Number</span>
                    <input type="text" name="phone" value="{{ old('phone', $student->phone) }}" placeholder="Enter phone number">
                </label>

                <label class="lecturer-form__field">
                    <span>Gender</span>
                    @php($selectedGender = old('gender', $student->gender))
                    <select name="gender" required>
                        <option value="Male" {{ $selectedGender === 'Male' ? 'selected' : '' }}>Male</option>
                        <option value="Female" {{ $selectedGender === 'Female' ? 'selected' : '' }}>Female</option>
                    </select>
                </label>

                <label class="lecturer-form__field">
                    <span>Status</span>
                    @php($selectedStatus = old('status', $student->status ?: 'Active'))
                    <select name="status" required>
                        <option value="Active" {{ $selectedStatus === 'Active' ? 'selected' : '' }}>Active</option>
                        <option value="Inactive" {{ $selectedStatus === 'Inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </label>

                <label class="lecturer-form__field">
                    <span>Batch</span>
                    <input type="text" name="batch" value="{{ old('batch', $student->batch) }}" placeholder="Example: T14">
                </label>

                <label class="lecturer-form__field">
                    <span>Program</span>
                    <input type="text" name="program" value="{{ old('program', $student->program) }}" placeholder="Example: Tahfiz">
                </label>

                <label class="lecturer-form__field">
                    <span>Campus</span>
                    <input type="text" name="campus" value="{{ old('campus', $student->campus) }}" placeholder="Enter campus">
                </label>

                <label class="lecturer-form__field">
                    <span>{{ $formMode === 'create' ? 'Password' : 'New Password' }}</span>
                    <input type="password" name="password" placeholder="{{ $formMode === 'create' ? 'Enter password' : 'Leave blank to keep current password' }}" {{ $formMode === 'create' ? 'required' : '' }}>
                </label>

                <label class="lecturer-form__field">
                    <span>{{ $formMode === 'create' ? 'Confirm Password' : 'Confirm New Password' }}</span>
                    <input type="password" name="password_confirmation" placeholder="Confirm password" {{ $formMode === 'create' ? 'required' : '' }}>
                </label>
            </div>

            <div class="lecturer-form__actions">
                <a href="{{ route('admin.students') }}" class="users-button users-button--secondary">Cancel</a>
                <button type="submit" class="users-button users-button--primary">
                    {{ $formMode === 'create' ? 'Save Student' : 'Update Student' }}
                </button>
            </div>
        </form>
    </section>
</div>
@endsection
