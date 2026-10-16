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

        if ($goal->created_by === $user->id) {
            return true;
        }

        if ($goal->status === 'draft') {
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
        if ($goal->status === 'draft') {
            return false;
        }

        if ($user->isAdmin()) {
            return true;
        }

        return $user->canReviewGoals()
            && $this->belongsToUserDepartmentOrSection($user, $goal);
    }

    public function canSubmitDailyReport(User $user, Goal $goal): bool
    {
        if ($goal->status === 'draft') {
            return false;
        }

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

        return $query
            ->where(function (Builder $query) use ($user) {
                $query->where('status', '!=', 'draft')
                    ->orWhere('created_by', $user->id);
            })
            ->where(function (Builder $query) use ($user) {
                $query->where('created_by', $user->id)
                    ->orWhereHas('assignedUsers', fn (Builder $query) => $query->whereKey($user->id))
                    ->orWhereHas('assignments', fn (Builder $query) => $this->scopeToUserDepartment($query, $user));
            });
    }

    private function belongsToUserDepartmentOrSection(User $user, Goal $goal): bool
    {
        if ($goal->assignments()->where('user_id', $user->id)->exists()) {
            return true;
        }

        return $goal->assignments()
            ->where(fn (Builder $query) => $this->scopeToUserDepartment($query, $user))
            ->exists();
    }

    /**
     * Match department-wide assignments and assignments for the user's own section.
     * Extra departments a user can administer do not grant ordinary goal visibility.
     */
    private function scopeToUserDepartment(Builder $query, User $user): Builder
    {
        $query->where('department_id', $user->department_id)
            ->whereNull('user_id')
            ->where(function (Builder $query) use ($user) {
                $query->whereNull('section_id');

                if ($user->section_id) {
                    $query->orWhere('section_id', $user->section_id);
                }
            });

        return $query;
    }
}
