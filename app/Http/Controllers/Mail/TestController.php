<?php

namespace App\Http\Controllers\Mail;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;

class TestController extends Controller
{

    public function index(Request $request)
    {
        $date = Carbon::now();

        return view('mail.test')->with(['data' => $date]);
    }
}
