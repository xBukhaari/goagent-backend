<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bid extends Model
{
    use HasFactory;

    protected $fillable = [
        'job_id',
        'agent_id',
        'amount',
        'message',
        'estimated_completion_time',
        'status',
    ];

    public function job()
    {
        return $this->belongsTo(LogisticsJob::class, 'job_id');
    }

    public function agent()
    {
        return $this->belongsTo(User::class, 'agent_id');
    }
}
