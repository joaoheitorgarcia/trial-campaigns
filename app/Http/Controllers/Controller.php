<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

abstract class Controller
{
    protected function perPage(Request $request, int $default = 15, int $max = 100): int
    {
        return max(1, min($request->integer('per_page', $default), $max));
    }
}
