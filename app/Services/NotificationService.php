<?php

namespace App\Services;

use App\Models\files_groups;
use App\Models\notification;
use App\Models\report;
use App\Models\users_groups;
use Illuminate\Support\Facades\Storage;

class NotificationService
{
  public function sendNotification($file_id, $user_id, $user_name, $file_name, $free)
  {
    $this->sendReports($file_id, $user_id, $free);

    $file_groups = files_groups::where('file_id', $file_id)->get(['group_id']);
    $ids = $file_groups->pluck('group_id');

    foreach ($ids as $id) {
      $group_users = users_groups::where('group_id', $id)->where('user_id', '!=', $user_id)->get(['user_id']);
      $users = $group_users->pluck('user_id');

      foreach ($users as $user) {
        if ($free) {
          notification::create([
            'text' => "File with id={$file_id} and name={$file_name} has been released by {$user_name}, the file is free now",
            'user_id' => $user,
          ]);
        } else
          notification::create([
            'text' => "File with id={$file_id} and name={$file_name} was reserved by {$user_name}, the file isnt available now",
            'user_id' => $user,
          ]);
      }
    }
  }

  public function sendReports($file_id, $user_id, $free)
  {
    if ($free) {
      report::create([
        'user_id' => $user_id,
        'file_id' => $file_id,
        'operation' => "F",
      ]);
    } else {
      report::create([
        'user_id' => $user_id,
        'file_id' => $file_id,
        'operation' => "R",
      ]);
    }
  }
}
