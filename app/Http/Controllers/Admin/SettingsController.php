<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Settings;

class SettingsController extends Controller
{
    public function index()
    {
        return response()->json(Settings::all());
    }

    public function update(Request $request, $key)
    {
        $setting = Settings::where('key',$key)->firstOrFail();
        $setting->value = $request->input('value');
        $setting->save();
        return response()->json($setting);
    }
}
