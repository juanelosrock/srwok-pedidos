<?php

namespace App\Http\Controllers;

use App\Services\SibcoApiService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function __construct(private SibcoApiService $sibco) {}

    public function enviar(Request $request): JsonResponse
    {
        $data = $request->validate([
            'pdv'           => ['required', 'string'],
            'ciudad'        => ['required', 'string'],
            'direccion'     => ['required', 'string'],
            'nombre'        => ['required', 'string', 'min:3'],
            'correo'        => ['required', 'email'],
            'celular'       => ['required', 'string', 'min:7'],
            'complemento'   => ['nullable', 'string'],
            'formapago'     => ['required', 'string'],
            'cabeceras'     => ['required', 'json'],
            'pedidos'       => ['required', 'json'],
            'cantidades'    => ['required', 'json'],
            'totales'       => ['required', 'json'],
            'contador'      => ['required', 'integer', 'min:1'],
            'total'         => ['required', 'numeric'],
            'valordomicilio'=> ['required', 'numeric'],
            'fcm'           => ['nullable', 'string'],
        ]);

        $tiposPago = [
            'Efectivo'  => 'EF',
            'Datafono'  => 'DA',
            'ONLINE'    => 'PO',
            'RAPPI'     => 'RA',
        ];
        $tipoPago = $tiposPago[$data['formapago']] ?? 'EF';

        $cabeceras  = json_decode($data['cabeceras'], true);
        $pedidos    = json_decode($data['pedidos'], true);
        $cantidades = json_decode($data['cantidades'], true);
        $totales    = json_decode($data['totales'], true);

        $ordenWeb = $this->construirOrdenXml(
            $data, $tipoPago, $cabeceras, $pedidos, $cantidades, $totales
        );

        $respuesta = $this->sibco->enviarPedido($ordenWeb);
        return response()->json($respuesta);
    }

    public function estado(Request $request): JsonResponse
    {
        $request->validate(['pedido' => ['required', 'string']]);
        $estado = $this->sibco->getEstadoPedido($request->pedido);
        return response()->json($estado);
    }

    private function construirOrdenXml(
        array $data,
        string $tipoPago,
        array $cabeceras,
        array $pedidos,
        array $cantidades,
        array $totales
    ): array {
        $doc = new \DOMDocument('1.0', 'UTF-8');
        $doc->xmlStandalone = true;

        $pedido = $doc->appendChild($doc->createElement('PEDIDO'));

        // Cliente
        $cliente = $pedido->appendChild($doc->createElement('CLIENTE'));
        $cliente->appendChild($doc->createElement('NOMBRE',    $data['nombre']));
        $cliente->appendChild($doc->createElement('APELLIDO',  '0'));
        $cliente->appendChild($doc->createElement('CORREO',    $data['correo']));
        $cliente->appendChild($doc->createElement('DIRECCION', $data['direccion']));
        $cliente->appendChild($doc->createElement('CIUDAD',    $data['ciudad']));
        $cliente->appendChild($doc->createElement('TELEFONO',  $data['celular']));
        $cliente->appendChild($doc->createElement('DIRECCION2', $data['complemento'] ?? ''));
        $cliente->appendChild($doc->createElement('FCM',       $data['fcm'] ?? ''));

        // Orden
        $orden = $pedido->appendChild($doc->createElement('ORDEN'));
        $orden->appendChild($doc->createElement('ID',       $data['contador']));
        $orden->appendChild($doc->createElement('NUMORDEN', $data['contador']));
        $orden->appendChild($doc->createElement('ZONA',     $data['pdv']));
        $orden->appendChild($doc->createElement('CIUDAD',   $data['ciudad']));
        $orden->appendChild($doc->createElement('FECHA',    now()->format('Y-m-d H:i:s')));
        $orden->appendChild($doc->createElement('VALOR',    $data['total'] - $data['valordomicilio']));
        $orden->appendChild($doc->createElement('RECARGO',  $data['valordomicilio']));
        $orden->appendChild($doc->createElement('OBSERVACION', $data['nombre']));

        // Pago
        $pago = $pedido->appendChild($doc->createElement('PAGO'));
        $pago->appendChild($doc->createElement('TIPO',  $tipoPago));
        $pago->appendChild($doc->createElement('VALOR', $data['total']));

        // Items
        for ($x = 0; $x < $data['contador']; $x++) {
            $cab      = $cabeceras[$x];
            $cantidad = $cantidades[$x]['cantidad'];
            $pedItem  = $pedidos[$x];

            $item = $pedido->appendChild($doc->createElement('ITEM'));
            $item->appendChild($doc->createElement('ITEMCONSECUTIVO', $x));
            $item->appendChild($doc->createElement('CODIGO',    $cab['codintegracion']));
            $item->appendChild($doc->createElement('PRODUCTO',  $cab['nombre']));
            $item->appendChild($doc->createElement('CANTIDAD',  $cantidad));
            $item->appendChild($doc->createElement('VALOR',     $cab['precio']));

            foreach ($pedItem as $grupo) {
                for ($j = 0; $j < count($grupo); $j++) {
                    $sub = $item->appendChild($doc->createElement('SUBITEM'));
                    $sub->appendChild($doc->createElement('ITEMCONSECUTIVO', $x));
                    $sub->appendChild($doc->createElement('CODIGO',   $grupo[$j]['codintegracion']));
                    $sub->appendChild($doc->createElement('PRODUCTO', $grupo[$j]['nombre']));
                    $sub->appendChild($doc->createElement('CANTIDAD', $cantidad));
                    $sub->appendChild($doc->createElement('VALOR',    $grupo[$j]['precio']));
                }
            }
        }

        $doc->formatOutput = true;
        $xml = $doc->saveXML();
        $obj = simplexml_load_string($xml);
        return json_decode(json_encode($obj), true);
    }
}
