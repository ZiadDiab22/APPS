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
    Route::get("showGroupFiles/{id}", [GroupController::class, "showGroupFiles"]);
    Route::get("showFiles", [FileController::class, "showFiles"]);
    Route::get("togglefreeFile/{id}/", [FileController::class, "togglefreeFile"]);
    Route::post("addFiletoGroup", [GroupController::class, "addFiletoGroup"]);
    Route::post("addMembertoGroup", [GroupController::class, "addMembertoGroup"]);
    Route::get("showUsers", [UserController::class, "showUsers"]);
    Route::post("checkin", [FileController::class, "checkin"]);
    Route::post("checkout", [FileController::class, "checkout"]);
});
Route::get("downloadFile/{id}/", [FileController::class, "downloadFile"]);
