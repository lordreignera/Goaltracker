<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Jetstream\HasProfilePhoto;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens;

    /** @use HasFactory<UserFactory> */
    use HasFactory;

    use HasProfilePhoto;
    use HasRoles;
    use Notifiable;
    use TwoFactorAuthenticatable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'first_name',
        'second_name',
        'email',
        'phone_number',
        'password',
        'department_id',
        'section_id',
        'unit_id',
        'supervisor_id',
        'role',
        'requested_role',
        'approval_status',
        'is_active',
        'approved_at',
        'approved_by',
        'last_login_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array<int, string>
     */
    protected $appends = [
        'profile_photo_url',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'approved_at' => 'datetime',
            'last_login_at' => 'datetime',
            'is_active' => 'boolean',
            'password' => 'hashed',
        ];
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function accessibleDepartments()
    {
        return $this->belongsToMany(Department::class)->withTimestamps();
    }

    public function accessibleDepartmentIds(): array
    {
        $ids = $this->relationLoaded('accessibleDepartments')
            ? $this->accessibleDepartments->pluck('id')
            : $this->accessibleDepartments()->pluck('departments.id');

        return $ids
            ->push($this->department_id)
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    public function section()
    {
        return $this->belongsTo(Section::class);
    }

    public function supervisor()
    {
        return $this->belongsTo(User::class, 'supervisor_id');
    }

    public function staff()
    {
        return $this->hasMany(User::class, 'supervisor_id');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function isApproved(): bool
    {
        return $this->approval_status === 'approved' && $this->is_active;
    }

    public function isAdmin(): bool
    {
        return $this->hasRole('Super Admin') || $this->hasRole('Admin') || $this->role === 'admin';
    }

    public function isSuperAdmin(): bool
    {
        return $this->hasRole('Super Admin');
    }

    public function isSupervisor(): bool
    {
        return $this->hasRole('Supervisor')
            || $this->hasRole('Manager')
            || in_array($this->role, ['supervisor', 'manager'], true);
    }

    public function canManageGoals(): bool
    {
        return $this->isAdmin()
            || $this->isSupervisor()
            || $this->can('manage goals');
    }

    public function canReviewGoals(): bool
    {
        return $this->isAdmin()
            || $this->isSupervisor()
            || $this->can('review goals');
    }

    public function canManageAdministration(): bool
    {
        return $this->isAdmin()
            || $this->hasAnyPermission([
                'manage departments',
                'manage sections',
                'manage units',
                'manage users',
                'manage quarters',
            ]);
    }

    public function hasAnyPermission(array $permissions): bool
    {
        foreach ($permissions as $permission) {
            if ($this->can($permission)) {
                return true;
            }
        }

        return false;
    }
}
