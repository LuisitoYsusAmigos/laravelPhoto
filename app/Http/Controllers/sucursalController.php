<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\sucursal;
use Illuminate\Support\Facades\Validator;

class sucursalController extends Controller
{
    public function index()
    {
        /*
        $sucursales = sucursal::all();
        if($sucursales->isEmpty()){
            return response()->json(['message' => 'No hay sucursales rgistradas'], 404);
        }
        return response()->json($sucursales, 200);
        */
        $data = [
            'sucursales' => sucursal::all()
        ];
        return response()->json($data, 200);
        
    }

    public function store(Request $request)
    {
        // Verificar los datos recibidos
        $datos = $request->all();
        if (empty($datos)) {
            return response()->json(['message' => 'No se recibieron datos'], 400);
        }
    
        // Validación
        $validator = Validator::make($datos, [
            'lugar' => 'required|unique:sucursal'
        ]);
    
        if ($validator->fails()) {
            return response()->json(['message' => 'Faltan datos', 'errors' => $validator->errors()], 400);
        }
    
        // Crear sucursal
        try {
            $sucursal = sucursal::create([
                'lugar' => $request->input('lugar')
            ]);
    
            return response()->json([
                'sucursal' => $sucursal,
                'status' => 201
            ], 201);
    
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error al crear sucursal', 'error' => $e->getMessage()], 500);
        }
    }

    public function show($id)
    {
        $sucursal = sucursal::find($id);
        if (empty($sucursal)) {
            return response()->json(['message' => 'No se encontró la sucursal'], 404);
        }
        return response()->json($sucursal, 200);
    }
    
    public function destroy($id)
    {
        $sucursal = sucursal::find($id);
        if (empty($sucursal)) {
            return response()->json(['message' => 'No se encontró la sucursal'], 404);
        }
    
        try {
            $sucursal->delete();
            return response()->json(['message' => 'Sucursal eliminada'], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error al eliminar sucursal', 'error' => $e->getMessage()], 500);
        }
    }

    public function update(Request $request, $id){
        $sucursal = sucursal::find($id);
        if (empty($sucursal)) {
            return response()->json(['message' => 'No se encontró la sucursal'], 404);
        }
         
        $validator = Validator::make($request->all(), [
            'lugar' => 'required|unique:sucursal'
        ]);

        if($validator -> fails()){
            return response()->json(['message' => 'Faltan datos', 'errors' => $validator->errors()], 400);
        }

        $sucursal->lugar = $request->lugar;
        $sucursal->save();
        $data = [
            'mensaje' => 'Sucursal actualizada',
            'sucursal' => $sucursal,
            'status' => 200
        ];

        return response()->json($data, 200);
    }

    public function updateParcial(Request $request, $id){
        $sucursal = sucursal::find($id);
        if (empty($sucursal)) {
            return response()->json(['message' => 'No se encontró la sucursal'], 404);
        }
         
        $validator = Validator::make($request->all(), [
            'lugar' => 'unique:sucursal'
        ]);

        if($validator -> fails()){
            return response()->json(['message' => 'Faltan datos', 'errors' => $validator->errors()], 400);
        }

        if($request->has('lugar')){
            $sucursal->lugar = $request->lugar;
        }
        $sucursal->save();
        $data = [
            'mensaje' => 'Sucursal actualizada',
            'sucursal' => $sucursal,
            'status' => 200
        ];

        return response()->json($data, 200);
    }
}
