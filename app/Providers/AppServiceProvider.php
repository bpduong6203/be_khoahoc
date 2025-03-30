<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Models\User;
use App\Models\Course;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [];

    public function boot(): void
    {
        $this->registerPolicies();

        Gate::define('admin-access', function (User $user) {
            return $user->hasRole('admin');
        });

        Gate::define('teacher-access', function (User $user) {
            return $user->hasRole('instructor');
        });

        Gate::define('student-access', function (User $user) {
            return $user->hasRole('user');
        });

        Gate::define('teacher-or-admin', function (User $user) {
            return $user->hasAnyRole(['instructor', 'admin']);
        });

        Gate::define('view-course', function (User $user, $courseId) {
            if ($user->hasRole('admin')) return true;
            $course = Course::find($courseId);
            if (!$course) return false;
            if ($user->hasRole('instructor')) return $course->user_id === $user->id;
            if ($user->hasRole('user')) {
                $isEnrolled = $user->enrollments()->where('course_id', $courseId)->exists();
                $isPublic = $course->status === 'Published';
                return $isEnrolled || $isPublic;
            }
            return false;
        });

        Gate::define('update-course', function (User $user, $courseId) {
            $course = Course::find($courseId);
            return $course && ($user->hasRole('admin') || ($user->hasRole('instructor') && $course->user_id === $user->id));
        });

        Gate::define('delete-course', function (User $user, $courseId) {
            $course = Course::find($courseId);
            return $course && ($user->hasRole('admin') || ($user->hasRole('instructor') && $course->user_id === $user->id));
        });
    }
}