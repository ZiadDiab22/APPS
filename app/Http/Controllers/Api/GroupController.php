<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\group;
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
            return response()->json([
                'message' => 'done successfully',
                'group' => $Model,
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
}
