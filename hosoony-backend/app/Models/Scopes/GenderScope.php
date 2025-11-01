<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Auth;

class GenderScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     */
    public function apply(Builder $builder, Model $model): void
    {
        $user = Auth::user();
        
        // Only apply gender scope if user is not admin and model has gender column
        if ($user && !$user->isAdmin() && $model->getConnection()->getSchemaBuilder()->hasColumn($model->getTable(), 'gender')) {
            $builder->where('gender', $user->gender);
        }
    }
}
