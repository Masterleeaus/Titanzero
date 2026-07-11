<?php

namespace Modules\TitanZero\Http\Controllers\SuperAdmin;

use Illuminate\Routing\Controller;

class PersonasController extends Controller
{
    public function index()
    {
        return view('titanzero::super-admin.personas.index');
    }
}
