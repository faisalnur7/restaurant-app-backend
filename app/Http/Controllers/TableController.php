<?php

namespace App\Http\Controllers;

use App\Models\Table;
use Illuminate\Http\Request;

class TableController extends Controller
{
    public function index()
    {
        $tables = Table::orderBy('id')->get();
        return response()->json($tables);
    }

    // Create a new category
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $table = Table::create([
            'name' => $request->name,
        ]);

        return response()->json($table, 201);
    }

    // Show a single table
    public function show($id)
    {
        $table = Table::find($id);
        if (!$table) {
            return response()->json(['message' => 'Table not found'], 404);
        }
        return response()->json($table);
    }

    // Update a table
    public function update(Request $request, $id)
    {
        $table = Table::find($id);
        if (!$table) {
            return response()->json(['message' => 'Table not found'], 404);
        }

        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $table->update([
            'name' => $request->name,
        ]);

        return response()->json($table);
    }

    // Delete a table
    public function destroy($id)
    {
        $table = Table::find($id);
        if (!$table) {
            return response()->json(['message' => 'Table not found'], 404);
        }

        $table->delete();
        return response()->json(['message' => 'Table deleted']);
    }
}
