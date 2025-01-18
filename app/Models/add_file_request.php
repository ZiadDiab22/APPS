<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class add_file_request extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'type',
        'content',
        'accepted',
        'user_id',
        'group_id'
    ];
}
