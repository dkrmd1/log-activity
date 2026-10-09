<?php

namespace App\Http\Controllers;

use App\Http\Requests\PasswordUpdateRequest;
use App\Http\Requests\ProfileUpdateRequest;
use App\Support\Signature;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    public function edit()
    {
        $user = auth()->user();

        return view('profile.edit', [
            'user' => $user,
            'signatureUri' => Signature::dataUri($user->signature),
        ]);
    }

    public function update(ProfileUpdateRequest $request)
    {
        $user = auth()->user();
        $data = $request->safe()->only(['name', 'email']);

        if ($request->hasFile('avatar')) {
            // Hapus foto lama kalau ada, supaya tidak numpuk file yang tidak terpakai.
            if ($user->avatar) {
                Storage::disk('public')->delete($user->avatar);
            }

            $data['avatar'] = $request->file('avatar')->store('avatars', 'public');
        }

        $user->update($data);

        return back()->with('success', 'Profil berhasil diperbarui.');
    }

    /**
     * Simpan tanda tangan: bisa digambar langsung (canvas -> data PNG) atau upload file gambar.
     */
    public function updateSignature(Request $request)
    {
        $request->validate([
            'signature_data' => ['nullable', 'string', 'max:3000000'],
            'signature_file' => ['nullable', 'file', 'mimes:png,jpg,jpeg', 'max:1024'], // maks 1MB
        ], [], [
            'signature_data' => 'Tanda tangan',
            'signature_file' => 'File tanda tangan',
        ]);

        $bytes = null;

        if ($request->hasFile('signature_file')) {
            $bytes = file_get_contents($request->file('signature_file')->getRealPath());
        } elseif ($request->filled('signature_data')) {
            if (! preg_match('#^data:image/png;base64,(.+)$#s', $request->input('signature_data'), $m)) {
                return back()->withErrors(['signature_data' => 'Format tanda tangan tidak dikenali.']);
            }

            $bytes = base64_decode($m[1], true) ?: null;
        }

        if (! $bytes) {
            return back()->withErrors(['signature_data' => 'Gambar tanda tangan dulu, atau pilih file gambar.']);
        }

        $normalized = Signature::normalize($bytes);

        if (! $normalized) {
            return back()->withErrors(['signature_data' => 'File bukan gambar PNG/JPG yang valid.']);
        }

        $user = $request->user();
        Signature::delete($user->signature);
        $user->update(['signature' => Signature::put($normalized[0], $normalized[1])]);

        return back()->with('success', 'Tanda tangan berhasil disimpan.');
    }

    public function destroySignature(Request $request)
    {
        $user = $request->user();
        Signature::delete($user->signature);
        $user->update(['signature' => null]);

        return back()->with('success', 'Tanda tangan dihapus.');
    }

    public function updatePassword(PasswordUpdateRequest $request)
    {
        $request->user()->update([
            'password' => Hash::make($request->validated('password')),
        ]);

        return back()->with('success', 'Password berhasil diganti.');
    }
}
