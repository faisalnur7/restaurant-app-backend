<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class CustomerController extends Controller
{
    public function index()
    {
        $customers = Customer::latest()->paginate(10);
        return response()->json($customers);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'  => 'required|string|max:255',
            'phone' => 'required|string|max:20|unique:customers,phone',
            'email' => 'nullable|email|unique:customers,email',
            'note'  => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        $data = $validated;

        if ($request->hasFile('image')) {
            $filename = time() . '_' . $request->file('image')->getClientOriginalName();
            $request->file('image')->move(public_path('uploads/customers'), $filename);
            $data['image'] = 'uploads/customers/' . $filename;
        }

        $customer = Customer::create($data);

        return response()->json([
            'message' => 'Customer created successfully!',
            'data' => $customer
        ], 201);
    }

    public function show($id)
    {
        $customer = Customer::findOrFail($id);
        return response()->json($customer);
    }

    public function update(Request $request, $id)
    {
        $customer = Customer::findOrFail($id);

        $validated = $request->validate([
            'name'  => 'required|string|max:255',
            'phone' => 'required|string|max:20|unique:customers,phone,' . $customer->id,
            'email' => 'nullable|email|unique:customers,email,' . $customer->id,
            'note'  => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        $data = $validated;

        if ($request->hasFile('image')) {
            // delete old image if exists
            if ($customer->image && File::exists(public_path($customer->image))) {
                File::delete(public_path($customer->image));
            }

            $filename = time() . '_' . $request->file('image')->getClientOriginalName();
            $request->file('image')->move(public_path('uploads/customers'), $filename);
            $data['image'] = 'uploads/customers/' . $filename;
        }

        $customer->update($data);

        return response()->json([
            'message' => 'Customer updated successfully!',
            'data' => $customer
        ]);
    }

    public function destroy($id)
    {
        $customer = Customer::findOrFail($id);

        if ($customer->image && File::exists(public_path($customer->image))) {
            File::delete(public_path($customer->image));
        }

        $customer->delete();

        return response()->json(['message' => 'Customer deleted successfully!']);
    }
}
