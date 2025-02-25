<?php

namespace App\Http\Controllers;

use App\Models\SchoolNumber;
use Illuminate\Http\Request;

class SchoolNumberController extends Controller
{
    public function index()
    {
        return response()->json(SchoolNumber::all(), 200);
    }

    public function store(Request $request)
    {
        $request->validate([
            'account_id' => 'required|exists:accounts,id',
            'number' => 'required|unique:school_numbers',
        ]);

        $schoolNumber = SchoolNumber::create([
            'account_id' => $request->account_id,
            'number' => $request->number,
        ]);

        return response()->json($schoolNumber, 201);
    }

    public function show($id)
    {
        $schoolNumber = SchoolNumber::find($id);
        if (!$schoolNumber) return response()->json(['message' => 'School number not found'], 404);
        return response()->json($schoolNumber);
    }

    public function update(Request $request, $id)
    {
        $schoolNumber = SchoolNumber::find($id);
        if (!$schoolNumber) return response()->json(['message' => 'School number not found'], 404);

        $schoolNumber->update([
            'number' => $request->number ?? $schoolNumber->number,
        ]);

        return response()->json($schoolNumber);
    }

    public function destroy($id)
    {
        $schoolNumber = SchoolNumber::find($id);
        if (!$schoolNumber) return response()->json(['message' => 'School number not found'], 404);

        $schoolNumber->delete();
        return response()->json(['message' => 'School number deleted successfully']);
    }
}
