<?php


namespace App\Services;

use App\Models\files_groups;
use App\Models\group;
use App\Models\users_groups;

class GroupService
{
  public function add($data)
  {
    $data['creater_id'] = auth()->user()->id;
    $groupModel = group::create($data);
    users_groups::create([
      'group_id' => $groupModel->id,
      'user_id' => auth()->user()->id,
    ]);
    return $groupModel;
  }
}
