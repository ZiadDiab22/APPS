<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class group extends Model
{
    use HasFactory;
    public $timestamps = false;

    protected $fillable = [
        'name',
        'access_type_id',
        'creater_id'
    ];
}
