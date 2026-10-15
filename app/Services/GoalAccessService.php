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

        if ($goal->status === 'draft' && $goal->created_by !== $user->id) {
            return false;
        }

        return $this->belongsToUserDepartmentOrSection($user, $goal);
    }

    public function canUpdateGoal(User $user, Goal $goal): bool
    {
        if (! $this->canViewGoal($user, $goal)) {
            return false;
        }

        return $user->canManageGoals()
            || ($goal->created_by === $user->id && $goal->status === 'draft');
    }

    public function canReviewGoal(User $user, Goal $goal): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return $user->canReviewGoals()
            && $this->belongsToUserDepartmentOrSection($user, $goal);
    }

    public function canSubmitDailyReport(User $user, Goal $goal): bool
    {
        if (! $this->canViewGoal($user, $goal)) {
            return false;
        }

        return $user->isAdmin()
            || $user->canManageGoals()
            || $user->can('submit daily reports');
    }

    public function scopeVisibleGoals(Builder $query, User $user): Builder
    {
        if ($user->isAdmin()) {
            return $query;
        }

        $departmentIds = $user->accessibleDepartmentIds();

        return $query
            ->where(function (Builder $query) use ($user) {
                $query->where('status', '!=', 'draft')
                    ->orWhere('created_by', $user->id);
            })
            ->where(function (Builder $query) use ($user, $departmentIds) {
                $query->whereHas('assignedUsers', fn (Builder $query) => $query->whereKey($user->id))
                    ->orWhereHas('assignments', function (Builder $query) use ($user, $departmentIds) {
                        $query->whereIn('department_id', $departmentIds)
                            ->where(function (Builder $query) use ($user) {
                                $query->whereNull('section_id')
                                    ->whereNull('user_id');

                                if ($user->section_id) {
                                    $query->orWhere(function (Builder $query) use ($user) {
                                        $query->where('department_id', $user->department_id)
                                            ->where('section_id', $user->section_id);
                                    });
                                }
                            });
                    });
            });
    }

    private function belongsToUserDepartmentOrSection(User $user, Goal $goal): bool
    {
        if ($goal->assignments()->where('user_id', $user->id)->exists()) {
            return true;
        }

        $departmentIds = $user->accessibleDepartmentIds();

        if (empty($departmentIds)) {
            return false;
        }

        return $goal->assignments()
            ->whereIn('department_id', $departmentIds)
            ->where(function (Builder $query) use ($user) {
                $query->whereNull('section_id')
                    ->whereNull('user_id');

                if ($user->section_id) {
                    $query->orWhere(function (Builder $query) use ($user) {
                        $query->where('department_id', $user->department_id)
                            ->where('section_id', $user->section_id);
                    });
                }
            })
            ->exists();
    }
}
