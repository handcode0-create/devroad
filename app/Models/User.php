<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that should be mass assignable.
     *
     * @var list<string>
     */
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
            'password' => 'hashed',
            'daily_goal_minutes' => 'integer',
            'weekly_goal_sessions' => 'integer',
            'email_notifications' => 'boolean',
            'learning_reminders' => 'boolean',
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
}
