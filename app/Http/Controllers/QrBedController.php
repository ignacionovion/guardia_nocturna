<?php

namespace App\Http\Controllers;

use App\Models\Bed;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class QrBedController extends Controller
{
    public function show(Request $request, string $token)
    {
        $token = $request->route('token') ?? $token;

        $bed = Bed::where('qr_token', $token)->firstOrFail();

        return view('qr.bed', compact('bed'));
    }
}
