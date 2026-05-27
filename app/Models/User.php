<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Notifications\ResetPasswordNotification;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'department',
        'job_title',
        'phone_number',
        'profile_photo_path',
        'user_type',
        'user_status',
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
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function isAdmin(): bool
    {
        return strtolower(trim((string) $this->user_type)) === 'admin';
    }

    public function hasFullSystemAccess(): bool
    {
        return $this->isAdmin() || $this->hasFullAccessDepartment();
    }

    public function hasFullAccessDepartment(): bool
    {
        return in_array($this->normalizedDepartment(), [
            'ceosoffice',
            'itanddatasystems',
        ], true);
    }

    public function isDepartment(string $department): bool
    {
        return $this->normalizedDepartment() === $this->normalizeDepartment($department);
    }

    private function normalizedDepartment(): string
    {
        return $this->normalizeDepartment((string) $this->department);
    }

    private function normalizeDepartment(string $department): string
    {
        return preg_replace('/[^a-z0-9]+/', '', strtolower(trim($department))) ?? '';
    }

    public function getProfilePhotoUrlAttribute(): ?string
    {
        return $this->profile_photo_path
            ? asset('storage/' . $this->profile_photo_path)
            : null;
    }

    public function sendPasswordResetNotification($token): void
    {
        (new ResetPasswordNotification($token))->send($this);
    }


     public function pbmcs()
    {
        return $this->hasMany(Pbmc::class, 'created_by');
    }
}
