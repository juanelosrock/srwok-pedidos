# Sr WOK - Pedidos Online (Laravel)

## Descripción
Aplicación web de pedidos a domicilio para la cadena de restaurantes **Sr WOK** (comida oriental).
Migración completa desde PHP plano + jQuery + Materialize CSS a Laravel 11 con Tailwind CSS v4 + Alpine.js.

## Stack
- **Backend:** Laravel 11, PHP 8.2
- **Frontend:** Tailwind CSS v4 (via @tailwindcss/vite), Alpine.js
- **Build:** Vite
- **API externa:** `api.sibco.mobi` — sistema POS de Sr WOK

## Estructura clave

```
app/
  Http/Controllers/
    HomeController.php      # Ciudad + validación de dirección
    MenuController.php      # Menú, productos, combos, adiciones
    OrderController.php     # Construcción XML y envío del pedido
  Services/
    SibcoApiService.php     # Proxy hacia api.sibco.mobi (toda la comunicación externa)
config/
  sibco.php                 # URL y token de la API (valores desde .env)
resources/views/
  layouts/app.blade.php     # Layout principal
  home/index.blade.php      # Pantalla inicio: ciudad + dirección
  menu/index.blade.php      # Menú + carrito + checkout (5 modales)
routes/
  web.php                   # Rutas web + rutas /api/*
public/img/                 # Imágenes del restaurante
```

## Variables de entorno requeridas (.env)
```
SIBCO_API_URL=http://api.sibco.mobi/restsibco/index.php/api/sibco
SIBCO_TOKEN=...JWT token...
APP_URL=https://...ngrok o dominio...
```

## Flujo de la app
1. `/` → usuario selecciona ciudad e ingresa dirección → se valida cobertura con la API
2. `/menu` → carga menú de la tienda asignada → usuario arma su pedido
3. Checkout en 4 modales: carrito → forma de pago → datos cliente → confirmación

## Comandos útiles
```bash
php artisan serve --port=8000   # Servidor local
npm run dev                     # Assets con hot reload
npm run build                   # Build para producción
php artisan config:clear        # Limpiar caché de config
```

## Repositorio
https://github.com/juanelosrock/srwok-pedidos

## Colores de marca
- Principal: `#C62828` (red darken-3)
- Hover/dark: `#B71C1C` (red darken-4)
- Fondo suave: `#FFEBEE`
- Fondo medio: `#FFCDD2`

## Instrucciones de trabajo
- Cada vez que el usuario corrija un error de código, un enfoque incorrecto o una decisión de diseño, registrar la corrección en `MEMORY.md` bajo "Correcciones y lecciones aprendidas"
- Cada vez que se complete una mejora o cambio significativo, registrarlo en `MEMORY.md` bajo "Cambios y mejoras"
- Leer `MEMORY.md` al inicio de cada sesión para retomar contexto

## Notas importantes
- `SibcoApiService::post()` retorna `mixed` porque `/validardireccionfix` devuelve un `int`, no un array
- Todos los endpoints de la API devuelven objetos asociativos → usar `array_values()` antes de retornar al frontend
- El carrito se maneja en `localStorage` del navegador
- El token JWT de SIBCO está en `.env` como `SIBCO_TOKEN` (no hardcodeado)
