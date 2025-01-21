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
    Route::get("deleteUser/{id}", [UserController::class, "deleteUser"]);
    Route::get("showGroups", [GroupController::class, "showGroups"]);
    Route::get("showGroupFiles/{id}", [GroupController::class, "showGroupFiles"]);
    Route::get("showFiles", [FileController::class, "showFiles"]);
    Route::get("togglefreeFile/{id}/", [FileController::class, "togglefreeFile"]);
    Route::post("addFiletoGroup", [GroupController::class, "addFiletoGroup"]);
    Route::post("addMembertoGroup", [GroupController::class, "addMembertoGroup"]);
    Route::get("showUsers", [UserController::class, "showUsers"]);
    Route::post("checkin", [FileController::class, "checkin"]);
    Route::post("checkout", [FileController::class, "checkout"]);
    Route::get("showNotification", [UserController::class, "showNotification"]);
    Route::post("showFileReport", [UserController::class, "showFileReport"]);
    Route::post("showUsersReport", [UserController::class, "showUsersReport"]);
    Route::post("showGroupReport", [UserController::class, "showGroupReport"]);
    Route::post("addFileRequest", [FileController::class, "addFileRequest"]);
    Route::get("showFileRequests/{id}", [FileController::class, "showFileRequests"]);
    Route::get("acceptFileRequest/{id}", [FileController::class, "acceptFileRequest"]);
    Route::get("deleteFileRequest/{id}", [FileController::class, "deleteFileRequest"]);
});
Route::get("downloadFile/{id}/", [FileController::class, "downloadFile"]);
Route::get("export", [UserController::class, "export"]);
