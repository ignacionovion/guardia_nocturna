<?php

namespace App\Http\Controllers;

use App\Models\Bed;
use Illuminate\Http\Request;

class QrBedController extends Controller
{
    public function show($token)
    {
        $bed = Bed::where('qr_token', $token)->firstOrFail();

        return view('qr.bed', compact('bed'));
    }
}
