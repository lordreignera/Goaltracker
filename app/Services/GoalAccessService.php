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

        return $query->where(function (Builder $query) use ($user) {
            $query->where(function (Builder $query) use ($user) {
                $query->where('department_id', $user->department_id)
                    ->where(function (Builder $query) use ($user) {
                        $query->whereNull('unit_id');

                        if ($user->unit_id) {
                            $query->orWhere('unit_id', $user->unit_id);
                        }
                    });
            })->orWhere(function (Builder $query) use ($user) {
                $query->whereHas('assignedDepartments', fn (Builder $query) => $query->whereKey($user->department_id))
                    ->where(function (Builder $query) use ($user) {
                        $query->whereDoesntHave('assignedUnits');

                        if ($user->unit_id) {
                            $query->orWhereHas('assignedUnits', fn (Builder $query) => $query->whereKey($user->unit_id));
                        }
                    });
            });
        });
    }

    private function belongsToUserDepartmentOrUnit(User $user, Goal $goal): bool
    {
        if (! $user->department_id) {
            return false;
        }

        $goal->loadMissing(['assignedDepartments:id', 'assignedUnits:id']);

        $assignedDepartmentIds = $goal->assignedDepartments->pluck('id');
        $assignedUnitIds = $goal->assignedUnits->pluck('id');

        if ($assignedDepartmentIds->isNotEmpty()) {
            if (! $assignedDepartmentIds->contains($user->department_id)) {
                return false;
            }

            return $assignedUnitIds->isEmpty()
                || ($user->unit_id && $assignedUnitIds->contains($user->unit_id));
        }

        if ($user->department_id !== $goal->department_id) {
            return false;
        }

        return ! $goal->unit_id || $user->unit_id === $goal->unit_id;
    }
}
