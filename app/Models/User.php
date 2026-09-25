<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'phone',
        'position',
        'salary_base',
        'salary_allowance',
        'bank_name',
        'bank_account_number',
        'bank_account_holder',
        'is_active',
        'approval_status',
        'approved_at',
        'approved_by',
        'rejection_reason',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
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
            'password' => 'hashed',
            'is_active' => 'boolean',
            'salary_base' => 'decimal:2',
            'salary_allowance' => 'decimal:2',
        ];
    }

    public function staffPayrolls()
    {
        return $this->hasMany(StaffPayroll::class, 'user_id');
    }

    public function isAdmin(): bool
    {
        return $this->role === 'ADMIN';
    }

    public function isStaff(): bool
    {
        return $this->role === 'STAFF';
    }

    public function isGuide(): bool
    {
        return $this->role === 'GUIDE';
    }

    public function guide()
    {
        return $this->hasOne(Guide::class, 'user_id');
    }

    public function isApproved(): bool
    {
        return $this->approval_status === 'APPROVED';
    }

    public function isPendingApproval(): bool
    {
        return $this->approval_status === 'PENDING';
    }

    public function isRejected(): bool
    {
        return $this->approval_status === 'REJECTED';
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function auditLogs()
    {
        return $this->hasMany(AuditLog::class, 'user_id');
    }

    public function sendEmailVerificationNotification(): void
    {
        $this->notify(new \App\Notifications\VerifyEmailNotification());
    }
}
