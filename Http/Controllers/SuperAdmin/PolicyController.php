<?php

namespace Modules\TitanZero\Http\Controllers\SuperAdmin;

use Illuminate\Routing\Controller;

class PolicyController extends Controller
{
    public function index()
    {
        return view('titanzero::super-admin.policy.index');
    }
}
