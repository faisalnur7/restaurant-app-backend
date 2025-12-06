<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\RestaurantSetting;
use Illuminate\Http\Request;

class BranchController extends Controller
{
    public function index()
    {
        $branches = Branch::orderBy('id')->get();
        return response()->json($branches);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'branch_name'           => 'required|string|max:255',
            'branch_code'           => 'required|string|max:50|unique:branches,branch_code',
            'contact_number'        => 'nullable|string|max:20',
            'email'                 => 'nullable|email|max:255',
            'status'                => 'required|in:active,inactive',
            'address'               => 'nullable|string',
        ]);

        $restaurant_setting = RestaurantSetting::first(); 
        $validated['restaurant_setting_id'] = $restaurant_setting?->id; 
        $branch = Branch::create($validated);

        return response()->json($branch, 201);
    }

    public function show($id)
    {
        $branch = Branch::find($id);

        if (!$branch) {
            return response()->json(['message' => 'Branch not found'], 404);
        }

        return response()->json($branch);
    }

    public function update(Request $request, $id)
    {
        $branch = Branch::find($id);

        if (!$branch) {
            return response()->json(['message' => 'Branch not found'], 404);
        }

        $validated = $request->validate([
            'restaurant_setting_id' => 'nullable|integer',
            'branch_name'           => 'required|string|max:255',
            'branch_code'           => 'required|string|max:50|unique:branches,branch_code,' . $id,
            'contact_number'        => 'nullable|string|max:20',
            'email'                 => 'nullable|email|max:255',
            'status'                => 'required|in:active,inactive',
            'address'               => 'nullable|string',
        ]);

        $branch->update($validated);

        return response()->json($branch);
    }

    public function destroy($id)
    {
        $branch = Branch::find($id);

        if (!$branch) {
            return response()->json(['message' => 'Branch not found'], 404);
        }

        $branch->delete();

        return response()->json(['message' => 'Branch deleted']);
    }
}
