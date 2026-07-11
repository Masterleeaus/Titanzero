<?php

namespace Modules\TitanZero\Http\Controllers\SuperAdmin;

use Illuminate\Routing\Controller;

class WorkflowsController extends Controller
{
    public function index()
    {
        return view('titanzero::super-admin.workflows.index');
    }
}
