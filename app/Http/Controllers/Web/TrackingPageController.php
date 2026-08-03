<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class TrackingPageController extends Controller
{
    public function show(string $token): View
    {
        return view('tracking.show', ['token' => $token]);
    }
}
