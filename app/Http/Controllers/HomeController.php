<?php

namespace App\Http\Controllers;

use App\Language;
use App\User;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index(): Renderable
    {
        return view('layouts.cms');
    }

    public function adminInfo(): JsonResponse
    {
        return response()->json(['success', User::where('id', auth()->id())->with('role')->first()]);
    }

    public function allLanguages(): JsonResponse
    {
        return response()->json(['success', Language::all()]);
    }
}
