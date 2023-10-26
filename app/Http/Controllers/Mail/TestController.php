<?php

namespace App\Http\Controllers\Mail;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class TestController extends Controller
{

    public function index(Request $request)
    {
        return view('mail.ageCommunicate.base.blockedClients.blockedClients');
    }
}
