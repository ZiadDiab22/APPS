<?php


namespace App\Services;

use App\Models\group;


class GroupService
{
  public function add($data)
  {
    $data['creater_id'] = auth()->user()->id;
    $groupModel = group::create($data);
    return $groupModel;
  }
}
