<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class User extends Authenticatable
{
    /** Thèmes d'interface disponibles (« auto » suit le système : Nuit ou Clair). */
    public const THEMES = ['nuit', 'minuit', 'ardoise', 'clair', 'sable', 'auto'];

    /** Thèmes clairs : gardent le mode clair historique (light_mode) synchronisé. */
    public const LIGHT_THEMES = ['clair', 'sable'];

    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'learning_goal',
        'daily_goal_minutes',
        'weekly_goal_sessions',
        'preferred_technology',
        'email_notifications',
        'learning_reminders',
        'light_mode',
        'theme',
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
            'daily_goal_minutes' => 'integer',
            'weekly_goal_sessions' => 'integer',
            'email_notifications' => 'boolean',
            'learning_reminders' => 'boolean',
            'light_mode' => 'boolean',
        ];
    }

    public function roadmaps(): HasMany
    {
        return $this->hasMany(Roadmap::class);
    }

    public function memos(): HasMany
    {
        return $this->hasMany(Memo::class);
    }

    public function memoFolders(): HasMany
    {
        return $this->hasMany(MemoFolder::class);
    }

    public function memoAttachments(): HasMany
    {
        return $this->hasMany(MemoAttachment::class);
    }

    public function tags(): HasMany
    {
        return $this->hasMany(Tag::class);
    }

    public function devLabProjects(): HasMany
    {
        return $this->hasMany(DevLabProject::class);
    }

    public function sandboxProjects(): HasMany
    {
        return $this->hasMany(SandboxProject::class);
    }

    public function learningProfile(): HasOne
    {
        return $this->hasOne(UserLearningProfile::class);
    }
}
