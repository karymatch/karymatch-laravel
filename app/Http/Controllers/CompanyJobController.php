<?php

namespace App\Http\Controllers;

use App\Models\JobPost;
use App\Services\PlanAlerts;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class CompanyJobController extends Controller
{
    public function dashboard(Request $request)
    {
        $company = $request->user()->company;
        $posts   = $company->jobPosts()->orderByDesc('posted_at')->get();

        return view('company.dashboard', [
            'company' => $company,
            'plan'    => $company->effectivePlan(),
            'posts'   => $posts,
            'alerts'  => PlanAlerts::for($company),
            'live'    => $company->liveAdsCount(),
        ]);
    }

    public function create(Request $request)
    {
        $company = $request->user()->company;

        return view('company.job-form', ['post' => null, 'plan' => $company->effectivePlan(), 'company' => $company]);
    }

    public function store(Request $request)
    {
        $company = $request->user()->company;

        if (! $company->isApproved()) {
            return redirect()->route('company.dashboard')
                ->with('error', 'Perusahaan belum diverifikasi admin, jadi belum bisa memasang iklan.');
        }

        $plan = $company->effectivePlan();
        if (! $company->hasAdQuota()) {
            return back()->withInput()->with('error',
                "Kuota iklan paket {$plan->name} penuh. Tutup satu iklan atau upgrade paket.");
        }

        $data      = $this->validated($request);
        $questions = $this->questionsFrom($data['questions'] ?? '');

        if (count($questions) > $plan->max_screening_questions) {
            return back()->withInput()->with('error',
                "Paket {$plan->name} maksimal {$plan->max_screening_questions} pertanyaan screening.");
        }

        DB::transaction(function () use ($company, $plan, $data, $questions) {
            $post = $company->jobPosts()->create([
                ...collect($data)->except('questions', 'skills')->all(),
                'skills'    => JobPost::parseSkills($data['skills'] ?? ''),
                'posted_at' => now(),
                'ends_at'   => now()->addDays($plan->ad_duration_days),
            ]);
            foreach ($questions as $i => $text) {
                $post->questions()->create(['question' => $text, 'sort' => $i]);
            }
        });

        return redirect()->route('company.dashboard')
            ->with('ok', "Iklan tayang selama {$plan->ad_duration_days} hari.");
    }

    public function edit(Request $request, JobPost $post)
    {
        Gate::authorize('renew', $post); // cek kepemilikan

        if ($post->isExpired()) {
            return redirect()->route('company.dashboard')
                ->with('error', 'Iklan yang masa tayangnya habis tersimpan sebagai arsip dan tidak bisa diedit. Pakai Duplikat atau Up ulang.');
        }

        $company = $request->user()->company;

        return view('company.job-form', ['post' => $post->load('questions'), 'plan' => $company->effectivePlan(), 'company' => $company]);
    }

    public function update(Request $request, JobPost $post)
    {
        Gate::authorize('renew', $post);

        if (Gate::denies('update', $post)) {
            return redirect()->route('company.dashboard')
                ->with('error', 'Iklan ini sudah berakhir dan tidak bisa diedit.');
        }

        $plan      = $request->user()->company->effectivePlan();
        $data      = $this->validated($request);
        $questions = $this->questionsFrom($data['questions'] ?? '');

        if (count($questions) > $plan->max_screening_questions) {
            return back()->withInput()->with('error',
                "Paket {$plan->name} maksimal {$plan->max_screening_questions} pertanyaan screening.");
        }

        DB::transaction(function () use ($post, $data, $questions) {
            // tanggal tayang tidak diubah saat edit; hanya isi iklan
            $post->update([
                ...collect($data)->except('questions', 'skills')->all(),
                'skills' => JobPost::parseSkills($data['skills'] ?? ''),
            ]);
            $post->questions()->delete();
            foreach ($questions as $i => $text) {
                $post->questions()->create(['question' => $text, 'sort' => $i]);
            }
        });

        return redirect()->route('company.dashboard')->with('ok', 'Iklan diperbarui.');
    }

    /** Up ulang: tanggal tayang direset ke hari ini, masa tayang sesuai paket. */
    public function renew(Request $request, JobPost $post)
    {
        Gate::authorize('renew', $post);
        $company = $request->user()->company;

        if (! $company->isApproved()) {
            return back()->with('error', 'Perusahaan belum diverifikasi.');
        }
        if (! $post->isLive() && ! $company->hasAdQuota()) {
            return back()->with('error', 'Kuota iklan penuh. Tutup satu iklan atau upgrade paket dulu.');
        }

        $post->renew();

        return back()->with('ok', 'Iklan tayang lagi mulai hari ini selama '.$company->effectivePlan()->ad_duration_days.' hari.');
    }

    public function duplicate(Request $request, JobPost $post)
    {
        Gate::authorize('duplicate', $post);
        $company = $request->user()->company;
        $plan    = $company->effectivePlan();

        if (! $company->isApproved()) {
            return back()->with('error', 'Perusahaan belum diverifikasi.');
        }
        if (! $company->hasAdQuota()) {
            return back()->with('error', "Kuota iklan paket {$plan->name} penuh.");
        }

        DB::transaction(function () use ($post, $plan) {
            $copy = $post->replicate(['posted_at', 'ends_at', 'is_closed']);
            $copy->posted_at = now();
            $copy->ends_at   = now()->addDays($plan->ad_duration_days);
            $copy->is_closed = false;
            $copy->save();

            foreach ($post->questions->take($plan->max_screening_questions) as $q) {
                $copy->questions()->create(['question' => $q->question, 'sort' => $q->sort]);
            }
        });

        return back()->with('ok', 'Salinan iklan dibuat dan langsung tayang.');
    }

    public function close(Request $request, JobPost $post)
    {
        Gate::authorize('renew', $post);
        $post->update(['is_closed' => true]);

        return back()->with('ok', 'Iklan ditutup.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'title'       => ['required', 'string', 'max:150'],
            'city'        => ['required', 'string', 'max:100'],
            'type'        => ['required', 'in:Full-time,Part-time,Kontrak,Magang,Freelance'],
            'salary_min'  => ['nullable', 'integer', 'min:0'],
            'salary_max'  => ['nullable', 'integer', 'min:0', 'gte:salary_min'],
            'lat'         => ['nullable', 'numeric', 'between:-90,90'],
            'lng'         => ['nullable', 'numeric', 'between:-180,180'],
            'description' => ['nullable', 'string', 'max:5000'],
            'questions'   => ['nullable', 'string', 'max:2000'],
            'skills'      => ['nullable', 'string', 'max:600'],
        ]);
    }

    /** Satu baris = satu pertanyaan screening */
    private function questionsFrom(?string $text): array
    {
        return array_values(array_filter(array_map('trim', preg_split('/\R/', (string) $text))));
    }
}
