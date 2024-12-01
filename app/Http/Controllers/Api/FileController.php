<?php

namespace App\Http\Controllers\Api;

use App\Services\FileService;
use App\Http\Controllers\Controller;
use App\Models\file;
use App\Models\files_groups;
use App\Models\users_groups;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

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
        if ($file->available == 1) {
            $file->available = 0;
            $file->reserver_id = auth()->user()->id;
        } else {
            $file->reserver_id = null;
            $file->available = 1;
        }
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

    public function downloadFile($id)
    {

        if (!$file = File::find($id)) {
            return response()->json(['error' => 'File not found'], 404);
        }

        $path = storage_path('app/uploads/' . $file->name);

        return response()->download($path, $file->name);
    }

    public function checkin($id)
    {
        if (!$file = File::find($id)) {
            return response()->json(['error' => 'File not found'], 404);
        }

        if ($file->available == 0) return response()->json(['error' => 'File not available']);

        if ($file->creater_id != auth()->user()->id) {

            $users_groups = users_groups::where('user_id', auth()->user()->id)->get(['group_id']);
            $groupIds = $users_groups->pluck('group_id');
            $result = $groupIds->toArray();

            $file_groups = files_groups::where('file_id', $file->id)->get(['group_id']);
            $fileIds = $file_groups->pluck('group_id');
            $result2 = $fileIds->toArray();

            if (empty(array_intersect($result, $result2))) {
                return response()->json(['message' => 'you dont have access to file\'s groups']);
            }
        }
        $file->available = 0;
        $file->reserver_id = auth()->user()->id;
        $file->save();

        $path = storage_path('app/uploads/' . $file->name);

        response()->download($path, $file->name);

        return response()->json([
            'status' => true,
            'message' => 'done successfully'
        ]);
    }

    public function checkout(Request $request)
    {
        $request->validate([
            'file_id' => 'required',
            'file' => 'required|file|mimes:txt|max:10240',
        ]);

        if (!$file = File::find($request->file_id)) {
            return response()->json(['error' => 'File not found'], 404);
        }

        if ($file->reserver_id != auth()->user()->id) return response()->json(['error' => 'you don\'t have access to this file']);

        $file->reserver_id = null;
        $file->available = 1;
        $file->save();

        $file2 = $request->file('file');

        $data = [
            'name' => $file2->getClientOriginalName(),
            'type' => $file2->getClientOriginalExtension(),
            'content' => file_get_contents($file2->getRealPath()),
        ];

        $file->update($data);

        Storage::put('uploads/' . $data['name'], $data['content']);

        $files = file::where('creater_id', auth()->user()->id)->get();
        return response()->json([
            'message' => 'done successfully',
            'file' => $files,
        ]);
    }
}
