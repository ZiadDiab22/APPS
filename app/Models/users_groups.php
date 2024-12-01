<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class users_groups extends Model
{
    use HasFactory;
    public $timestamps = false;

    protected $fillable = [
        'group_id',
        'user_id'
    ];
}
