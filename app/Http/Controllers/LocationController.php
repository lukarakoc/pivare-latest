<?php

namespace App\Http\Controllers;

use App\Location;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LocationController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function getAllLocations(): JsonResponse
    {
        return response()->json(['success', Location::all()]);
    }
}
