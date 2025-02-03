<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApprovalFlowStep extends Model
{
    //Relation to User
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
