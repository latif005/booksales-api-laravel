<?php

namespace App\Http\Controllers;

use App\Models\ContactMessage;
use Illuminate\Http\Request;

class ContactMessageController extends Controller
{

    // Menampilkan semua pesan kepada Admin
    public function index()
    {
        $messages = ContactMessage::with('user')
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Data pesan berhasil diambil',
            'data' => $messages
        ]);
    }

// Menyimpan pesan dari pengguna
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'subject' => 'required|string|max:255',
            'message' => 'required|string',
        ]);

        $contactMessage = ContactMessage::create([
            'user_id' => auth()->id(),
            'name' => $validated['name'],
            'email' => $validated['email'],
            'subject' => $validated['subject'],
            'message' => $validated['message'],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Pesan berhasil dikirim',
            'data' => $contactMessage
        ], 201);
    }
}
