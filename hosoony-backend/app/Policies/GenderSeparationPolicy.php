<?php

namespace App\Policies;

use App\Models\User;
use App\Models\ClassModel;
use Illuminate\Auth\Access\HandlesAuthorization;

class GenderSeparationPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can assign a student to a class.
     */
    public function assignStudentToClass(User $user, ClassModel $class, User $student): bool
    {
        // Admin can assign anyone
        if ($user->isAdmin()) {
            return true;
        }

        // Check if student and class have compatible genders
        if ($student->gender !== $class->gender) {
            return false;
        }

        // Teachers can only assign to their own gender classes
        if ($user->isTeacher() && $user->gender !== $class->gender) {
            return false;
        }

        return true;
    }

    /**
     * Determine whether the user can view a class.
     */
    public function viewClass(User $user, ClassModel $class): bool
    {
        // Admin can view all classes
        if ($user->isAdmin()) {
            return true;
        }

        // Users can only view classes of their own gender
        return $user->gender === $class->gender;
    }

    /**
     * Determine whether the user can view sessions of a class.
     */
    public function viewClassSessions(User $user, ClassModel $class): bool
    {
        return $this->viewClass($user, $class);
    }

    /**
     * Determine whether the user can view students of a class.
     */
    public function viewClassStudents(User $user, ClassModel $class): bool
    {
        return $this->viewClass($user, $class);
    }

    /**
     * Determine whether the user can teach a class.
     */
    public function teachClass(User $user, ClassModel $class): bool
    {
        // Admin can teach any class
        if ($user->isAdmin()) {
            return true;
        }

        // Teachers can only teach classes of their own gender
        if ($user->isTeacher()) {
            return $user->gender === $class->gender;
        }

        return false;
    }

    /**
     * Determine whether the user can access mixed gender content.
     */
    public function accessMixedGender(User $user): bool
    {
        // Only admin can access mixed gender content
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can communicate with opposite gender.
     */
    public function communicateWithOppositeGender(User $user, User $targetUser): bool
    {
        // Admin can communicate with anyone
        if ($user->isAdmin()) {
            return true;
        }

        // Users cannot communicate with opposite gender
        return $user->gender === $targetUser->gender;
    }
}
