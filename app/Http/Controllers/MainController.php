<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MainController extends Controller
{
    public function index()
    {
        $isAdmin = Auth::check() && Auth::user()->isAdmin();
        return view('help.index', compact('isAdmin'));
    }
}
