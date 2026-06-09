<?php

namespace App\Services;

use App\Models\Goal;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

class GoalAccessService
{
    public function canViewGoal(User $user, Goal $goal): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        if ($goal->owner_id === $user->id) {
            return true;
        }

        return $this->belongsToUserDepartmentOrUnit($user, $goal);
    }

    public function canUpdateGoal(User $user, Goal $goal): bool
    {
        if (! $this->canViewGoal($user, $goal)) {
            return false;
        }

        return $user->isAdmin()
            || $user->isSupervisor()
            || $goal->owner_id === $user->id;
    }

    public function canReviewGoal(User $user, Goal $goal): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        return $user->isSupervisor()
            && $this->belongsToUserDepartmentOrUnit($user, $goal);
    }

    public function scopeVisibleGoals(Builder $query, User $user): Builder
    {
        if ($user->isSuperAdmin()) {
            return $query;
        }

        if ($user->isSupervisor()) {
            return $query
                ->where('department_id', $user->department_id)
                ->where(function (Builder $query) use ($user) {
                    $query->whereNull('unit_id');

                    if ($user->unit_id) {
                        $query->orWhere('unit_id', $user->unit_id);
                    }
                });
        }

        return $query
            ->where('department_id', $user->department_id)
            ->where(function (Builder $query) use ($user) {
                $query->whereNull('unit_id');

                if ($user->unit_id) {
                    $query->orWhere('unit_id', $user->unit_id);
                }
            });
    }

    private function belongsToUserDepartmentOrUnit(User $user, Goal $goal): bool
    {
        if (! $user->department_id || $user->department_id !== $goal->department_id) {
            return false;
        }

        if ($goal->unit_id) {
            return $user->unit_id === $goal->unit_id;
        }

        return true;
    }
}
