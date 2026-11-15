<?php

namespace App\Http\Controllers;

use App\Models\Cv;
use Illuminate\Http\Request;

class CvController extends Controller
{
    public function edit(Request $request)
    {
        return view('seeker.cv', ['cv' => $request->user()->cvOrNew(), 'user' => $request->user()]);
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'summary'       => ['nullable', 'string', 'max:1000'],
            'phone'         => ['nullable', 'regex:/^\+?[0-9][0-9 \-]{6,18}$/'],
            'city'          => ['nullable', 'string', 'max:100'],
            'link'          => ['nullable', 'url', 'max:255'],
            'skills'        => ['nullable', 'string', 'max:1500'],
            'education'     => ['nullable', 'string', 'max:3000'],
            'experience'    => ['nullable', 'string', 'max:3000'],
            'projects'      => ['nullable', 'string', 'max:3000'],
            'certificates'  => ['nullable', 'string', 'max:3000'],
            'languages'     => ['nullable', 'string', 'max:1000'],
            'pref_salary'   => ['nullable', 'integer', 'min:0', 'max:1000'],
            'pref_location' => ['nullable', 'string', 'max:100'],
            'pref_type'     => ['nullable', 'in:Full-time,Part-time,Kontrak,Magang,Freelance,Remote'],
        ], [
            'phone.regex' => 'Nomor HP tidak valid. Contoh: 081234567890.',
            'link.url'    => 'Tautan portofolio harus diawali https://',
        ]);

        $cv = $request->user()->cvOrNew();

        $cv->update([
            'summary'       => $data['summary'] ?? null,
            'phone'         => $data['phone'] ?? null,
            'city'          => $data['city'] ?? null,
            'link'          => $data['link'] ?? null,
            'skills'        => Cv::parseSkills($data['skills'] ?? ''),
            'education'     => Cv::parseLines($data['education'] ?? ''),
            'experience'    => Cv::parseLines($data['experience'] ?? ''),
            'projects'      => Cv::parseLines($data['projects'] ?? ''),
            'certificates'  => Cv::parseLines($data['certificates'] ?? ''),
            'languages'     => Cv::parseLines($data['languages'] ?? ''),
            'pref_salary'   => $data['pref_salary'] ?? null,
            'pref_location' => $data['pref_location'] ?? null,
            'pref_type'     => $data['pref_type'] ?? null,
        ]);

        return redirect()->route('cv.edit')->with('ok', 'CV disimpan. Kelengkapan '.$cv->fresh()->completeness().'%.');
    }
}
