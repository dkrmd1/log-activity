<?php

namespace App\Http\Controllers;

use App\Http\Requests\AppSettingRequest;
use App\Models\AppSetting;
use Illuminate\Support\Facades\Storage;

class SettingController extends Controller
{
    public function edit()
    {
        return view('settings.edit', ['setting' => AppSetting::current()]);
    }

    public function update(AppSettingRequest $request)
    {
        $setting = AppSetting::current();
        $data = $request->safe()->only(['app_name']);

        if ($request->hasFile('logo')) {
            if ($setting->logo) {
                Storage::disk('public')->delete($setting->logo);
            }

            $data['logo'] = $request->file('logo')->store('logo', 'public');
        }

        if ($request->hasFile('favicon')) {
            if ($setting->favicon) {
                Storage::disk('public')->delete($setting->favicon);
            }

            $data['favicon'] = $request->file('favicon')->store('favicon', 'public');
        }

        if ($request->hasFile('report_logo')) {
            if ($setting->report_logo) {
                Storage::disk('public')->delete($setting->report_logo);
            }

            $data['report_logo'] = $request->file('report_logo')->store('report_logo', 'public');
        }

        $setting->update($data);

        return back()->with('success', 'Pengaturan aplikasi berhasil disimpan.');
    }
}