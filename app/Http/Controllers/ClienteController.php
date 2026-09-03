<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cliente;

class ClienteController extends Controller
{
    public function index()
    {
        return Cliente::orderBy('nombre')->get();
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nombre' => 'required|string|max:120',
            'doc_nit' => 'required|string|max:30|unique:clientes,doc_nit',
            'telefono' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:120',
            'direccion' => 'nullable|string|max:200',
        ]);

        $cliente = Cliente::create($data);

        return response()->json($cliente, 201);
    }

    public function show(Cliente $cliente)
    {
        return $cliente->load('vehiculos');
    }

    public function update(Request $request, Cliente $cliente)
    {
        $data = $request->validate([
            'nombre' => 'sometimes|required|string|max:120',
            'doc_nit' => 'sometimes|required|string|max:30|unique:clientes,doc_nit,' . $cliente->id,
            'telefono' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:120',
            'direccion' => 'nullable|string|max:200',
        ]);

        $cliente->update($data);

        return $cliente;
    }

    public function destroy(Cliente $cliente)
    {
        $cliente->delete();

        return response()->json(null, 204);
    }
}