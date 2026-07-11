<?php

namespace Modules\TitanZero\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\TitanZero\Entities\TitanZeroCoach;

class CoachAdminController extends Controller
{
    public function index()
    {
        $coaches = TitanZeroCoach::query()->orderBy('id')->get();
        return view('titanzero::admin.coaches.index', compact('coaches'));
    }

    public function edit(int $id)
    {
        $coach = TitanZeroCoach::query()->findOrFail($id);
        return view('titanzero::admin.coaches.edit', compact('coach'));
    }

    public function update(int $id, Request $request)
    {
        $coach = TitanZeroCoach::query()->findOrFail($id);
        $coach->name = (string)$request->input('name', $coach->name);
        $coach->description = (string)$request->input('description', $coach->description);
        $coach->is_enabled = (bool)$request->input('is_enabled', true);

        $rulesJson = (string)$request->input('rules_json', '');
        if ($rulesJson !== '') {
            $decoded = json_decode($rulesJson, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $coach->rules = $decoded;
            }
        }

        $coach->save();
        return redirect()->route('dashboard.admin.titanzero.coaches.index')->with('status', 'Coach updated.');
    }
}
