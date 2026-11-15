<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class KaryMatchSyncController extends Controller
{
    /** Snapshot data untuk jembatan sinkronisasi SPA demo. */
    public function index(): JsonResponse
    {
        $records = DB::table('karymatch_sync_records')->get(['record_key', 'payload']);

        return response()->json([
            'docs' => $records->map(fn ($row) => [
                'id' => $row->record_key,
                'v' => $row->payload,
            ])->values(),
        ])->header('Cache-Control', 'no-store, private');
    }

    /** Simpan satu record/chunk (termasuk 'pw' berisi hash SHA-256, agar login lintas perangkat berjalan seperti HTML asli). */
    public function upsert(Request $request, string $record): JsonResponse
    {
        abort_unless(preg_match('/^[a-zA-Z0-9_~.-]{1,120}$/', $record), 422, 'Kunci data tidak valid.');

        $validated = $request->validate([
            'v' => ['required', 'string', 'max:200000'],
        ]);

        DB::table('karymatch_sync_records')->updateOrInsert(
            ['record_key' => $record],
            ['payload' => $validated['v'], 'updated_at' => now(), 'created_at' => now()],
        );

        return response()->json(['saved' => true]);
    }
}
