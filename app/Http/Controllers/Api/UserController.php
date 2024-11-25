<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    public function upload(Request $request)
    {
        // Validate that a file was uploaded
        if (!$request->hasFile('file')) {
            return response()->json(['error' => 'No file was uploaded'], 422);
        }

        // Get the uploaded file
        $file = $request->file('file');

        // Get the file name
        $fileName = $file->getClientOriginalName();

        // Get the file extension
        $fileExtension = $file->getClientOriginalExtension();

        // Get the file type
        $fileType = $file->getMimeType();

        // Read the file content
        $fileContent = file_get_contents($file->getRealPath());

        // Save the file to storage if needed
        Storage::put('uploads/' . $fileName, $fileContent);

        return response()->json([
            'name' => $fileName,
            'type' => $fileType,
            'extension' => $fileExtension,
            'size' => $file->getSize(),
            'content' => $fileContent
        ]);
    }
}
