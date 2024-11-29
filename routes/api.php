<?php

use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\FileController;
use App\Http\Controllers\Api\GroupController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post("register", [UserController::class, "register"]);
Route::post("login", [UserController::class, "login"])->middleware('validate.login.inputs');

Route::group(["middleware" => ["auth:api"]], function () {
    Route::post("addFile", [FileController::class, "addFile"])->middleware('validate.file.inputs');
    Route::post("addGroup", [GroupController::class, "addGroup"])->middleware('validate.group.inputs');
    Route::get("deleteGroup/{id}", [GroupController::class, "deleteGroup"]);
    Route::get("deleteFile/{id}", [FileController::class, "deleteFile"]);
    Route::get("showGroups", [GroupController::class, "showGroups"]);
    Route::get("showFiles", [FileController::class, "showFiles"]);
    Route::get("togglefreeFile/{id}/", [FileController::class, "togglefreeFile"]);
});
