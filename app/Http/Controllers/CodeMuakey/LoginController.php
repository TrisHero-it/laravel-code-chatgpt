<?php

namespace App\Http\Controllers\CodeMuakey;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        return view('code-muakey.tools.login.index');
    }
}
