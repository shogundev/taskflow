<?php

namespace App\Http\Controllers;

class VersionController extends Controller
{
    public function __invoke()
    {
        return response()->json(['version' => config('app.version')]);
    }
}
