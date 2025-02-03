<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApprovalLog extends Model
{
    
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
}
