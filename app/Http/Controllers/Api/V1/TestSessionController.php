<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
// use App\Http\Requests\V1\TestSessionRequest;
use Illuminate\Http\Request;

class TestSessionController extends Controller
{
    public function testSession(Request $request)
    {
        return $request->session()->getId();
        //$request->session()->put('key', 'asd');
        //return $request->session()->get('key');
    }

}
