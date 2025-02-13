<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\file;
use App\Models\files_groups;
use App\Models\group;
use App\Models\User;
use App\Models\users_groups;
use Illuminate\Http\Request;
use App\Services\GroupService;

class GroupController extends Controller
{
    protected $groupService;

    public function __construct(GroupService $groupService)
    {
        $this->groupService = $groupService;
    }

    public function addGroup(Request $request)
    {
        $Model = $this->groupService->add($request->all());

        if ($Model) {
            $groups = group::where('creater_id', auth()->user()->id)->get();
            return response()->json([
                'message' => 'done successfully',
                'group' => $groups,
            ]);
        }
    }

    public function deleteGroup($id)
    {
        group::where('id', $id)->delete();

        if (auth()->user()->type_id != 1) {
            $groups = group::where('creater_id', auth()->user()->id)->get();
        } else {
            $groups = group::get();
        }

        return response([
            'status' => true,
            'message' => "done successfully",
            'groups' => $groups,
        ], 200);
    }

    public function showGroups()
    {
        if (auth()->user()->type_id == 1) {
            $groups = group::get();
        } else {
            $groups = users_groups::where('user_id', auth()->user()->id)
                ->join('groups as g', 'g.id', 'group_id')
                ->get(['g.id', 'name', 'creater_id', 'access_type_id']);
        }

        return response([
            'status' => true,
            'message' => "done successfully",
            'groups' => $groups,
        ], 200);
    }

    public function addFiletoGroup(Request $request)
    {
        $validatedData = $request->validate([
            'group_id' => 'required',
            'file_id' => 'required',
        ]);

        if (files_groups::where('file_id', $request->file_id)->where('group_id', $request->group_id)->exists()) {
            return response()->json([
                'status' => true,
                'message' => "this file exists in this group already."
            ], 200);
        }

        if (!(file::where('creater_id', auth()->user()->id)->where('id', $request->file_id)->exists())) {
            return response()->json([
                'status' => false,
                'message' => "You dont have access to this file"
            ], 200);
        }

        files_groups::create($validatedData);

        $files = files_groups::where('group_id', $request->group_id)
            ->join('files', 'files.id', 'file_id')
            ->join('groups', 'groups.id', 'group_id')
            ->get([
                'file_id',
                'group_id',
                'groups.name as group_name',
                'files.name as file_name',
                'content',
                'files.type',
                'files.created_at',
                'files.updated_at'
            ]);

        return response()->json([
            'status' => true,
            'message' => 'done successfull',
            'group_files' => $files
        ]);
    }

    public function addMembertoGroup(Request $request)
    {
        $validatedData = $request->validate([
            'group_id' => 'required',
            'user_id' => 'required',
        ]);

        if (users_groups::where('user_id', $request->user_id)->where('group_id', $request->group_id)->exists()) {
            return response()->json([
                'status' => true,
                'message' => "this user exists in this group already."
            ], 200);
        }

        if (!(User::where('id', $request->user_id)->exists())) {
            return response()->json([
                'status' => false,
                'message' => "wrong id , user dont exist"
            ], 200);
        }

        users_groups::create($validatedData);

        $users = users_groups::where('group_id', $request->group_id)
            ->join('users', 'users.id', 'user_id')
            ->join('groups', 'groups.id', 'group_id')
            ->get([
                'user_id',
                'group_id',
                'groups.name as group_name',
                'users.name as user_name',
                'users.email'
            ]);

        return response()->json([
            'status' => true,
            'message' => 'done successfull',
            'group_files' => $users
        ]);
    }

    public function showGroupFiles($id)
    {
        if (!(users_groups::where('group_id', $id)->where('user_id', auth()->user()->id)->exists())) {
            return response()->json([
                'status' => false,
                'message' => "You dont have access to this group."
            ]);
        }

        $files = files_groups::where('group_id', $id)
            ->join('files', 'files.id', 'file_id')
            ->get([
                'file_id',
                'files.creater_id as file_creater_id',
                'files.name as file_name',
                'content',
                'files.type',
                'available',
                'reserver_id',
                'files.type',
                'files.created_at',
                'files.updated_at'
            ]);

        if (group::where('id', $id)->where('creater_id', auth()->user()->id)->exists()) {
            $users = users_groups::where('group_id', $id)
                ->join('users as u', 'u.id', 'user_id')->get([
                    'u.id',
                    'u.name',
                    'email',
                    'u.created_at',
                    'u.updated_at'
                ]);
            $allUsers = User::get();
            $myFiles = file::where('creater_id', auth()->user()->id)->get();
            $groupInfo = group::where('id', $id)->get();
            return response([
                'status' => true,
                'message' => "done successfully",
                'files' => $files,
                'myFiles' => $myFiles,
                'users' => $users,
                'allUsers' => $allUsers,
                'groupInfo' => $groupInfo
            ], 200);
        }

        return response([
            'status' => true,
            'message' => "done successfully",
            'files' => $files,
        ], 200);
    }
}
