<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Fotografia;

class FotografiaController extends Controller
{
    public function index()
    {
        return Fotografia::orderByDesc('fecha_subida')->get();
    }

    public function store(Request $request)
    {
        $request->validate([
            'foto' => 'required|image|max:8192',
            'descripcion' => 'nullable|string|max:200',
        ]);

        $ruta = $request->file('foto')->store('fotografias', 'public');

        $fotografia = Fotografia::create([
            'fotografiable_id' => 0,
            'fotografiable_type' => 'pendiente',
            'ruta_archivo' => $ruta,
            'descripcion' => $request->descripcion,
            'fecha_subida' => now(),
        ]);

        return response()->json($fotografia, 201);
    }

    public function show(Fotografia $fotografia)
    {
        return $fotografia;
    }

    public function destroy(Fotografia $fotografia)
    {
        \Storage::disk('public')->delete($fotografia->ruta_archivo);
        $fotografia->delete();

        return response()->json(null, 204);
    }
}