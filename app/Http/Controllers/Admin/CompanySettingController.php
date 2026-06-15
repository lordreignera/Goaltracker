<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CompanySetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CompanySettingController extends Controller
{
    public function edit(Request $request)
    {
        abort_unless($request->user()->isAdmin(), 403);

        return view('settings.company', [
            'settings' => CompanySetting::current(),
        ]);
    }

    public function update(Request $request)
    {
        abort_unless($request->user()->isAdmin(), 403);

        $data = $request->validate([
            'company_name' => ['required', 'string', 'max:255'],
            'company_short_name' => ['nullable', 'string', 'max:80'],
            'brand_mark' => ['required', 'string', 'max:12'],
            'logo' => ['nullable', 'image', 'max:2048'],
            'product_name' => ['required', 'string', 'max:120'],
            'tagline' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'website' => ['nullable', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:255'],
        ]);

        $data['company_short_name'] = $data['company_short_name'] ?: $data['company_name'];
        $data['tagline'] = $data['tagline'] ?: 'Plan, review, approve, and report on measurable goals.';

        unset($data['logo']);

        $settings = CompanySetting::current();

        if ($request->hasFile('logo')) {
            if ($settings->logo_path) {
                Storage::disk('public')->delete($settings->logo_path);
            }

            $data['logo_path'] = $request->file('logo')->store('company-logos', 'public');
        }

        $settings->update($data);

        return back()->with('status', 'Company settings saved.');
    }
}
