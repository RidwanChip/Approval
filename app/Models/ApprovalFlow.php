<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ApprovalFlow extends Model
{
    use SoftDeletes;
    //Create relation to ApprovalFlowStep
    public function steps()
    {
        return $this->hasMany(ApprovalFlowStep::class);
    }

    //Function for delete step
    protected static function booted()
    {
        static::deleting(function ($flow) {
            $flow->steps()->delete();
        });
    }
}
