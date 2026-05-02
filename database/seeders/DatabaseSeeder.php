<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\Department;
use App\Models\User;
use App\Models\Student;
use App\Models\Counselor;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Seed Roles
        $roles = [
            ['role_name' => 'Student',      'description' => 'Enrolled students who can book appointments'],
            ['role_name' => 'Counselor',    'description' => 'Licensed school counselors'],
            ['role_name' => 'Office Staff', 'description' => 'Guidance office administrative staff'],
            ['role_name' => 'School Admin', 'description' => 'School administrator with report access'],
            ['role_name' => 'System Admin', 'description' => 'Full system access and configuration'],
        ];

        foreach ($roles as $role) {
            Role::firstOrCreate(['role_name' => $role['role_name']], $role);
        }

        // Seed Departments
        $departments = [
            ['department_name' => 'College of Computer Studies',        'department_code' => 'CCS'],
            ['department_name' => 'College of Business Administration',  'department_code' => 'CBA'],
            ['department_name' => 'College of Engineering',              'department_code' => 'COE'],
            ['department_name' => 'College of Education',                'department_code' => 'CED'],
            ['department_name' => 'College of Arts and Sciences',        'department_code' => 'CAS'],
            ['department_name' => 'College of Nursing',                  'department_code' => 'CON'],
        ];

        foreach ($departments as $dept) {
            Department::firstOrCreate(['department_code' => $dept['department_code']], $dept);
        }

        $sysAdminRole    = Role::where('role_name', 'System Admin')->first();
        $counselorRole   = Role::where('role_name', 'Counselor')->first();
        $staffRole       = Role::where('role_name', 'Office Staff')->first();
        $schoolAdminRole = Role::where('role_name', 'School Admin')->first();
        $studentRole     = Role::where('role_name', 'Student')->first();

        // System Admin
        User::updateOrCreate(['email' => 'admin@counseling.edu'], [
            'username'   => 'sysadmin',
            'password'   => Hash::make('password'),
            'first_name' => 'System',
            'last_name'  => 'Administrator',
            'role_id'    => $sysAdminRole->role_id,
            'is_active'  => true,
        ]);

        // School Admin
        User::updateOrCreate(['email' => 'schooladmin@counseling.edu'], [
            'username'   => 'schooladmin',
            'password'   => Hash::make('password'),
            'first_name' => 'Maria',
            'last_name'  => 'Reyes',
            'role_id'    => $schoolAdminRole->role_id,
            'is_active'  => true,
        ]);

        // Office Staff
        User::updateOrCreate(['email' => 'staff@counseling.edu'], [
            'username'   => 'officestaff',
            'password'   => Hash::make('password'),
            'first_name' => 'Ana',
            'last_name'  => 'Santos',
            'role_id'    => $staffRole->role_id,
            'is_active'  => true,
        ]);

        // Counselors
        $ccsDept = Department::where('department_code', 'CCS')->first();
        $cbaDept = Department::where('department_code', 'CBA')->first();

        $counselor1User = User::updateOrCreate(['email' => 'counselor1@counseling.edu'], [
            'username'   => 'counsel_garcia',
            'password'   => Hash::make('password'),
            'first_name' => 'Dr. Jose',
            'last_name'  => 'Garcia',
            'role_id'    => $counselorRole->role_id,
            'is_active'  => true,
        ]);

        Counselor::firstOrCreate(['user_id' => $counselor1User->user_id], [
            'department_id'            => $ccsDept->department_id,
            'specialization'           => 'Academic and Career Counseling',
            'license_number'           => 'RPm-2019-001',
            'max_appointments_per_day' => 8,
            'available_days'           => [1, 2, 3, 4, 5],
            'available_from'           => '08:00:00',
            'available_until'          => '17:00:00',
        ]);

        $counselor2User = User::updateOrCreate(['email' => 'counselor2@counseling.edu'], [
            'username'   => 'counsel_dela_cruz',
            'password'   => Hash::make('password'),
            'first_name' => 'Dr. Liza',
            'last_name'  => 'Dela Cruz',
            'role_id'    => $counselorRole->role_id,
            'is_active'  => true,
        ]);

        Counselor::firstOrCreate(['user_id' => $counselor2User->user_id], [
            'department_id'            => $cbaDept->department_id,
            'specialization'           => 'Personal and Social Development',
            'license_number'           => 'RPm-2020-042',
            'max_appointments_per_day' => 6,
            'available_days'           => [1, 2, 3, 4, 5],
            'available_from'           => '09:00:00',
            'available_until'          => '16:00:00',
        ]);

        // Demo Student
        $studentUser = User::updateOrCreate(['email' => 'student@counseling.edu'], [
            'username'   => 'demo_student',
            'password'   => Hash::make('password'),
            'first_name' => 'Juan',
            'last_name'  => 'dela Cruz',
            'role_id'    => $studentRole->role_id,
            'is_active'  => true,
        ]);

        Student::firstOrCreate(['user_id' => $studentUser->user_id], [
            'student_num'   => '2024-00001',
            'course'        => 'Bachelor of Science in Information Technology',
            'year_level'    => 2,
            'section'       => 'A',
            'department_id' => $ccsDept->department_id,
            'gender'        => 'male',
        ]);

        $this->command->info('✅ Seeding complete!');
        $this->command->table(
            ['Role', 'Email', 'Password'],
            [
                ['System Admin', 'admin@counseling.edu',       'password'],
                ['School Admin', 'schooladmin@counseling.edu', 'password'],
                ['Office Staff', 'staff@counseling.edu',       'password'],
                ['Counselor 1',  'counselor1@counseling.edu',  'password'],
                ['Counselor 2',  'counselor2@counseling.edu',  'password'],
                ['Student',      'student@counseling.edu',     'password'],
            ]
        );
    }
}