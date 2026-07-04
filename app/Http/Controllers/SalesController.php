<?php

namespace App\Http\Controllers;

use App\Models\Sales;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class SalesController extends Controller
{
    public function index()
    {
        $sales = Sales::with(['branch', 'user'])->get();

        // Return berupa view atau response JSON, sesuaikan dengan kebutuhan aplikasi
        return response()->json($sales);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'invoice' => 'required|string|unique:sales',
            'branch_id' => 'required|string|exists:branches,id',
            'user_id' => 'required|string|exists:users,id',
            'date' => 'required|date',
            'subtotal' => 'required|numeric',
            'discount' => 'required|numeric',
            'tax' => 'required|numeric',
            'grand_total' => 'required|numeric',
            'status' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'The given data was invalid.',
                'errors' => $validator->errors(),
            ], 422);
        }
        $validatedData = $validator->validated();

        $validatedData['id'] = Str::uuid()->toString();

        $sale = Sales::create($validatedData);

        return response()->json($sale, 201);
    }

    public function show(string $id)
    {
        $sale = Sales::with(['branch', 'user'])->findOrFail($id);

        return response()->json($sale);
    }

    public function update(Request $request, string $id)
    {
        $sale = Sales::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'invoice' => 'required|string|unique:sales,invoice,'.$id,
            'branch_id' => 'required|string|exists:branches,id',
            'user_id' => 'required|string|exists:users,id',
            'date' => 'required|date',
            'subtotal' => 'required|numeric',
            'discount' => 'required|numeric',
            'tax' => 'required|numeric',
            'grand_total' => 'required|numeric',
            'status' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'The given data was invalid.',
                'errors' => $validator->errors(),
            ], 422);
        }
        $validatedData = $validator->validated();

        $sale->update($validatedData);

        return response()->json($sale);
    }

    public function destroy(string $id)
    {
        $sale = Sales::findOrFail($id);
        $sale->delete();

        return response()->json(['message' => 'Deleted']);
    }
}
