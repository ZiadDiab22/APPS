<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\notification;
use App\Services\UserService;
use App\Services\FileService;
use App\Models\User;
use Illuminate\Http\Request;
use App\Models\file;
use App\Models\files_groups;
use App\Models\group;
use App\Models\report;
use App\Models\users_groups;
use Pdf;

class UserController extends Controller
{
    protected $userService;
    protected $fileService;

    public function __construct(UserService $userService, FileService $fileService)
    {
        $this->userService = $userService;
        $this->fileService = $fileService;
    }

    public function register(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required',
            'email' => 'email|required',
            'password' => 'required',
        ]);

        if (User::where('email', $request->email)->exists()) {
            return response()->json([
                'status' => false,
                'message' => "email is taken"
            ], 200);
        }

        $validatedData['password'] = bcrypt($request->password);
        $validatedData['type_id'] = 2;

        $user = User::create($validatedData);

        $accessToken = $user->createToken('authToken')->accessToken;

        $user_data = User::where('id', $user->id)->first();

        return response()->json([
            'status' => true,
            'access_token' => $accessToken,
            'user_data' => $user_data
        ]);
    }

    public function login(Request $request)
    {
        $accessToken = $this->userService->checkCredential($request->password, $request->email);

        if ($accessToken) {
            $user_data = User::where('id', auth()->user()->id)->first();
            return response()->json([
                'status' => true,
                'access_token' => $accessToken,
                'user_data' => $user_data
            ]);
        } else {
            return response()->json([
                'status' => false,
                'message' => 'something went error',
            ]);
        }
    }

    public function showUsers()
    {
        $users = User::where('id', '!=', auth()->user()->id)->get(['id', 'name', 'email']);

        return response()->json([
            'status' => true,
            'users' => $users
        ]);
    }

    public function showNotification()
    {
        notification::where('user_id', auth()->user()->id)->update(['viewed' => 1]);
        $data = notification::where('user_id', auth()->user()->id)->get();

        return response()->json([
            'status' => true,
            'users' => $data
        ]);
    }

    public function showFileReport(Request $request)
    {
        $request->validate([
            'group_id' => 'required',
            'file_id' => 'required',
        ]);

        if (!$file = File::find($request->file_id)) {
            return response()->json([
                'status' => false,
                'error' => "File not found"
            ], 404);
        }

        if ($file->creater_id != auth()->user()->id) {

            $users_groups = users_groups::where('user_id', auth()->user()->id)->get(['group_id']);
            $groupIds = $users_groups->pluck('group_id');
            $result = $groupIds->toArray();

            $file_groups = files_groups::where('file_id', $file->id)->get(['group_id']);
            $fileIds = $file_groups->pluck('group_id');
            $result2 = $fileIds->toArray();

            if (empty(array_intersect($result, $result2))) {
                return response()->json([
                    'status' => false,
                    'message' => "you dont have access to file groups"
                ]);
            }
        }

        $users_groups = users_groups::where('group_id', $request->group_id)->get(['user_id']);
        $users = $users_groups->pluck('user_id');
        $users = $users->toArray();

        $data = Report::where('file_id', $request->file_id)
            ->whereIn('user_id', $users)
            ->join('users as u', 'u.id', 'user_id')
            ->join('files as f', 'f.id', 'file_id')
            ->get([
                'reports.id',
                'user_id',
                'u.name as user_name',
                'u.email as user_email',
                'file_id',
                'f.name as file_name',
                'f.type',
                'content',
                'old_content',
                'operation',
                'reports.created_at',
                'reports.updated_at'
            ]);

        if ($request->CSV) {
            $this->fileService->exportCSV($data);
        }

        if ($request->PDF) {
            $array = [
                'title' => 'The following table shows the modifications made to the files, the type of modification and who made it.',
                'date' => date('d M Y'),
                'data' => $data
            ];

            $pdf = Pdf::loadView('pdf', $array);
            return $pdf->download('report.pdf');
        }

        return response()->json([
            'status' => true,
            'data' => $data
        ]);
    }

    public function showUsersReport(Request $request)
    {
        $request->validate([
            'group_id' => 'required',
        ]);

        if (!$group = group::find($request->group_id)) {
            return response()->json([
                'status' => false,
                'error' => "group not found"
            ], 404);
        }

        if ($group->creater_id != auth()->user()->id) {
            return response()->json([
                'status' => false,
                'message' => "only group creater can do this process"
            ]);
        }

        $users_groups = users_groups::where('group_id', $request->group_id)->get(['user_id']);
        $users = $users_groups->pluck('user_id');
        $users = $users->toArray();

        $files_groups = files_groups::where('group_id', $request->group_id)->get(['file_id']);
        $files = $files_groups->pluck('file_id');
        $files = $files->toArray();


        $data = Report::whereIn('file_id', $files)
            ->whereIn('user_id', $users)
            ->join('users as u', 'u.id', 'user_id')
            ->join('files as f', 'f.id', 'file_id')
            ->get([
                'reports.id',
                'user_id',
                'u.name as user_name',
                'u.email as user_email',
                'file_id',
                'f.name as file_name',
                'f.type',
                'content',
                'old_content',
                'operation',
                'reports.created_at',
                'reports.updated_at'
            ]);

        if ($request->CSV) {
            $this->fileService->exportCSV($data);
        }
        if ($request->PDF) {
            $array = [
                'title' => 'The following table shows the modifications made to the files, the type of modification and who made it.',
                'date' => date('d M Y'),
                'data' => $data
            ];

            $pdf = Pdf::loadView('pdf', $array);
            return $pdf->download('report.pdf');
        }

        return response()->json([
            'status' => true,
            'data' => $data
        ]);
    }

    public function deleteUser($id)
    {
        if (auth()->user()->type_id != 1) {
            return response([
                'status' => false,
                'message' => 'not authorized'
            ], 200);
        }

        if (!(User::where('id', $id)->exists())) {
            return response([
                'status' => false,
                'message' => 'not found, wrong id'
            ], 200);
        }

        User::where('id', $id)->delete();

        $users = User::get();

        return response([
            'status' => true,
            'message' => "done successfully",
            'groups' => $users,
        ], 200);
    }

    public function export()
    {
        // $filename = "apps_database_" . substr(str_replace('.', '', microtime(true)), -4) . ".csv";
        // header('Content-Type: text/csv');
        // header('Content-Disposition: attachment; filename="' . $filename . '"');
        // header('Content-Type: text/html; charset=utf-8');
        // $output = fopen('php://output', 'w');
        // fputs($output, "\xEF\xBB\xBF");

        $data = Report::join('users as u', 'u.id', 'user_id')
            ->join('files as f', 'f.id', 'file_id')
            ->get([
                'reports.id',
                'user_id',
                'u.name as user_name',
                'u.email as user_email',
                'file_id',
                'f.name as file_name',
                'f.type',
                'content',
                'old_content',
                'operation',
                'reports.created_at',
                'reports.updated_at'
            ]);

        // fputcsv($output, ['Reports'], ';');
        // fputcsv($output, [], ';');

        // $array = json_decode($data, true);
        // $cols = array_keys($array[0]);

        // fputcsv($output, $cols, ';');

        // foreach ($data as $rec) {
        //     $record = [];
        //     $array = json_decode($rec, true);
        //     $record = array_values($array);
        //     fputcsv($output, $record, ';');
        // }

        // fclose($output);
        // exit;
    }
}
