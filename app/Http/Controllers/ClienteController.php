<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cliente;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class ClienteController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $clientes = Cliente::all();
        return response()->json($clientes);
    }
    public function indexFullName()
{
    $clientes = Cliente::all()->map(function ($cliente) {
        $cliente->full_name = $cliente->nombre . ' ' . $cliente->apellido;
        return $cliente;
    });

    return response()->json($clientes);
}


    /**
     * Display a paginated listing of the resource.
     */
    public function indexPaginado(Request $request)
    {
        // Obtener parámetros de paginación con valores por defecto
        $page = $request->input('page', 1);
        $perPage = $request->input('perPage', 10);
        
        // Obtener el total de registros
        $totalItems = Cliente::count();
        
        // Calcular el total de páginas
        $totalPages = ceil($totalItems / $perPage);
        
        // Obtener los clientes paginados
        $clientes = Cliente::skip(($page - 1) * $perPage)
                           ->take($perPage)
                           ->get();
        
        // Devolver la respuesta con el formato solicitado
        return response()->json([
            'currentPage' => (int)$page,
            'perPage' => (int)$perPage,
            'totalItems' => $totalItems,
            'totalPages' => $totalPages,
            'data' => $clientes
        ]);
    }

    /**
     * Buscar clientes en varios campos a la vez.
     */
    public function search(Request $request)
    {
        // Obtener el término de búsqueda
        $searchTerm = $request->input('search', '');
        
        // Si el término de búsqueda está vacío, devolver todos los clientes
        if (empty($searchTerm)) {
            return response()->json(Cliente::all());
        }
        
        // Buscar en múltiples campos
        $clientes = Cliente::where('ci', 'LIKE', "%{$searchTerm}%")
                           ->orWhere('telefono', 'LIKE', "%{$searchTerm}%")
                           ->orWhere('email', 'LIKE', "%{$searchTerm}%")
                           ->orWhere('nombre', 'LIKE', "%{$searchTerm}%")
                           ->orWhere('apellido', 'LIKE', "%{$searchTerm}%")
                           ->get();
        
        // Verificar si hay coincidencias
        if ($clientes->isEmpty()) {
            return response()->json([
                'message' => 'No se encontraron coincidencias para: ' . $searchTerm,
                'data' => []
            ]);
        }
        
        return response()->json($clientes);
    }

    public function searchFullName(Request $request)
    {
        // Obtener el término de búsqueda
        $searchTerm = $request->input('search', '');
        
        // Si el término de búsqueda está vacío, devolver todos los clientes
        if (empty($searchTerm)) {
            return response()->json(Cliente::all());
        }
        
        // Buscar en múltiples campos
        $clientes = Cliente::where('ci', 'LIKE', "%{$searchTerm}%")
                           ->orWhere('telefono', 'LIKE', "%{$searchTerm}%")
                           ->orWhere('email', 'LIKE', "%{$searchTerm}%")
                           ->orWhere('nombre', 'LIKE', "%{$searchTerm}%")
                           ->orWhere('apellido', 'LIKE', "%{$searchTerm}%")
                           ->get()
                           ->map(function ($cliente) {
                               $cliente->full_name = $cliente->nombre . ' ' . $cliente->apellido;
                               return $cliente;
                           });
        
        // Verificar si hay coincidencias
        if ($clientes->isEmpty()) {
            return response()->json([
                'message' => 'No se encontraron coincidencias para: ' . $searchTerm,
                'data' => []
            ]);
        }
        
        return response()->json($clientes);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'ci' => 'nullable|string|unique:clientes,ci',
            'nombre' => 'required|string',
            'apellido' => 'required|string',
            'fechaNacimiento' => 'nullable|date',
            'telefono' => 'nullable|string',
            'direccion' => 'nullable|string',
            'email' => 'nullable|email|unique:clientes,email'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $cliente = Cliente::create($request->all());

        return response()->json($cliente, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $cliente = Cliente::find($id);
        
        if (!$cliente) {
            return response()->json(['message' => 'Cliente no encontrado'], 404);
        }

        return response()->json($cliente);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Cliente $cliente)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $cliente = Cliente::find($id);
        
        if (!$cliente) {
            return response()->json(['message' => 'Cliente no encontrado'], 404);
        }

        $validator = Validator::make($request->all(), [
            'ci' => 'required|string|unique:clientes,ci,' . $id,
            'nombre' => 'required|string',
            'apellido' => 'required|string',
            'fechaNacimiento' => 'required|date',
            'telefono' => 'required|string',
            'direccion' => 'required|string',
            'email' => 'required|email|unique:clientes,email,' . $id
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $cliente->update($request->all());

        return response()->json($cliente);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $cliente = Cliente::find($id);
        
        if (!$cliente) {
            return response()->json(['message' => 'Cliente no encontrado'], 404);
        }

        $cliente->delete();

        return response()->json(['message' => 'Cliente eliminado correctamente']);
    }

    /**
     * Obtiene el total de clientes.
     */
    public function totalClientes()
    {
        $resultado = DB::select("SELECT COUNT(*) AS total_clientes FROM clientes");
        
        return response()->json([
            'total_clientes' => $resultado[0]->total_clientes
        ]);
    }



    
}
