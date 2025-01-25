<?php

namespace App\Http\Middleware;

use App\Models\group;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ValidateGroupCreater
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $id = $request->route('id');

        if ($id === null) {
            $id = $request->group_id;
        }

        if (!(group::where('id', $id)->exists())) {
            return response([
                'status' => false,
                'message' => 'not found, wrong id'
            ], 200);
        }

        if (auth()->user()->type_id != 1) {
            if (!(group::where('id', $id)->where('creater_id', auth()->user()->id)->exists())) {
                return response([
                    'status' => false,
                    'message' => 'you dont have access to this group'
                ], 200);
            }
        }
        return $next($request);
    }
}
