<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class file extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'type',
        'content',
        'available',
        'creater_id',
        'reserver_id'
    ];
}
