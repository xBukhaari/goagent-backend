<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LogisticsJob extends Model
{
    use HasFactory;

    protected $table = 'logistics_jobs';

    protected $fillable = [
        'importer_id',
        'title',
        'description',
        'origin_port',
        'destination',
        'budget',
        'expected_delivery_date',
        'status',
    ];

    public function importer()
    {
        return $this->belongsTo(User::class, 'importer_id');
    }

    public function bids()
    {
        return $this->hasMany(Bid::class, 'job_id');
    }
}
