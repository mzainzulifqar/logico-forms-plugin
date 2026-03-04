<?php

namespace Logicoforms\Forms\Http\Controllers;

use Illuminate\Routing\Controller;
use Logicoforms\Forms\Models\FormThemePreset;

class FormThemePresetController extends Controller
{
    public function index()
    {
        return response()->json([
            'data' => FormThemePreset::all(),
        ]);
    }
}
