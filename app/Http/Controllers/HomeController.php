<?php

namespace App\Http\Controllers;

use App\Services\SibcoApiService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function __construct(private SibcoApiService $sibco) {}

    public function index()
    {
        return view('home.index');
    }

    public function ciudades(): JsonResponse
    {
        $ciudades = $this->sibco->getCiudades();
        return response()->json($ciudades);
    }

    public function validarDireccion(Request $request): JsonResponse
    {
        $data = $request->validate([
            'ciudad'    => ['required', 'string'],
            'direccion' => ['required', 'string', 'max:100'],
        ]);

        $resultado = $this->sibco->validarDireccion($data['ciudad'], $data['direccion']);
        return response()->json($resultado);
    }
}
