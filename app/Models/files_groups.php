<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class files_groups extends Model
{
    use HasFactory;
    public $timestamps = false;

    protected $fillable = [
        'group_id',
        'file_id'
    ];
}
