<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateRestaurantSettingRequest;
use App\Models\RestaurantSetting;
use Illuminate\Support\Facades\File;

class RestaurantSettingController extends Controller
{
    /**
     * Get restaurant settings (single row)
     */
    public function index()
    {
        $settings = RestaurantSetting::first();

        if (!$settings) {
            return response()->json([
                'message' => 'Settings not found.',
            ], 200);
        }

        return response()->json($settings);
    }

    /**
     * Update restaurant settings
     */
    public function update(Request $request)
    {
        $settings = RestaurantSetting::firstOrNew();
        $data = $request->all();

        $data['enable_tax'] = $request->enable_tax === "false" ? 0 : 1;

        // Ensure the upload directory exists
        $uploadPath = public_path('uploads/settings');
        if (!File::exists($uploadPath)) {
            File::makeDirectory($uploadPath, 0755, true);
        }

        // Handle logo upload
        if ($request->hasFile('logo')) {
            $request->validate([
                'logo' => 'image|mimes:jpeg,png,jpg,webp|max:2048',
            ]);

            // Delete old file if exists
            if ($settings->logo && File::exists(public_path($settings->logo))) {
                File::delete(public_path($settings->logo));
            }

            $filename = time() . '_' . $request->file('logo')->getClientOriginalName();
            $request->file('logo')->move($uploadPath, $filename);
            $data['logo'] = 'uploads/settings/' . $filename;
        }

        // Handle favicon upload
        if ($request->hasFile('favicon')) {
            $request->validate([
                'favicon' => 'image|mimes:jpeg,png,jpg,ico,svg,webp|max:1024',
            ]);

            // Delete old file if exists
            if ($settings->favicon && File::exists(public_path($settings->favicon))) {
                File::delete(public_path($settings->favicon));
            }

            $filename = time() . '_' . $request->file('favicon')->getClientOriginalName();
            $request->file('favicon')->move($uploadPath, $filename);
            $data['favicon'] = 'uploads/settings/' . $filename;
        }

        $settings->fill($data)->save();

        return response()->json([
            'message' => 'Settings updated successfully.',
            'data' => $settings,
        ]);
    }
}
