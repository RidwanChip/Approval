<?php

namespace App\Models;

use Spatie\Activitylog\LogOptions;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;

class ApprovalRequest extends Model
{
    use LogsActivity;

    // Start Relation Group

    // Create realtion to user
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    // Create realtion to ApprovalFlow
    public function flow()
    {
        return $this->belongsTo(ApprovalFlow::class, 'approval_flow_id');
    }
    // Create realtion to ApprovalLog
    public function logs()
    {
        return $this->hasMany(ApprovalLog::class);
    }

    // End Relation Group

    // Start Action Group

    // Action Approve
    public function approve(string $notes = ''): void
    {
        $this->logs()->create([
            'user_id' => auth()->id(),
            'level' => $this->current_level,
            'action' => 'approved',
            'notes' => $notes // Simpan notes
        ]);

        if ($this->current_level >= $this->flow->steps()->count()) {
            $this->update(['status' => 'approved']);
            return;
        }

        $this->increment('current_level');
    }

    // Action OnHold
    public function onHold(string $notes): void
    {
        $this->logs()->create([
            'user_id' => auth()->id(),
            'level' => $this->current_level,
            'action' => 'onHold',
            'notes' => $notes // Simpan notes
        ]);

        $this->update(['status' => 'onHold']);
    }

    // Action Reject
    public function reject(string $notes): void
    {
        $this->logs()->create([
            'user_id' => auth()->id(),
            'level' => $this->current_level,
            'action' => 'rejected',
            'notes' => $notes // Simpan notes
        ]);

        $this->update(['status' => 'rejected']);
    }

    // Action check status pending
    public function isApprovalPending(): bool
    {
        return $this->status === 'pending' || $this->status === 'onHold';
    }

    // End Action Group

    protected static function booted()
    {
        static::deleting(function ($request) {
            // Hapus semua log terlebih dahulu
            $request->logs()->delete();
        });
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'current_level',
                'status',
                'user.name',
                'flow.name'
            ])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('approval_request')
            ->setDescriptionForEvent(fn(string $eventName) => "Approval request has been {$eventName}")
            ->logFillable();
    }
}
