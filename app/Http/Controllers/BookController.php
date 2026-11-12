<?php

namespace App\Http\Controllers;

use App\Models\Book;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class BookController extends Controller
{
    // Menampilkan semua data buku

    // Menampilkan semua data buku dan pencarian buku
    public function index(Request $request)
    {
        $query = Book::with(['genre', 'author']);

        // Pencarian berdasarkan judul buku
        if ($request->filled('search')) {
            $query->where('title', 'like', '%' . $request->search . '%');
        }

        $books = $query->latest()->get();

        return response()->json([
            'success' => true,
            'message' => 'Data buku berhasil diambil',
            'data' => $books
        ]);
    }


    // Menambahkan buku
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string',
            'description' => 'nullable|string',
            'price' => 'required|numeric',
            'stock' => 'required|integer',
            'cover_photo' => 'required|image|mimes:jpeg,png,jpg,webp|max:2048',
            'genre_id' => 'required|exists:genres,id',
            'author_id' => 'required|exists:authors,id'
        ]);

        // Menyimpan gambar ke storage
        $coverPhoto = $request->file('cover_photo')->store('books', 'public');

        // Menyimpan data buku
        $book = Book::create([
            'title' => $request->title,
            'description' => $request->description,
            'price' => $request->price,
            'stock' => $request->stock,
            'cover_photo' => $coverPhoto,
            'genre_id' => $request->genre_id,
            'author_id' => $request->author_id
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Buku berhasil ditambahkan',
            'data' => $book
        ], 201);
    }

    // Mengubah data buku
    public function update(Request $request, $id)
    {
        $book = Book::find($id);

        if (!$book) {
            return response()->json([
                'message' => 'Book not found'
            ], 404);
        }

        $validated = $request->validate([
            'title' => 'required|string',
            'description' => 'nullable|string',
            'price' => 'required|numeric',
            'stock' => 'required|integer',
            'cover_photo' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'genre_id' => 'required|exists:genres,id',
            'author_id' => 'required|exists:authors,id'
        ]);

        // Jika ada cover foto baru
        if ($request->hasFile('cover_photo')) {

            // Hapus cover foto lama
            if ($book->cover_photo) {
                Storage::disk('public')->delete($book->cover_photo);
            }

            // Simpan cover foto baru
            $validated['cover_photo'] = $request->file('cover_photo')
                ->store('books', 'public');
        }

        // Update data buku
        $book->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Buku berhasil diperbarui',
            'data' => $book
        ]);
    }

    public function destroy($id)
    {
        $book = Book::find($id);

        if (!$book) {
            return response()->json([
                'message' => 'Book not found'
            ], 404);
        }

        $book->delete();

        return response()->json([
            'message' => 'Book deleted successfully'
        ]);
    }

    public function show($id)
    {
        $book = Book::with(['genre', 'author'])->find($id);

        if (!$book) {
            return response()->json([
                'message' => 'Book not found'
            ], 404);
        }

        return response()->json($book);
    }
}
