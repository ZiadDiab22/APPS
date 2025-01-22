<?php


namespace App\Services;

use App\Models\File;
use Illuminate\Support\Facades\Storage;
use Pdf;

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

  public function getAllFiles()
  {
    return File::all();
  }

  public function exportCSV($data)
  {
    $filename = "apps_database_" . substr(str_replace('.', '', microtime(true)), -4) . ".csv";
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Content-Type: text/html; charset=utf-8');
    $output = fopen('php://output', 'w');
    fputs($output, "\xEF\xBB\xBF");
    fputcsv($output, ['Reports'], ';');
    fputcsv($output, [], ';');

    $array = json_decode($data, true);
    $cols = array_keys($array[0]);

    fputcsv($output, $cols, ';');

    foreach ($data as $rec) {
      $record = [];
      $array = json_decode($rec, true);
      $record = array_values($array);
      fputcsv($output, $record, ';');
    }

    fclose($output);
    exit;
  }
}
