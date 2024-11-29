<?php


namespace App\Services;

use App\Models\File;
use Illuminate\Support\Facades\Storage;

class FileService
{
  public function addFile($file)
  {
    $data['name'] = $file->getClientOriginalName();
    $data['type'] = $file->getClientOriginalExtension();
    $data['content'] = file_get_contents($file->getRealPath());
    $data['creater_id'] = auth()->user()->id;

    $fileModel = file::create($data);

    Storage::put('uploads/' . $data['name'], $data['content']);


    return $fileModel;
  }

  // Retrieve all files from the database
  public function getAllFiles()
  {
    return File::all();
  }
}
