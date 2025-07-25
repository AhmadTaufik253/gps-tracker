<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LocationHistory extends Model
{
    use HasFactory;
    protected $table = 'location_histories';
    protected $fillable = ['device_id', 'latitude', 'longitude'];
}
