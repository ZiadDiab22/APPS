<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class user_types extends Model
{
    use HasFactory;
    public $timestamps = false;

    protected $fillable = [
        'name'
    ];
}
