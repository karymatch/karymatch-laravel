<?php

namespace App\Http\Controllers;

use App\Models\JobPost;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class HomeController extends Controller
{
    public const TYPES = ['Full-time', 'Part-time', 'Kontrak', 'Magang', 'Freelance'];

    public function index(Request $request)
    {
        if (! $request->user()) {
            return view('home');
        }

        $q     = trim((string) $request->query('q'));
        $city  = trim((string) $request->query('kota'));
        $type  = (string) $request->query('tipe');
        $match = $request->boolean('cocok');

        $user     = $request->user();
        $isSeeker = $user && $user->role === 'pekerja';
        $cv       = $isSeeker ? $user->cvOrNew() : null;

        $query = JobPost::live()
            ->join('companies', 'companies.id', '=', 'job_posts.company_id')
            ->select('job_posts.*')
            ->with('company')
            ->when($q, fn ($query) => $query->where(function ($w) use ($q) {
                $w->where('job_posts.title', 'like', "%{$q}%")
                  ->orWhere('companies.name', 'like', "%{$q}%")
                  ->orWhere('job_posts.skills', 'like', "%{$q}%");
            }))
            ->when($city, fn ($query) => $query->where('job_posts.city', 'like', "%{$city}%"))
            ->when(in_array($type, self::TYPES, true), fn ($query) => $query->where('job_posts.type', $type))
            // Urutan: paket Pro aktif, lalu Bisnis aktif, lalu Gratis; di dalamnya yang terbaru dulu
            ->orderByRaw("CASE WHEN companies.plan_ends_at > NOW()
                               THEN (CASE companies.plan_slug WHEN 'pro' THEN 0 WHEN 'bisnis' THEN 1 ELSE 2 END)
                               ELSE 2 END")
            ->orderByDesc('job_posts.posted_at');

        if ($match && $cv && $cv->skills) {
            // "Cocok dengan skill saya": skor minimal 40%, yang paling cocok di atas
            $scored = $query->limit(300)->get()
                ->map(fn ($p) => [$p, $p->matchScore($cv)])
                ->filter(fn ($x) => $x[1] >= 40)
                ->sortByDesc(fn ($x) => $x[1])
                ->pluck(0)->values();

            $page  = max(1, (int) $request->query('page', 1));
            $posts = new LengthAwarePaginator(
                $scored->forPage($page, 12)->values(), $scored->count(), 12, $page,
                ['path' => $request->url(), 'query' => $request->query()]
            );
        } else {
            $posts = $query->paginate(12)->withQueryString();
        }

        return view('home', [
            'posts'      => $posts,
            'q'          => $q,
            'city'       => $city,
            'type'       => $type,
            'match'      => $match,
            'cv'         => $cv,
            'isSeeker'   => $isSeeker,
            'savedIds'   => $isSeeker ? $user->savedJobs()->pluck('job_post_id')->all() : [],
            'appliedIds' => $isSeeker ? $user->applications()->pluck('job_post_id')->all() : [],
        ]);
    }

    public function show(Request $request, JobPost $post)
    {
        abort_unless($post->isLive(), 404);
        $post->load('company', 'questions');

        $user     = $request->user();
        $isSeeker = $user && $user->role === 'pekerja';
        $cv       = $isSeeker ? $user->cvOrNew() : null;

        return view('jobs.show', [
            'post'     => $post,
            'isSeeker' => $isSeeker,
            'cv'       => $cv,
            'score'    => $post->matchScore($cv),
            'applied'  => $isSeeker ? $user->applications()->where('job_post_id', $post->id)->first() : null,
            'saved'    => $isSeeker ? $user->savedJobs()->where('job_post_id', $post->id)->exists() : false,
        ]);
    }
}
