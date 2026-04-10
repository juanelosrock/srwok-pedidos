<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\RequestException;

class SibcoApiService
{
    private string $baseUrl;
    private string $token;

    public function __construct()
    {
        $this->baseUrl = config('sibco.base_url');
        $this->token   = config('sibco.token');
    }

    private function post(string $endpoint, array $data = []): mixed
    {
        $response = Http::timeout(15)
            ->post($this->baseUrl . $endpoint, array_merge(['token' => $this->token], $data));

        $response->throw();

        return $response->json();
    }

    public function getCiudades(): array
    {
        $json = $this->post('/ciudades');
        return (is_array($json) ? $json['data']['data'] : null) ?? [];
    }

    public function validarDireccion(string $ciudad, string $direccion): mixed
    {
        return $this->post('/validardireccionfix', [
            'ciudad'    => $ciudad,
            'direccion' => $direccion,
        ]);
    }

    public function getMenu(string $tienda): array
    {
        $json = $this->post('/nuevomenu', ['tienda' => $tienda]);
        $data = (is_array($json) ? $json['data']['data'] : null) ?? [];
        return array_values($data);
    }

    public function getProducto(string $producto): array
    {
        $json = $this->post('/productomenu', ['producto' => $producto]);
        $data = (is_array($json) ? $json['data']['data'] : null) ?? [];
        return array_values($data);
    }

    public function getCombos(string $pdv): array
    {
        $json = $this->post('/bdcombos', ['pdv' => $pdv]);
        $data = (is_array($json) ? $json['data']['data'] : null) ?? [];
        return array_values($data);
    }

    public function getAdiciones(string $pdv): array
    {
        $json = $this->post('/bdadiciones', ['pdv' => $pdv]);
        $data = (is_array($json) ? $json['data']['data'] : null) ?? [];
        return array_values($data);
    }

    public function enviarPedido(array $ordenWeb): array
    {
        $json = $this->post('/pedidospaginaweb', [
            'ordenweb' => json_encode($ordenWeb),
        ]);
        return is_array($json) ? $json : [];
    }

    public function getEstadoPedido(string $pedido): array
    {
        $json = $this->post('/estadodelivery', ['pedido' => $pedido]);
        return (is_array($json) ? $json['data']['data'] : null) ?? [];
    }
}
