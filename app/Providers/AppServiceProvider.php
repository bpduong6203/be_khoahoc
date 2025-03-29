<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Models\User;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [];

    public function boot(): void
    {
        $this->registerPolicies();

        // Quyền Admin
        Gate::define('admin-access', function (User $user) {
            return $user->hasRole('admin');
        });

        // Quyền Teacher
        Gate::define('teacher-access', function (User $user) {
            return $user->hasRole('instructor');
        });

        // Quyền Student
        Gate::define('student-access', function (User $user) {
            return $user->hasRole('user');
        });

        // Quyền Teacher hoặc Admin
        Gate::define('teacher-or-admin', function (User $user) {
            return $user->hasAnyRole(['instructor', 'admin']);
        });

        // Quyền xem khóa học
        Gate::define('view-course', function (User $user, $courseId) {
            if ($user->hasRole('admin')) {
                return true;
            }
            if ($user->hasRole('instructor')) {
                return $user->courses()->where('id', $courseId)->exists();
            }
            if ($user->hasRole('user')) {
                return $user->enrollments()->where('course_id', $courseId)->exists();
            }
            return false;
        });
    }
}