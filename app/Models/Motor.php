<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Motor extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'plate_number',
        'brand',
        'model',
        'year',
        'engine_number',
        'chassis_number',
        'color',
        'notes',
    ];

    /**
     * Get the user that owns the motor
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get all service histories for this motor
     */
    public function serviceHistories()
    {
        return $this->hasMany(ServiceHistory::class);
    }

    /**
     * Get latest service history
     */
    public function latestService()
    {
        return $this->hasOne(ServiceHistory::class)->latestOfMany();
    }
}
