<?php


namespace App\Services;

use Illuminate\Support\Facades\Auth;

class UserService
{

  public function checkCredential($password, $email)
  {
    if (!Auth::guard('web')->attempt(['password' => $password, 'email' => $email])) {
      return false;
    }

    $accessToken = auth()->user()->createToken('authToken')->accessToken;
    return $accessToken;
  }
}
