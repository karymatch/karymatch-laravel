<?php

namespace App\Http\Controllers;

use App\Models\Application;
use App\Models\JobPost;
use App\Models\Notice;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\Request;

class ApplicationController extends Controller
{
    /** Riwayat lamaran: semua tersimpan, termasuk yang ditarik atau ditolak, walau lowongan sudah ditutup. */
    public function index(Request $request)
    {
        $apps = $request->user()->applications()
            ->with('jobPost.company')
            ->latest()
            ->get();

        return view('seeker.applications', compact('apps'));
    }

    public function store(Request $request, JobPost $post)
    {
        $user = $request->user();
        $post->load('company', 'questions');

        if (! $post->isLive()) {
            return back()->with('error', 'Lowongan ini sudah tidak tayang.');
        }

        $cv = $user->cvOrNew();
        if (! $cv->readyToApply()) {
            return redirect()->route('cv.edit')
                ->with('error', 'Lengkapi CV dulu sebelum melamar. Masih kurang: '.implode(', ', $cv->missingForApply()).'.');
        }

        if ($user->applications()->where('job_post_id', $post->id)->exists()) {
            return redirect()->route('applications.index')->with('error', 'Kamu sudah melamar lowongan ini (satu lamaran per lowongan).');
        }

        // satu jawaban wajib untuk setiap pertanyaan screening
        $rules = [];
        foreach ($post->questions as $q) {
            $rules["answers.{$q->id}"] = ['required', 'string', 'max:500'];
        }
        $validated = $request->validate($rules, ['required' => 'Jawab semua pertanyaan screening dulu.']);

        $answers = $post->questions->map(fn ($q) => [
            'q' => $q->question,
            'a' => trim($validated['answers'][$q->id]),
        ])->values()->all();

        try {
            $user->applications()->create([
                'job_post_id'       => $post->id,
                'status'            => 'baru',
                'screening_answers' => $answers,
                'cv_snapshot'       => $cv->only([
                    'summary', 'phone', 'city', 'link', 'skills', 'education', 'experience',
                    'projects', 'certificates', 'languages', 'pref_salary', 'pref_location', 'pref_type',
                ]) + ['name' => $user->name, 'email' => $user->email],
                'events'            => [['s' => 'baru', 't' => now()->toDateTimeString()]],
            ]);
        } catch (UniqueConstraintViolationException) {
            return redirect()->route('applications.index')->with('error', 'Kamu sudah melamar lowongan ini.');
        }

        Notice::create([
            'user_id' => $post->company->user_id,
            'message' => "Pelamar baru untuk {$post->title}: {$user->name}",
        ]);

        return redirect()->route('applications.index')->with('ok', 'Lamaran terkirim. CV tersimpan sebagai salinan.');
    }

    public function withdraw(Request $request, Application $application)
    {
        abort_unless($application->user_id === $request->user()->id, 403);

        if (! $application->canWithdraw()) {
            $why = match ($application->status) {
                'ditinjau', 'screening' => 'Lamaran sedang ditinjau',
                'lolos'                 => 'Lamaran sudah lolos berkas',
                'wawancara'             => 'Lamaran sudah masuk tahap interview',
                'penawaran'             => 'Kamu sudah menerima penawaran kerja',
                'ditarik'               => 'Lamaran sudah ditarik',
                default                 => 'Lamaran sudah diproses',
            };

            return back()->with('error', $why.', tidak dapat menarik lamaran.');
        }

        $application->moveTo('ditarik');

        $post = $application->jobPost()->with('company')->first();
        Notice::create([
            'user_id' => $post->company->user_id,
            'message' => "{$request->user()->name} menarik lamaran untuk {$post->title}",
        ]);

        return back()->with('ok', 'Lamaran ditarik.');
    }

    /** CV persis seperti saat dikirim (bukan CV terbaru). */
    public function cv(Request $request, Application $application)
    {
        abort_unless($application->user_id === $request->user()->id, 403);
        $application->load('jobPost.company');

        return view('seeker.application-cv', ['app' => $application, 'cv' => $application->cv_snapshot ?? []]);
    }
}
