<?php

namespace App\Http\Controllers;

use App\Services\SibcoApiService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MenuController extends Controller
{
    public function __construct(private SibcoApiService $sibco) {}

    public function index()
    {
        return view('menu.index');
    }

    public function menu(Request $request): JsonResponse
    {
        $request->validate(['tienda' => ['required', 'string']]);
        $menu = $this->sibco->getMenu($request->tienda);
        return response()->json($menu);
    }

    public function producto(Request $request): JsonResponse
    {
        $request->validate(['producto' => ['required', 'string']]);
        $producto = $this->sibco->getProducto($request->producto);
        return response()->json($producto);
    }

    public function combos(Request $request): JsonResponse
    {
        $request->validate(['tienda' => ['required', 'string']]);
        $combos = $this->sibco->getCombos($request->tienda);
        return response()->json($combos);
    }

    public function adiciones(Request $request): JsonResponse
    {
        $request->validate(['tienda' => ['required', 'string']]);
        $adiciones = $this->sibco->getAdiciones($request->tienda);
        return response()->json($adiciones);
    }
}
