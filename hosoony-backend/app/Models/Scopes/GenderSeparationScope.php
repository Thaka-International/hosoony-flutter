<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Auth;

class GenderSeparationScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     */
    public function apply(Builder $builder, Model $model): void
    {
        // Skip scope for admin users
        if (Auth::check() && Auth::user()->isAdmin()) {
            return;
        }

        // Skip scope for unauthenticated users (will be handled by policies)
        if (!Auth::check()) {
            return;
        }

        $userGender = Auth::user()->gender;

        // Apply gender filter based on model type
        if ($model instanceof \App\Models\ClassModel) {
            $builder->where('gender', $userGender);
        } elseif ($model instanceof \App\Models\Session) {
            $builder->whereHas('class', function ($query) use ($userGender) {
                $query->where('gender', $userGender);
            });
        } elseif ($model instanceof \App\Models\ClassSchedule) {
            $builder->whereHas('class', function ($query) use ($userGender) {
                $query->where('gender', $userGender);
            });
        } elseif ($model instanceof \App\Models\SessionItem) {
            $builder->whereHas('session.class', function ($query) use ($userGender) {
                $query->where('gender', $userGender);
            });
        } elseif ($model instanceof \App\Models\Attendance) {
            $builder->whereHas('session.class', function ($query) use ($userGender) {
                $query->where('gender', $userGender);
            });
        } elseif ($model instanceof \App\Models\Activity) {
            $builder->whereHas('class', function ($query) use ($userGender) {
                $query->where('gender', $userGender);
            });
        } elseif ($model instanceof \App\Models\ActivitySubmission) {
            $builder->whereHas('activity.class', function ($query) use ($userGender) {
                $query->where('gender', $userGender);
            });
        } elseif ($model instanceof \App\Models\Exam) {
            $builder->whereHas('class', function ($query) use ($userGender) {
                $query->where('gender', $userGender);
            });
        } elseif ($model instanceof \App\Models\ExamResult) {
            $builder->whereHas('exam.class', function ($query) use ($userGender) {
                $query->where('gender', $userGender);
            });
        }
    }
}
