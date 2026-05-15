<?php

namespace App\Http\Controllers\CodeMuakey;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class IndexController extends Controller
{
    public function index(Request $request)
    {
        $email = $request->query('email');
        if ($email != null && !$this->validateEmail($email)) {
            abort(400, 'Invalid email address');
        }

        return view('code-muakey.index', compact('email'));
    }

    private function validateEmail($email)
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL);
    }
}
