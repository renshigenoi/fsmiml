<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\Web\UpdateProfileRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function show(): View
    {
        return view('dashboard.profile');
    }

    public function update(UpdateProfileRequest $request): RedirectResponse
    {
        $user = $request->user();

        $user->update([
            'name' => $request->validated('name'),
            'phone' => $request->validated('phone'),
        ]);

        if (filled($request->validated('password'))) {
            $user->update([
                'password' => Hash::make($request->validated('password')),
            ]);
        }

        return back()->with('success', 'Profil berhasil diperbarui.');
    }
}
