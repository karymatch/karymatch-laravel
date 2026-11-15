<?php

namespace App\Http\Controllers;

use App\Models\JobPost;
use Illuminate\Http\Request;

class SavedJobController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $cv   = $user->cvOrNew();

        $posts = $user->savedJobs()->with('jobPost.company')->latest()->get()
            ->pluck('jobPost')->filter();

        return view('seeker.saved', [
            'posts'   => $posts,
            'cv'      => $cv,
            'savedIds' => $posts->pluck('id')->all(),
            'appliedIds' => $user->applications()->pluck('job_post_id')->all(),
        ]);
    }

    public function toggle(Request $request, JobPost $post)
    {
        $saved = $request->user()->savedJobs()->where('job_post_id', $post->id);

        if ($saved->exists()) {
            $saved->delete();
            $msg = 'Dihapus dari tersimpan.';
        } else {
            $request->user()->savedJobs()->create(['job_post_id' => $post->id]);
            $msg = 'Lowongan disimpan.';
        }

        return back()->with('ok', $msg);
    }
}
