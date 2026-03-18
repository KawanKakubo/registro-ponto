<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'cpf',
        'role',
        'is_active',
        'is_super_admin',
        'email',
        'password',
        'establishment_id',
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
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'is_super_admin' => 'boolean',
        'is_active' => 'boolean',
    ];

    /**
     * Verifica se o usuário é super admin
     */
    public function isSuperAdmin(): bool
    {
        return $this->is_super_admin === true;
    }

    /**
     * Verifica se o usuário pode editar outro admin
     */
    public function canEditAdmin(User $admin): bool
    {
        // Super admin pode editar qualquer um
        if ($this->isSuperAdmin()) {
            return true;
        }
        
        // Admin normal só pode editar a si mesmo
        return $this->id === $admin->id;
    }

    /**
     * Verifica se o usuário pode excluir outro admin
     */
    public function canDeleteAdmin(User $admin): bool
    {
        // Apenas super admin pode excluir admins
        // E não pode excluir a si mesmo
        return $this->isSuperAdmin() && $this->id !== $admin->id;
    }

    /**
     * Relacionamento com estabelecimento
     */
    public function establishment()
    {
        return $this->belongsTo(Establishment::class);
    }
}
