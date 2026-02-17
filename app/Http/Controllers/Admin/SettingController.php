<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SettingController extends Controller
{
    public function index()
    {
        $settings = Setting::all()->pluck('value', 'key');
        return view('admin.settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'app_name' => 'required|string|max:255',
            'app_logo' => 'nullable|image|max:2048', // 2MB Max
            'app_favicon' => 'nullable|image|max:1024', // 1MB Max
        ]);

        // Update Text Settings
        Setting::where('key', 'app_name')->update(['value' => $request->app_name]);

        // Handle Logo Upload
        if ($request->hasFile('app_logo')) {
            $path = $request->file('app_logo')->store('public/settings');
            Setting::where('key', 'app_logo')->update(['value' => Storage::url($path)]);
        }

        // Handle Favicon Upload
        if ($request->hasFile('app_favicon')) {
            $path = $request->file('app_favicon')->store('public/settings');
            Setting::where('key', 'app_favicon')->update(['value' => Storage::url($path)]);
        }

        return redirect()->back()->with('success', 'Pengaturan berhasil diperbarui.');
    }
}
