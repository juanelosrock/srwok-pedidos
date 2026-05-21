# MEMORY — Sr WOK Pedidos

Historial de cambios, mejoras y correcciones del proyecto. Actualizar con cada iteración.

---

## Cambios y mejoras

### Despliegue AWS EC2
- Servidor: Amazon Linux 2023, Apache (httpd), ec2-user
- Node.js 20 instalado via nvm (el sistema venía con Node 18, Vite requiere 20+)
- Dominio final: `pedido.sibco.info`
- Permisos SQLite: `chown apache:apache database/ && chmod 775`
- Igual para `storage/` y `bootstrap/cache/`

### UI — Menú
- Eliminado banner imagen + botón volver del header del menú
- Eliminadas estrellas de calificación
- `valorDomicilio` default cambiado de 3600 → 0 (viene del API, no hardcodeado)

### Dirección
- Fix: `orient1` siempre incluido en el array de partes (aunque vacío), para garantizar mínimo 5 elementos que requiere `/validardireccionfix`
- Agregado campo **Complemento** en el formulario de dirección (home)
- El complemento se guarda en `localStorage` como `complemento`
- En el checkout, el campo "Indicaciones de entrega" se pre-llena con el valor del complemento
- `DIRECCION2` en XML = `[complemento del home] + [indicaciones del checkout]`

### Sistema de cupones
- Validación: `POST /api/cupon` → proxied a `cupones.sibco.info/api/v1/coupons/validate`
- Aplicado en el carrito: descuento visual + chip verde/rojo
- `DESCUENTO` en XML `<PAGO>` = porcentaje del cupón (`discount_value` del API)
- Redención: se envía el monto **original** (antes del descuento = `total + cupon_descuento`)
- Error de redención mostrado en modal de confirmación (banner amarillo)

### Registro de cliente en plataforma cupones
- Al confirmar pedido: 1) registrar cliente → 2) aceptar términos → 3) redimir cupón
- Este orden garantiza que el cliente exista al momento de redimir
- Es fire-and-forget (no bloquea la respuesta al usuario)

### Zona horaria
- Cambiado `UTC` → `America/Bogota` (UTC-5) en `config/app.php`
- Afecta el campo `FECHA` del XML enviado a SIBCO

### Modal de producto — grupos de adicionales
- `tipo: "1"` → radio (selección única)
- `tipo: "2"` → checkbox (selección múltiple)
- `minimo`: mínimo requerido de selecciones (0 = opcional)
- `maximo`: máximo permitido de selecciones
- Badge "Requerido" (rojo) cuando `minimo >= 1`, "Opcional" (azul) cuando `minimo == 0`
- Subtítulo dinámico: "Elige 1 opción" / "Elige hasta N" / "Elige entre X y Y"
- Contador en tiempo real: `X/maximo` para grupos tipo 2
- `toggleCheckbox` bloquea silenciosamente cuando se alcanza `maximo`
- `verificarAdicionales` usa `minimo` para determinar si el grupo está completo

---

## Correcciones y lecciones aprendidas

### Alpine.js
- `@click="condicion && funcion()"` → si `condicion` es `false`, Alpine hace `preventDefault()` y bloquea el evento nativo (radio buttons dejan de funcionar)
- **Solución**: usar `@click="if (condicion) funcion()"` — retorna `undefined`, no interfiere

### API SIBCO
- `SibcoApiService::post()` retorna `mixed` porque `/validardireccionfix` devuelve un `int`, no array
- La dirección necesita mínimo 5 partes separadas por espacio para que el endpoint no falle
- El campo `tipo` en los grupos de adicionales puede no aparecer en outputs de tinker si se usa `json_encode` con datos cacheados — verificar siempre con `array_keys()`

### API Cupones
- URL base incorrecta: `str_replace('/validate', '', url)` dejaba `/coupons` → resultado: `/api/v1/coupons/customers/register`
- **Solución**: `str_replace('/coupons/validate', '', url)` → resultado correcto: `/api/v1/customers/register`
- Campo `accept_privacy: true` es **requerido** en el registro (no es obvio, falla con 422)
- Campo `department` es **requerido** en el registro
- En `accept-terms`, el cliente no existe si el registro falló previamente → siempre registrar primero

### Vite / Node
- Error de native binding en Vite: `rm -rf node_modules package-lock.json && npm install && npm run build`
- Node 18 no soportado por Vite → instalar Node 20 via nvm

---

## Valores y configuración clave

| Variable | Valor / Noción |
|---|---|
| PDV Chipichape Cali | Dirección: `CRA 1 2 11`, ciudad `1` → PDV `60` |
| `CUPONES_API_URL` | `https://cupones.sibco.info/api/v1/coupons/validate` |
| Base clientes | `https://cupones.sibco.info/api/v1/customers/` |
| Timezone | `America/Bogota` |
| Puerto local | `8083` (para no chocar con otros proyectos) |
