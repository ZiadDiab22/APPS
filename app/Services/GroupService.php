<?php


namespace App\Services;

use App\Models\group;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class GroupService
{
  public function add($data)
  {
    $data['creater_id'] = auth()->user()->id;
    $groupModel = group::create($data);
    return $groupModel;
  }
}
