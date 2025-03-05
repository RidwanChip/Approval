<?php

namespace App\Models;

use Spatie\Activitylog\LogOptions;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;

class ApprovalLog extends Model
{
    use LogsActivity;

    //Create relation to ApprovalRequest
    public function request()
    {
        return $this->belongsTo(ApprovalRequest::class, 'approval_request_id');
    }

    //Create relation to User
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'level',
                'action',
                'notes',
                'user.name',
                'request.flow.name'
            ])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('approval_log')
            ->setDescriptionForEvent(fn(string $eventName) => "Approval log has been {$eventName}")
            ->logFillable();
    }
}
