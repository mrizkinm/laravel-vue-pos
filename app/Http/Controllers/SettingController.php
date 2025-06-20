<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class SettingController extends Controller
{
    public function index()
    {
        return Inertia::render('Settings/Index');
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'company_name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:20',
            'address' => 'required|string|max:255',
            'tax_number' => 'nullable|string|max:50',
            'currency' => 'required|string|max:10',
            'tax_rate' => 'required|numeric|min:0|max:100',
            'low_stock_threshold' => 'required|integer|min:0',
            'default_payment_term' => 'required|integer|min:0',
            'receipt_footer' => 'nullable|string|max:500',
            'show_tax_number_on_receipt' => 'boolean',
            'logo' => 'nullable|image|mimes:jpg,jpeg,png|max:2048'
        ]);

        $settings = Setting::first() ?? new Setting();

        if ($request->hasFile('logo')) {
            if ($settings->logo) {
                Storage::disk('public')->delete($settings->logo);
            }
            $validated['logo'] = $request->file('logo')->store('settings', 'public');
        }

        $settings->fill($validated);
        $settings->save();

        return back()->with('message', 'Settings updated successfully');
    }
}
