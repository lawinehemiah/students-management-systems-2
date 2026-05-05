<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Permission\Traits\HasRoles;
use App\Traits\Auditable;

class User extends Authenticatable
{
    use Notifiable, HasRoles, Auditable, SoftDeletes;

    protected $fillable = [
        'first_name',
        'middle_name',
        'last_name',
        'email',
        'password',
        'user_type',
        'registration_number',
        'phone',
        'gender',
        'profile_photo',
        'department_id',
        'status', 
        'must_change_password',
        'can_be_impersonated',
        'impersonated_by',
        'last_impersonated_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'can_be_impersonated' => 'boolean',
        'impersonated_by' => 'integer',
        'last_impersonated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */
    
    public function impersonatedBy()
    {
        return $this->belongsTo(User::class, 'impersonated_by');
    }

    public function isBeingImpersonated()
    {
        return $this->impersonated_by !== null;
    }

    public function canBeImpersonated()
    {
        return $this->can_be_impersonated && $this->user_type === 'student';
    }

    public function applications()
    {
        return $this->hasMany(Application::class);
    }

    public function student()
    {
        return $this->hasOne(Student::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class, 'department_id');
    }

    /*
    |--------------------------------------------------------------------------
    | Role Checks
    |--------------------------------------------------------------------------
    */

    public function isSuperAdmin(): bool
    {
        return $this->hasRole('SuperAdmin');
    }

    public function isAdmin(): bool
    {
        return $this->hasAnyRole(['SuperAdmin', 'Director', 'Principal']);
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    public function scopeOrderByName($query)
    {
        return $query->orderBy('first_name')->orderBy('last_name');
    }

    public function scopeByDepartment($query, $departmentId)
    {
        return $query->where('department_id', $departmentId);
    }

    public function scopeByRole($query, $roleName)
    {
        return $query->whereHas('roles', function($q) use ($roleName) {
            $q->where('name', $roleName);
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Department Helpers
    |--------------------------------------------------------------------------
    */

    public function students()
    {
        return $this->hasMany(Student::class, 'department_id', 'department_id');
    }

    public function departmentStudents()
    {
        return Student::whereHas('programme', function($q) {
            $q->where('department_id', $this->department_id);
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Audit & Profile
    |--------------------------------------------------------------------------
    */

    public function shouldAudit(string $action): bool
    {
        if ($action === 'restored') {
            return false;
        }
        return true;
    }

    public function getAuditIdentifier(): ?string
    {
        return $this->email ?? $this->first_name . ' ' . $this->last_name ?? "User #{$this->id}";
    }

    public function getProfilePhotoUrlAttribute()
    {
        if ($this->profile_photo) {
            if (filter_var($this->profile_photo, FILTER_VALIDATE_URL)) {
                return $this->profile_photo;
            }
            return asset('storage/profile_photos/' . $this->profile_photo);
        }
        
        return 'https://ui-avatars.com/api/?name=' . urlencode($this->first_name . ' ' . $this->last_name) . '&color=7F9CF5&background=EBF4FF';
    }

    /*
    |--------------------------------------------------------------------------
    | IMPERSONATION METHODS (FIXED)
    |--------------------------------------------------------------------------
    */

    /**
     * Get redirect route after impersonation starts/stops
     * FIXED: Now returns existing route that works
     */
    public function getImpersonateRedirectRoute(): string
    {
        // Use an existing route that all users can access
        return 'superadmin.results.index';
    }

    /**
     * Get dashboard route (for other purposes)
     * FIXED: Now returns existing route
     */
    public function getDashboardRoute(): string
    {
        // Always return a route that exists
        return 'superadmin.results.index';
    }

    /**
     * Check if admin can impersonate this user
     */
    public function canBeImpersonatedBy(User $admin): bool
    {
        // Cannot impersonate yourself
        if ($admin->id === $this->id) {
            return false;
        }
        
        $adminRole = $admin->getRoleNames()->first();
        $targetRole = $this->getRoleNames()->first();
        
        // Predefined rules
        $rules = [
            'SuperAdmin' => ['*'],
            'Director' => ['Head_of_Department', 'Deputy_Principal_Academics', 'Tutor', 'Student'],
            'Principal' => ['Head_of_Department', 'Deputy_Principal_Academics', 'Tutor', 'Student'],
            'Deputy_Principal_Academics' => ['Tutor', 'Student'],
            'Head_of_Department' => ['Student'],
            'Dean_of_Students' => ['Student'],
            'Examination_Officer' => ['Student']
        ];
        
        $allowedRoles = $rules[$adminRole] ?? [];
        
        if (in_array('*', $allowedRoles)) {
            return true;
        }
        
        if (!in_array($targetRole, $allowedRoles)) {
            return false;
        }
        
        // HOD can only impersonate students in their department
        if ($adminRole === 'Head_of_Department') {
            return $this->student?->department_id === $admin->department_id;
        }
        
        return true;
    }

    /*
    |--------------------------------------------------------------------------
    | Pending Counts (Placeholders)
    |--------------------------------------------------------------------------
    */

    public function getPendingApprovalsCountAttribute()
    {
        return 0;
    }

    public function getPendingResultsCountAttribute()
    {
        return 0;
    }

    public function getPendingRequisitionsCountAttribute()
    {
        return 0;
    }

    public function getPendingLeaveCountAttribute()
    {
        return 0;
    }

    public function getPendingPromotionCountAttribute()
    {
        return 0;
    }

    public function getPendingBudgetCountAttribute()
    {
        return 0;
    }
}