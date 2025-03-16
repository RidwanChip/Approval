<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Filament\Panel;
use Spatie\Activitylog\LogOptions;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Notifications\Notifiable;
use Spatie\Activitylog\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasRoles, LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'name',
                'email',
            ])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('user')
            ->setDescriptionForEvent(fn(string $eventName) => "User has been {$eventName}")
            ->logFillable();
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
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
        ];
    }

    // Create relation to Employee
    public function employee(): HasOne
    {
        return $this->hasOne(Employee::class);
    }

    // Action related user canApprove
    public function canApprove(ApprovalRequest $request): bool
    {
        if (!$request->flow) {
            return false; // Jika tidak ada flow, tidak bisa approve
        }

        $currentStep = $request->flow->steps()
            ->where('level', $request->current_level)
            ->first();

        if (!$currentStep) {
            return false; // Jika tidak ada step, tidak bisa approve
        }

        return $currentStep && $currentStep->user_id === $this->id;
    }
    public function canAccessPanel(Panel $panel): bool
    {
        return $this->hasRole(['Admin']);
    }
}
