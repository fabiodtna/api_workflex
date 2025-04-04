<?php

namespace App\Http\Controllers;

use App\Models\Feedback;
use Illuminate\Http\Request;

class FeedbackController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email',
            'message' => 'required|string'
        ]);

        Feedback::create([
            'name' => $request->name,
            'email' => $request->email,
            'message' => $request->message
        ]);

        return response()->json(['message' => 'Feedback enviado com sucesso!'], 201);
    }

    public function index()
    {
        return response()->json(Feedback::all(), 200);
    }
}
