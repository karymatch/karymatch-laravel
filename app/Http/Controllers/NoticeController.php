<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class NoticeController extends Controller
{
    /** Daftar notifikasi; yang belum dibaca ditandai, lalu semuanya dianggap terbaca. */
    public function index(Request $request)
    {
        $notices = $request->user()->notices()->latest()->limit(50)->get();

        $request->user()->notices()->whereNull('read_at')->update(['read_at' => now()]);

        return view('seeker.notices', compact('notices'));
    }
}
