<?php

namespace App\Services;

use App\Models\Goal;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

class GoalAccessService
{
    public function canViewGoal(User $user, Goal $goal): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return $this->belongsToUserDepartmentOrUnit($user, $goal);
    }

    public function canUpdateGoal(User $user, Goal $goal): bool
    {
        if (! $this->canViewGoal($user, $goal)) {
            return false;
        }

        return $user->canManageGoals();
    }

    public function canReviewGoal(User $user, Goal $goal): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return $user->canReviewGoals()
            && $this->belongsToUserDepartmentOrUnit($user, $goal);
    }

    public function scopeVisibleGoals(Builder $query, User $user): Builder
    {
        if ($user->isAdmin()) {
            return $query;
        }

        $departmentIds = $user->accessibleDepartmentIds();

        return $query->where(function (Builder $query) use ($user, $departmentIds) {
            $query->whereHas('assignedUsers', fn (Builder $query) => $query->whereKey($user->id))
                ->orWhereHas('assignments', function (Builder $query) use ($user, $departmentIds) {
                    $query->whereIn('department_id', $departmentIds)
                        ->where(function (Builder $query) use ($user) {
                            $query->whereNull('section_id')
                                ->whereNull('unit_id');

                            if ($user->section_id) {
                                $query->orWhere(function (Builder $query) use ($user) {
                                    $query->where('department_id', $user->department_id)
                                        ->where('section_id', $user->section_id)
                                        ->whereNull('unit_id');
                                });
                            }

                            if ($user->unit_id) {
                                $query->orWhere(function (Builder $query) use ($user) {
                                    $query->where('department_id', $user->department_id)
                                        ->where('unit_id', $user->unit_id);
                                });
                            }
                        });
                });
        });
    }

    private function belongsToUserDepartmentOrUnit(User $user, Goal $goal): bool
    {
        $departmentIds = $user->accessibleDepartmentIds();

        if (empty($departmentIds)) {
            return false;
        }

        return $goal->assignments()
            ->where(function (Builder $query) use ($user, $departmentIds) {
                $query->where('user_id', $user->id)
                    ->orWhere(function (Builder $query) use ($user, $departmentIds) {
                        $query->whereIn('department_id', $departmentIds)
                            ->where(function (Builder $query) use ($user) {
                                $query->whereNull('section_id')
                                    ->whereNull('unit_id');

                                if ($user->section_id) {
                                    $query->orWhere(function (Builder $query) use ($user) {
                                        $query->where('department_id', $user->department_id)
                                            ->where('section_id', $user->section_id)
                                            ->whereNull('unit_id');
                                    });
                                }

                                if ($user->unit_id) {
                                    $query->orWhere(function (Builder $query) use ($user) {
                                        $query->where('department_id', $user->department_id)
                                            ->where('unit_id', $user->unit_id);
                                    });
                                }
                            });
                    });
            })
            ->exists();
    }
}
