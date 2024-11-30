<?php

namespace App\Http\Controllers\Api;

use App\Services\FileService;
use App\Http\Controllers\Controller;
use App\Models\file;
use Illuminate\Http\Request;

class FileController extends Controller
{
    protected $fileService;

    public function __construct(FileService $fileService)
    {
        $this->fileService = $fileService;
    }

    public function addFile(Request $request)
    {
        $file = $request->file('file');
        $fileModel = $this->fileService->addFile($file);

        if ($fileModel) {
            $files = file::where('creater_id', auth()->user()->id)->get();
            return response()->json([
                'message' => 'done successfully',
                'file' => $files,
            ]);
        }

        return response()->json([
            'status' => false,
            'message' => 'failed to upload',
        ]);
    }

    public function showFiles()
    {
        $files = file::where('creater_id', auth()->user()->id)->get();

        return response([
            'status' => true,
            'files' => $files,
        ], 200);
    }

    // Show the list of uploaded files
    public function index()
    {
        $files = $this->fileService->getAllFiles();
        return view('files.index', compact('files'));
    }

    public function togglefreeFile($id)
    {
        if (!(file::where('id', $id)->exists())) {
            return response([
                'status' => false,
                'message' => 'not found, wrong id'
            ], 200);
        }

        if (!(file::where('id', $id)->where('creater_id', auth()->user()->id)->exists())) {
            return response([
                'status' => false,
                'message' => 'you dont have access to this file'
            ], 200);
        }

        $file = file::find($id);
        if ($file->available == 1) $file->available = 0;
        else $file->available = 1;
        $file->save();

        $files = file::where('creater_id', auth()->user()->id)->get();

        return response([
            'status' => true,
            'message' => "done successfully",
            'files' => $files,
        ], 200);
    }

    public function deleteFile($id)
    {
        if (!(file::where('id', $id)->exists())) {
            return response([
                'status' => false,
                'message' => 'not found, wrong id'
            ], 200);
        }

        if (!(file::where('id', $id)->where('creater_id', auth()->user()->id)->exists())) {
            return response([
                'status' => false,
                'message' => 'you dont have access to this file'
            ], 200);
        }

        file::where('id', $id)->delete();
        $files = file::where('creater_id', auth()->user()->id)->get();

        return response([
            'status' => true,
            'message' => "done successfully",
            'files' => $files,
        ], 200);
    }
}
