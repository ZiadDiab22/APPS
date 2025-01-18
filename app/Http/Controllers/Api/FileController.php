<?php

namespace App\Http\Controllers\Api;

use App\Services\FileService;
use App\Services\NotificationService;
use App\Http\Controllers\Controller;
use App\Models\add_file_request;
use App\Models\file;
use App\Models\files_groups;
use App\Models\group;
use App\Models\users_groups;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class FileController extends Controller
{
    protected $fileService;
    protected $notificationService;

    public function __construct(FileService $fileService, NotificationService $notificationService)
    {
        $this->fileService = $fileService;
        $this->notificationService = $notificationService;
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
                'message' => 'not found, wrong file id'
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

        response()->download($path, $file->name);
        return response()->json(['status' => true, 'message' => $path]);
    }

    public function checkin(Request $request)
    {
        foreach ($request->ids as $id) {
            if (!$file = File::find($id)) {
                return response()->json(['error' => "File {$id} not found"], 404);
            }

            if ($file->available == 0) return response()->json(['error' => "File {$id} not available"]);

            if ($file->creater_id != auth()->user()->id) {

                $users_groups = users_groups::where('user_id', auth()->user()->id)->get(['group_id']);
                $groupIds = $users_groups->pluck('group_id');
                $result = $groupIds->toArray();

                $file_groups = files_groups::where('file_id', $file->id)->get(['group_id']);
                $fileIds = $file_groups->pluck('group_id');
                $result2 = $fileIds->toArray();

                if (empty(array_intersect($result, $result2))) {
                    return response()->json(['message' => "you dont have access to file {$id} groups"]);
                }
            }
        }

        $paths = [];

        foreach ($request->ids as $id) {
            $file = File::find($id);
            DB::transaction(function () use ($file) {
                $file->available = 0;
                $file->reserver_id = auth()->user()->id;
                $file->save();
            });
            $this->notificationService->sendNotification($id, auth()->user()->id, auth()->user()->name, $file->name, false);

            $path = storage_path('app/uploads/' . $file->name);

            response()->download($path, $file->name);
            $paths[] = $path;
        }

        return response()->json([
            'status' => true,
            'message' => 'done successfully',
            'paths' => $paths
        ]);
    }

    public function checkout(Request $request)
    {
        $request->validate([
            'file_id' => 'required',
            'file' => 'required|file|mimes:txt|max:10240',
        ]);

        if (!$file = File::find($request->file_id)) {
            return response()->json([
                'status' => false,
                'error' => 'File not found'
            ], 404);
        }

        if ($file->reserver_id != auth()->user()->id) return response()->json([
            'status' => false,
            'error' => 'you don\'t have access to this file'
        ]);

        $file2 = $request->file('file');
        if ($file->name != $file2->getClientOriginalName())
            return response()->json([
                'status' => false,
                'error' => 'The modified file must have the same name and extension as the original file.'
            ]);

        DB::transaction(function () use ($file) {
            $file->reserver_id = null;
            $file->available = 1;
            $file->save();
        });


        $this->notificationService->sendNotification($file->id, auth()->user()->id, auth()->user()->name, $file->name, true);

        $data = [
            'content' => file_get_contents($file2->getRealPath()),
        ];

        $file->update($data);

        Storage::put('uploads/' . $file->name, $data['content']);

        $files = file::where('creater_id', auth()->user()->id)->get();
        return response()->json([
            'status' => true,
            'message' => 'done successfully',
            'file' => $files,
        ]);
    }

    public function addFileRequest(Request $request)
    {
        $request->validate([
            'group_id' => 'required',
            'file' => 'required|file|mimes:txt|max:10240',
        ]);

        if (group::where('creater_id', auth()->user()->id)->where('id', $request->group_id)->exists()) {
            return response()->json([
                'status' => false,
                'message' => "You are an admin in this group and you dont need request to add files."
            ], 200);
        }

        if (!(users_groups::where('user_id', auth()->user()->id)->where('group_id', $request->group_id)->exists())) {
            return response()->json([
                'status' => false,
                'message' => "You dont have access to this group"
            ], 200);
        }

        $file = $request->file('file');
        $f = add_file_request::create([
            "user_id" => auth()->user()->id,
            "group_id" => $request->group_id,
            "name" => $file->getClientOriginalName(),
            "type" => $file->getClientOriginalExtension(),
            "content" => file_get_contents($file->getRealPath()),
        ]);

        Storage::put('uploads/' . $f->name, $f->content);

        $files = add_file_request::where('user_id', auth()->user()->id)->get();
        return response()->json([
            'message' => 'done successfully',
            'files' => $files,
        ]);
    }

    public function showFileRequests($id)
    {
        if (!(group::where('creater_id', auth()->user()->id)->where('id', $id)->exists())) {
            return response()->json([
                'status' => false,
                'message' => "You dont have access to this group info."
            ], 200);
        }

        $files = add_file_request::where('group_id', $id)->get();

        foreach ($files as $f) {
            $f['path'] = storage_path('app/uploads/' . $f['name']);
        }

        return response()->json([
            'message' => 'done successfully',
            'files' => $files,
        ]);
    }

    public function acceptFileRequest($id)
    {
        if (!$req = add_file_request::find($id)) {
            return response()->json([
                'status' => false,
                'error' => 'Request not found , Wrong id.'
            ], 404);
        }

        if (!(group::where('creater_id', auth()->user()->id)->where('id', $req->group_id)->exists())) {
            return response()->json([
                'status' => false,
                'message' => "You dont have access to group info."
            ], 200);
        }

        $req->accepted = 1;
        $req->save();

        file::create([
            "name" => $req->name,
            "content" => $req->content,
            "type" => $req->type,
            "creater_id" => $req->user_id,
        ]);

        $files = add_file_request::where('group_id', $req->group_id)->get();

        foreach ($files as $f) {
            $f['path'] = storage_path('app/uploads/' . $f['name']);
        }

        return response()->json([
            'message' => 'done successfully',
            'files' => $files,
        ]);
    }

    public function deleteFileRequest($id)
    {
        if (!$req = add_file_request::find($id)) {
            return response()->json([
                'status' => false,
                'error' => 'Request not found , Wrong id.'
            ], 404);
        }

        if (!(group::where('creater_id', auth()->user()->id)->where('id', $req->group_id)->exists())) {
            return response()->json([
                'status' => false,
                'message' => "You dont have access to group info."
            ], 200);
        }

        if ($req->accepted == 0) Storage::delete('uploads/' . $req->name);

        add_file_request::where('id', $id)->delete();

        $files = add_file_request::where('group_id', $req->group_id)->get();

        foreach ($files as $f) {
            $f['path'] = storage_path('app/uploads/' . $f['name']);
        }

        return response()->json([
            'message' => 'done successfully',
            'files' => $files,
        ]);
    }
}
