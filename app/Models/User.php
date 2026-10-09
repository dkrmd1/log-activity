<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'avatar',
        'signature',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // ----- 3 role terpisah -----
    //   admin       : kelola user, pengaturan aplikasi & kategori (administrator sistem)
    //   group_head  : Group Head IT & GA -> ACC / tolak laporan bulanan (bertanda tangan)
    //   staff       : Staf IT & GA       -> input data & mengajukan laporan bulanan (bertanda tangan)

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isGroupHead(): bool
    {
        return $this->role === 'group_head';
    }

    public function isStaff(): bool
    {
        return $this->role === 'staff';
    }

    public function getRoleLabelAttribute(): string
    {
        return match ($this->role) {
            'admin' => 'Admin',
            'group_head' => 'Group Head',
            default => 'Staf',
        };
    }

    public function getRoleColorAttribute(): string
    {
        return match ($this->role) {
            'admin' => 'danger',
            'group_head' => 'primary',
            default => 'secondary',
        };
    }

    /**
     * Jabatan lengkap, dipakai di blok tanda tangan laporan.
     */
    public function getPositionLabelAttribute(): string
    {
        return match ($this->role) {
            'admin' => 'Administrator Sistem',
            'group_head' => 'Group Head Information Technology and General Affair',
            default => 'Staf Information Technology and General Affair',
        };
    }

    public function getAvatarUrlAttribute(): ?string
    {
        return $this->avatar ? Storage::url($this->avatar) : null;
    }
}
