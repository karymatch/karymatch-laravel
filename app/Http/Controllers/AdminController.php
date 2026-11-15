<?php

namespace App\Http\Controllers;

use App\Models\Company;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function dashboard()
    {
        return view('admin.dashboard', [
            'companies' => Company::with('user')->orderByRaw("verification_status = 'menunggu' DESC")->latest()->get(),
        ]);
    }

    public function approve(Company $company)
    {
        $company->update(['verification_status' => 'disetujui', 'rejection_reason' => null]);

        return back()->with('ok', "{$company->name} disetujui.");
    }

    public function reject(Request $request, Company $company)
    {
        $data = $request->validate(['reason' => ['required', 'string', 'max:500']]);
        $company->update(['verification_status' => 'ditolak', 'rejection_reason' => $data['reason']]);

        return back()->with('ok', "{$company->name} ditolak.");
    }
}
