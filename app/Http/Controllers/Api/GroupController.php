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
        if (!(group::where('id', $id)->exists())) {
            return response([
                'status' => false,
                'message' => 'not found, wrong id'
            ], 200);
        }

        if (!(group::where('id', $id)->where('creater_id', auth()->user()->id)->exists())) {
            return response([
                'status' => false,
                'message' => 'you dont have access to this group'
            ], 200);
        }

        group::where('id', $id)->delete();
        $groups = group::where('creater_id', auth()->user()->id)->get();

        return response([
            'status' => true,
            'message' => "done successfully",
            'groups' => $groups,
        ], 200);
    }

    public function showGroups()
    {
        $groups = group::where('creater_id', auth()->user()->id)->get();

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

        if (!(group::where('creater_id', auth()->user()->id)->where('id', $request->group_id)->exists())) {
            return response()->json([
                'status' => false,
                'message' => "You dont have access to this group"
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

        if (!(group::where('creater_id', auth()->user()->id)->where('id', $request->group_id)->exists())) {
            return response()->json([
                'status' => false,
                'message' => "You dont have access to this group"
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
}
