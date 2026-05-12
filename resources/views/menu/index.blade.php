@extends('layouts.app')

@section('title', 'Sr WOK - Menú')

@push('head')
<style>
    :root {
        --rappi: #C62828;
        --rappi-dark: #B71C1C;
        --rappi-light: #FFEBEE;
        --rappi-mid: #FFCDD2;
    }
    .btn-rappi { background-color: var(--rappi); }
    .btn-rappi:hover { background-color: var(--rappi-dark); }
    .btn-rappi:disabled { background-color: #EF9A9A; cursor: not-allowed; }
    .tab-active { color: var(--rappi) !important; border-bottom: 2px solid var(--rappi); }
    .tab-pill { scroll-snap-align: start; }
    .brand-light { background-color: var(--rappi-light); }
    .brand-border { border-color: var(--rappi) !important; }
    .brand-text { color: var(--rappi) !important; }
    input:focus, select:focus, textarea:focus { outline: none; border-color: var(--rappi) !important; box-shadow: 0 0 0 3px #C6282818; }
    .img-cover { width: 100%; height: 100%; object-fit: cover; display: block; }
    .hide-scrollbar::-webkit-scrollbar { display: none; }
    .hide-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
    .line-clamp-2 { display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
</style>
@endpush

@section('content')
<div x-data="menuApp()" x-init="iniciar()" class="min-h-screen bg-[#F6F6F6]">

    {{-- ===== HEADER RESTAURANTE ===== --}}
    <div class="bg-white">
        {{-- Info restaurante --}}
        <div class="px-4 pt-3 pb-0">
            <div class="flex items-start gap-3 mb-3">
                {{-- Logo flotante sobre el banner --}}
                <div class="w-14 h-14 rounded-xl overflow-hidden border-2 border-white shadow-md flex-shrink-0 bg-white">
                    <img src="/img/fondo.jpg" class="img-cover" alt="Sr WOK"/>
                </div>
                <div class="flex-1 min-w-0 pt-1">
                    <h1 class="font-bold text-gray-900 text-base leading-tight" x-text="tienda.nombre || 'Sr WOK'"></h1>
                    <p class="text-xs text-gray-400 mt-0.5" x-text="tienda.descripcion || 'Oriental Buffet'"></p>
                </div>
            </div>

            {{-- Stats --}}
            <div class="flex items-center gap-4 pb-3 border-b border-gray-100 text-xs text-gray-500">
                <div class="flex items-center gap-1">
                    <svg class="w-3.5 h-3.5 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    <span class="font-medium text-green-600">Abierto</span>
                </div>
                <div class="flex items-center gap-1">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span x-text="(tienda.tiempoEntrega || '30-45') + ' min'"></span>
                </div>
                <div class="flex items-center gap-1">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 18.657A8 8 0 016.343 7.343S7 9 9 10c0-2 .5-5 2.986-7C14 5 16.09 5.777 17.656 7.343A7.975 7.975 0 0120 13a7.975 7.975 0 01-2.343 5.657z"/>
                    </svg>
                    <span>Domicilio <b class="text-gray-700" x-text="'$' + formatNum(valorDomicilio)"></b></span>
                </div>
            </div>

            {{-- Tabs de categorías (scroll horizontal) --}}
            <div x-show="categorias.length > 0" class="flex gap-0 overflow-x-auto hide-scrollbar -mx-4 px-4">
                <button
                    @click="categoriaFiltro = '0'"
                    :class="categoriaFiltro === '0' ? 'tab-active' : 'text-gray-400 border-b-2 border-transparent'"
                    class="tab-pill flex-shrink-0 text-xs font-semibold py-3 px-3 whitespace-nowrap transition-colors"
                >Todos</button>
                <template x-for="cat in categorias" :key="cat.comboid">
                    <button
                        @click="categoriaFiltro = String(cat.comboid); $nextTick(() => scrollToCategory(cat.comboid))"
                        :class="categoriaFiltro === String(cat.comboid) ? 'tab-active' : 'text-gray-400 border-b-2 border-transparent'"
                        class="tab-pill flex-shrink-0 text-xs font-semibold py-3 px-3 whitespace-nowrap transition-colors"
                        x-text="cat.combo"
                    ></button>
                </template>
            </div>
        </div>
    </div>

    {{-- ===== CARGANDO ===== --}}
    <div x-show="cargando" class="flex flex-col items-center justify-center py-20">
        <div class="w-10 h-10 border-3 brand-border border-t-transparent rounded-full animate-spin mb-3"></div>
        <p class="text-sm text-gray-400">Cargando menú...</p>
    </div>

    {{-- ===== LISTA DE PRODUCTOS ===== --}}
    <main x-show="!cargando" class="max-w-2xl mx-auto pb-28">
        <template x-for="cat in menuFiltrado" :key="cat.comboid">
            <div :id="'cat-' + cat.comboid" class="mb-2">
                {{-- Título categoría --}}
                <div class="px-4 pt-5 pb-2 bg-[#F6F6F6] sticky top-0 z-10">
                    <h2 class="text-sm font-bold text-gray-800 uppercase tracking-wide" x-text="cat.combo"></h2>
                </div>

                {{-- Productos --}}
                <div class="bg-white divide-y divide-gray-50">
                    <template x-for="prod in cat.productos" :key="prod.id">
                        <button
                            @click="tiendaAbierta ? abrirProducto(prod, cat) : (modal = 'cerrado')"
                            class="w-full flex items-center gap-3 px-4 py-4 text-left hover:bg-gray-50 transition-colors active:bg-gray-100"
                        >
                            {{-- Texto --}}
                            <div class="flex-1 min-w-0 pr-1">
                                <div class="flex items-center gap-1.5 mb-0.5">
                                    <template x-if="parseInt(prod.descuento) > 0">
                                        <span class="text-[10px] font-bold bg-[#C62828] text-white px-1.5 py-0.5 rounded-md" x-text="prod.descuento + '% OFF'"></span>
                                    </template>
                                </div>
                                <h3 class="font-semibold text-gray-900 text-sm leading-snug" x-text="prod.nombre"></h3>
                                <p class="text-xs text-gray-400 mt-1 line-clamp-2 leading-relaxed" x-text="prod.descripcion"></p>
                                <div class="mt-2 flex items-center gap-2">
                                    <span class="text-sm font-bold text-gray-900" x-text="'$' + formatNum(prod.precio)"></span>
                                    <template x-if="parseInt(prod.descuento) > 0">
                                        <span class="text-xs text-gray-300 line-through" x-text="'$' + formatNum(prod.valordescuento)"></span>
                                    </template>
                                </div>
                            </div>
                            {{-- Imagen --}}
                            <div class="relative flex-shrink-0 w-24 h-24">
                                <div class="w-24 h-24 rounded-xl overflow-hidden bg-gray-100">
                                    <img :src="prod.fotoproducto" :alt="prod.nombre"
                                        class="img-cover"
                                        onerror="this.src='/img/fondo.jpg'"
                                    />
                                </div>
                                <div class="absolute -bottom-2 -right-1 w-7 h-7 bg-[#C62828] rounded-full flex items-center justify-center shadow-md">
                                    <svg class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 4v16m8-8H4"/>
                                    </svg>
                                </div>
                            </div>
                        </button>
                    </template>
                </div>
            </div>
        </template>
    </main>

    {{-- ===== CARRITO FLOTANTE ===== --}}
    <div x-show="carrito.length > 0" x-transition class="fixed bottom-0 left-0 right-0 z-30 p-4 bg-gradient-to-t from-[#F6F6F6] via-[#F6F6F6]">
        <button @click="abrirModal('carrito')"
            class="btn-rappi w-full max-w-2xl mx-auto flex items-center justify-between text-white font-semibold py-4 px-5 rounded-2xl shadow-xl block transition-transform active:scale-[0.98]"
        >
            <span class="bg-white/20 text-white text-xs font-bold w-6 h-6 rounded-full flex items-center justify-center" x-text="carrito.length"></span>
            <span class="text-sm">Ver mi pedido</span>
            <span class="text-sm font-bold" x-text="'$' + formatNum(totalConDomicilio)"></span>
        </button>
    </div>

    {{-- ===== MODAL: Detalle producto ===== --}}
    <div x-show="modal === 'producto'"
        x-transition:enter="transition ease-out duration-250"
        x-transition:enter-start="opacity-0 translate-y-full"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 translate-y-full"
        class="fixed inset-0 z-40 flex items-end bg-black/50"
        @click.self="cerrarModal()"
    >
        <div class="bg-white w-full max-w-2xl mx-auto rounded-t-3xl max-h-[92vh] flex flex-col">
            {{-- Imagen grande --}}
            <div class="relative flex-shrink-0 h-52 rounded-t-3xl overflow-hidden bg-gray-100">
                <img :src="productoActual.foto || '/img/fondo.jpg'" :alt="productoActual.nombre"
                    class="img-cover"
                    onerror="this.src='/img/fondo.jpg'"
                />
                <button @click="cerrarModal()"
                    class="absolute top-3 right-3 w-9 h-9 bg-white rounded-full flex items-center justify-center shadow-lg">
                    <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            {{-- Contenido scrollable --}}
            <div class="flex-1 overflow-y-auto">
                <div class="px-4 pt-4 pb-2">
                    <h3 class="text-xl font-bold text-gray-900" x-text="productoActual.nombre"></h3>
                    <p class="text-sm text-gray-400 mt-1 leading-relaxed" x-text="productoActual.descripcion"></p>
                    <p class="text-lg font-bold text-gray-900 mt-2" x-text="'$' + formatNum(productoActual.precio)"></p>
                </div>

                {{-- Selector cantidad --}}
                <div class="flex items-center justify-between px-4 py-3 border-y border-gray-100 mt-2">
                    <span class="text-sm font-semibold text-gray-700">Cantidad</span>
                    <div class="flex items-center gap-4">
                        <button @click="cantidad > 1 && cantidad--"
                            class="w-8 h-8 border-2 border-gray-200 rounded-full flex items-center justify-center hover:border-[#C62828] transition-colors"
                            :class="cantidad <= 1 ? 'opacity-40' : ''">
                            <svg class="w-3 h-3 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M20 12H4"/>
                            </svg>
                        </button>
                        <span class="text-lg font-bold text-gray-900 w-5 text-center" x-text="cantidad"></span>
                        <button @click="cantidad++"
                            class="w-8 h-8 bg-[#C62828] rounded-full flex items-center justify-center hover:bg-[#B71C1C] transition-colors">
                            <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 4v16m8-8H4"/>
                            </svg>
                        </button>
                    </div>
                </div>

                {{-- Cargando adicionales --}}
                <div x-show="cargandoAdicionales" class="flex items-center gap-3 px-4 py-5">
                    <div class="w-5 h-5 border-2 brand-border border-t-transparent rounded-full animate-spin"></div>
                    <span class="text-sm text-gray-400">Cargando opciones...</span>
                </div>

                {{-- Grupos de adicionales --}}
                <template x-if="!cargandoAdicionales && adicionalesProducto.length > 0">
                    <div class="divide-y divide-gray-50">
                        <template x-for="grupo in adicionalesProducto" :key="grupo.idcategoria">
                            <div class="px-4 py-4" :id="'grupo-' + grupo.idcategoria">
                                <div class="flex items-center justify-between mb-3">
                                    <div>
                                        <h4 class="font-bold text-gray-900 text-sm" x-text="grupo.nombrecat"></h4>
                                        <p class="text-xs text-gray-400 mt-0.5">Elige 1 opción</p>
                                    </div>
                                    <span class="text-xs font-semibold bg-[#FFEBEE] brand-text px-2.5 py-1 rounded-full">Requerido</span>
                                </div>
                                <div class="space-y-2">
                                    <template x-for="adic in grupo.adicionales" :key="adic.adicionalesid">
                                        <label class="flex items-center justify-between p-3 rounded-xl border-2 cursor-pointer transition-all"
                                            :class="seleccionAdicionales[grupo.idcategoria] == adic.adicionalesid
                                                ? 'brand-border bg-[#FFEBEE]'
                                                : 'border-gray-100 hover:border-gray-200'"
                                        >
                                            <div class="flex items-center gap-3">
                                                <div class="w-5 h-5 rounded-full border-2 flex items-center justify-center flex-shrink-0 transition-all"
                                                    :class="seleccionAdicionales[grupo.idcategoria] == adic.adicionalesid
                                                        ? 'brand-border bg-[#C62828]'
                                                        : 'border-gray-300'"
                                                >
                                                    <template x-if="seleccionAdicionales[grupo.idcategoria] == adic.adicionalesid">
                                                        <svg class="w-2.5 h-2.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3.5" d="M5 13l4 4L19 7"/>
                                                        </svg>
                                                    </template>
                                                </div>
                                                <span class="text-sm text-gray-800" x-text="adic.adicionalnombre"></span>
                                            </div>
                                            <div class="flex items-center gap-2">
                                                <span x-show="parseInt(adic.precio) > 0" class="text-xs font-semibold text-gray-500" x-text="'+$' + formatNum(adic.precio)"></span>
                                                <input type="radio"
                                                    :name="'grupo-' + grupo.idcategoria"
                                                    :value="adic.adicionalesid"
                                                    x-model="seleccionAdicionales[grupo.idcategoria]"
                                                    @change="verificarAdicionales(); avanzarGrupo(grupo.idcategoria)"
                                                    class="sr-only"
                                                />
                                            </div>
                                        </label>
                                    </template>
                                </div>
                            </div>
                        </template>
                    </div>
                </template>

                <div class="h-28"></div>
            </div>

            {{-- Footer fijo --}}
            <div class="flex-shrink-0 border-t border-gray-100 px-4 py-3 bg-white">
                <button @click="agregarAlCarrito()" :disabled="!puedoAgregar"
                    class="btn-rappi w-full text-white font-bold py-4 rounded-2xl transition-all flex items-center justify-between px-5 text-sm"
                >
                    <span class="bg-white/20 text-white text-xs font-bold w-6 h-6 rounded-full flex items-center justify-center" x-text="cantidad"></span>
                    <span>Agregar al pedido</span>
                    <span x-text="'$' + formatNum(subtotalActual)"></span>
                </button>
            </div>
        </div>
    </div>

    {{-- ===== MODAL: Carrito ===== --}}
    <div x-show="modal === 'carrito'"
        x-transition:enter="transition ease-out duration-250"
        x-transition:enter-start="opacity-0 translate-y-full"
        x-transition:enter-end="opacity-100 translate-y-0"
        class="fixed inset-0 z-40 flex items-end bg-black/50"
        @click.self="cerrarModal()"
    >
        <div class="bg-white w-full max-w-2xl mx-auto rounded-t-3xl max-h-[85vh] flex flex-col">
            <div class="flex-shrink-0 px-4 pt-4 pb-3 border-b border-gray-100">
                <div class="w-10 h-1 bg-gray-200 rounded-full mx-auto mb-4"></div>
                <div class="flex items-center justify-between">
                    <h3 class="font-bold text-gray-900 text-lg">Mi pedido</h3>
                    <button @click="cerrarModal()" class="text-sm brand-text font-semibold">Seguir eligiendo</button>
                </div>
            </div>

            <div class="flex-1 overflow-y-auto divide-y divide-gray-50">
                <template x-for="(item, idx) in carrito" :key="idx">
                    <div class="flex items-start gap-3 px-4 py-4">
                        <div class="flex-1">
                            <div class="flex items-baseline gap-2">
                                <span class="text-sm font-bold text-gray-900" x-text="item.cantidad + 'x'"></span>
                                <span class="text-sm font-semibold text-gray-900" x-text="item.nombre"></span>
                            </div>
                            <p x-show="item.adicionales.length" class="text-xs text-gray-400 mt-1" x-text="item.adicionales.map(a => a.nombre || a.adicionalnombre).join(' · ')"></p>
                            <p class="text-sm font-bold text-gray-900 mt-1" x-text="'$' + formatNum(item.total)"></p>
                        </div>
                        <button @click="quitarDelCarrito(idx)"
                            class="w-7 h-7 flex items-center justify-center text-gray-300 hover:text-red-400 flex-shrink-0 mt-0.5 transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                        </button>
                    </div>
                </template>

                <div class="flex items-center justify-between px-4 py-3 text-sm text-gray-500">
                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Cargo de domicilio
                    </div>
                    <span class="font-semibold text-gray-700" x-text="'$' + formatNum(valorDomicilio)"></span>
                </div>

                {{-- Cupón de descuento --}}
                <div class="px-4 py-3 border-t border-gray-50">
                    <template x-if="!cupon.aplicado">
                        <div>
                            <div class="flex gap-2">
                                <input
                                    x-model="cupon.codigo"
                                    @keydown.enter="aplicarCupon()"
                                    type="text"
                                    placeholder="Código de cupón"
                                    class="flex-1 border border-gray-200 rounded-xl px-3 py-2.5 text-sm text-gray-700 uppercase"
                                    :disabled="validandoCupon"
                                />
                                <button
                                    @click="aplicarCupon()"
                                    :disabled="validandoCupon || !cupon.codigo.trim()"
                                    class="btn-rappi text-white text-sm font-semibold px-4 rounded-xl transition-colors disabled:opacity-50"
                                >
                                    <span x-show="!validandoCupon">Aplicar</span>
                                    <span x-show="validandoCupon" class="flex items-center gap-1">
                                        <div class="w-3.5 h-3.5 border-2 border-white border-t-transparent rounded-full animate-spin"></div>
                                    </span>
                                </button>
                            </div>
                            <p x-show="cupon.valido === false" class="text-xs text-red-500 mt-1.5 px-1" x-text="cupon.mensaje"></p>
                        </div>
                    </template>

                    <template x-if="cupon.aplicado">
                        <div class="flex items-center justify-between bg-green-50 border border-green-200 rounded-xl px-3 py-2.5">
                            <div class="flex items-center gap-2">
                                <svg class="w-4 h-4 text-green-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                                </svg>
                                <div>
                                    <p class="text-xs font-bold text-green-700 uppercase" x-text="cupon.codigo"></p>
                                    <p class="text-xs text-green-600" x-text="cupon.mensaje"></p>
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="text-sm font-bold text-green-700" x-text="'-$' + formatNum(cupon.descuento)"></span>
                                <button @click="quitarCupon()" class="text-gray-400 hover:text-red-400 transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            <div class="flex-shrink-0 border-t border-gray-100 px-4 py-4 bg-white">
                <div class="flex justify-between items-center mb-3">
                    <span class="text-base font-bold text-gray-900">Total</span>
                    <span class="text-lg font-bold text-gray-900" x-text="'$' + formatNum(totalConDomicilio)"></span>
                </div>
                <button @click="abrirModal('pago')"
                    class="btn-rappi w-full text-white font-bold py-4 rounded-2xl transition-colors text-sm">
                    Ir al pago
                </button>
            </div>
        </div>
    </div>

    {{-- ===== MODAL: Forma de pago ===== --}}
    <div x-show="modal === 'pago'"
        x-transition:enter="transition ease-out duration-250"
        x-transition:enter-start="opacity-0 translate-y-full"
        x-transition:enter-end="opacity-100 translate-y-0"
        class="fixed inset-0 z-40 flex items-end bg-black/50"
        @click.self="abrirModal('carrito')"
    >
        <div class="bg-white w-full max-w-2xl mx-auto rounded-t-3xl">
            <div class="px-4 pt-4 pb-3 border-b border-gray-100">
                <div class="w-10 h-1 bg-gray-200 rounded-full mx-auto mb-4"></div>
                <div class="flex items-center gap-3">
                    <button @click="abrirModal('carrito')" class="w-8 h-8 flex items-center justify-center rounded-full hover:bg-gray-100">
                        <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                        </svg>
                    </button>
                    <h3 class="font-bold text-gray-900 text-base">¿Cómo vas a pagar?</h3>
                </div>
            </div>

            <div class="px-4 py-4">
                <div class="flex items-center justify-between mb-4 bg-gray-50 rounded-2xl px-4 py-3">
                    <span class="text-sm text-gray-500">Total a pagar</span>
                    <span class="text-lg font-bold text-gray-900" x-text="'$' + formatNum(totalConDomicilio)"></span>
                </div>

                <div class="space-y-2 mb-5">
                    <template x-for="fp in formasPago" :key="fp.valor">
                        <label class="flex items-center gap-4 p-4 rounded-2xl border-2 cursor-pointer transition-all"
                            :class="formaPagoSeleccionada === fp.valor ? 'brand-border bg-[#FFEBEE]' : 'border-gray-100'"
                        >
                            <input type="radio" :value="fp.valor" x-model="formaPagoSeleccionada" class="sr-only"/>
                            <span class="text-2xl" x-text="fp.icono"></span>
                            <div class="flex-1">
                                <p class="font-semibold text-gray-900 text-sm" x-text="fp.texto"></p>
                                <p class="text-xs text-gray-400" x-text="fp.desc"></p>
                            </div>
                            <div class="w-5 h-5 rounded-full border-2 flex items-center justify-center transition-all flex-shrink-0"
                                :class="formaPagoSeleccionada === fp.valor ? 'brand-border bg-[#C62828]' : 'border-gray-300'"
                            >
                                <template x-if="formaPagoSeleccionada === fp.valor">
                                    <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                                    </svg>
                                </template>
                            </div>
                        </label>
                    </template>
                </div>

                <button @click="abrirModal('datos')" :disabled="!formaPagoSeleccionada"
                    class="btn-rappi w-full text-white font-bold py-4 rounded-2xl transition-colors text-sm">
                    Continuar
                </button>
                <div class="pb-2"></div>
            </div>
        </div>
    </div>

    {{-- ===== MODAL: Datos del cliente ===== --}}
    <div x-show="modal === 'datos'"
        x-transition:enter="transition ease-out duration-250"
        x-transition:enter-start="opacity-0 translate-y-full"
        x-transition:enter-end="opacity-100 translate-y-0"
        class="fixed inset-0 z-40 flex items-end bg-black/50"
    >
        <div class="bg-white w-full max-w-2xl mx-auto rounded-t-3xl max-h-[92vh] flex flex-col">
            <div class="flex-shrink-0 px-4 pt-4 pb-3 border-b border-gray-100">
                <div class="w-10 h-1 bg-gray-200 rounded-full mx-auto mb-4"></div>
                <div class="flex items-center gap-3">
                    <button @click="abrirModal('pago')" class="w-8 h-8 flex items-center justify-center rounded-full hover:bg-gray-100">
                        <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                        </svg>
                    </button>
                    <h3 class="font-bold text-gray-900 text-base">¿A quién le entregamos?</h3>
                </div>
            </div>

            <div class="flex-1 overflow-y-auto px-4 py-4 space-y-3">
                <div>
                    <label class="block text-xs font-semibold text-gray-500 mb-1.5 uppercase tracking-wide">Nombre completo</label>
                    <input x-model="cliente.nombre" @blur="validarCliente()" type="text" placeholder="Tu nombre"
                        class="w-full border-2 border-gray-100 rounded-xl px-4 py-3 text-sm text-gray-900 focus:brand-border transition-colors"/>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-500 mb-1.5 uppercase tracking-wide">Correo electrónico</label>
                    <input x-model="cliente.correo" @blur="validarCliente()" type="email" placeholder="tucorreo@ejemplo.com"
                        class="w-full border-2 border-gray-100 rounded-xl px-4 py-3 text-sm text-gray-900 focus:brand-border transition-colors"/>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-500 mb-1.5 uppercase tracking-wide">Celular</label>
                    <input x-model="cliente.celular" @blur="validarCliente()" type="tel" placeholder="3001234567"
                        class="w-full border-2 border-gray-100 rounded-xl px-4 py-3 text-sm text-gray-900 focus:brand-border transition-colors"/>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-500 mb-1.5 uppercase tracking-wide">Indicaciones de entrega</label>
                    <textarea x-model="cliente.complemento" rows="2"
                        placeholder="Apto, piso, torre, punto de referencia..."
                        class="w-full border-2 border-gray-100 rounded-xl px-4 py-3 text-sm text-gray-900 resize-none focus:brand-border transition-colors"></textarea>
                </div>

                {{-- Resumen --}}
                <div class="bg-gray-50 rounded-2xl p-4 space-y-3 mt-2">
                    <div class="flex items-start gap-2.5 text-sm">
                        <svg class="w-4 h-4 brand-text mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                        </svg>
                        <div>
                            <p class="text-xs text-gray-400">Dirección de entrega</p>
                            <p class="font-medium text-gray-800 text-xs mt-0.5" x-text="(localStorage.getItem('nombreciudad') || '') + ' · ' + (localStorage.getItem('direccion') || '')"></p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2.5 text-sm">
                        <svg class="w-4 h-4 brand-text flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                        <div>
                            <p class="text-xs text-gray-400">Pago · Total</p>
                            <p class="font-medium text-gray-800 text-xs mt-0.5">
                                <span x-text="formasPago.find(f => f.valor === formaPagoSeleccionada)?.texto"></span>
                                · <span class="font-bold" x-text="'$' + formatNum(totalConDomicilio)"></span>
                            </p>
                        </div>
                    </div>
                </div>

                <p x-show="errorEnvio" class="text-red-500 text-xs text-center py-1" x-text="errorEnvio"></p>
                <div class="h-24"></div>
            </div>

            <div class="flex-shrink-0 border-t border-gray-100 px-4 py-3 bg-white">
                <button @click="enviarPedido()" :disabled="!clienteValido || enviando"
                    class="btn-rappi w-full text-white font-bold py-4 rounded-2xl transition-colors flex items-center justify-center gap-2 text-sm">
                    <template x-if="enviando">
                        <div class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></div>
                    </template>
                    <span x-text="enviando ? 'Enviando...' : 'Confirmar pedido'"></span>
                </button>
            </div>
        </div>
    </div>

    {{-- ===== MODAL: Pedido confirmado ===== --}}
    <div x-show="modal === 'confirmado'" x-transition class="fixed inset-0 z-50 flex items-end bg-black/50">
        <div class="bg-white w-full max-w-2xl mx-auto rounded-t-3xl p-6">
            <div class="text-center mb-6">
                <div class="w-20 h-20 bg-green-50 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-10 h-10 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <h3 class="text-2xl font-bold text-gray-900">¡Pedido confirmado!</h3>
                <p class="text-gray-400 text-sm mt-2">Tu orden está en camino. Tiempo estimado: <span class="font-semibold text-gray-700" x-text="(tienda.tiempoEntrega || '30-45') + ' min'"></span></p>
            </div>

            <template x-if="cuponError">
                <div class="flex items-start gap-3 bg-yellow-50 border border-yellow-200 rounded-xl px-4 py-3 mb-4">
                    <svg class="w-5 h-5 text-yellow-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                    </svg>
                    <div>
                        <p class="text-sm font-semibold text-yellow-800">Problema con el cupón</p>
                        <p class="text-xs text-yellow-700 mt-0.5" x-text="cuponError"></p>
                    </div>
                </div>
            </template>

            <p class="text-xs font-semibold text-gray-400 uppercase tracking-widest mb-3 text-center">¿Preguntas? Llámanos</p>
            <div class="grid grid-cols-2 gap-2 mb-5">
                @foreach([['Armenia','6067359868'],['Bogotá','6017444424'],['Cali','6026959570'],['Ibagué','6082771250'],['Manizales','6068918899'],['Medellín','6046044949'],['Palmira','6022868970'],['Pereira','6063400551'],['Popayán','6028368090'],['Tuluá','6022359880']] as [$c,$t])
                <a href="tel:{{ $t }}" class="flex items-center gap-2 bg-gray-50 hover:bg-gray-100 rounded-xl px-3 py-2.5 transition-colors">
                    <svg class="w-3.5 h-3.5 text-green-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 8V5z"/>
                    </svg>
                    <span class="text-xs font-medium text-gray-700">{{ $c }}</span>
                </a>
                @endforeach
            </div>
            <a href="/" class="btn-rappi w-full text-white font-bold py-4 rounded-2xl transition-colors text-sm block text-center">
                Hacer otro pedido
            </a>
        </div>
    </div>

    {{-- ===== MODAL: Tienda cerrada ===== --}}
    <div x-show="modal === 'cerrado'" x-transition class="fixed inset-0 z-50 flex items-end bg-black/50">
        <div class="bg-white w-full max-w-2xl mx-auto rounded-t-3xl overflow-hidden">
            <img src="/img/wok2.jpg" alt="" class="w-full h-40 object-cover"/>
            <div class="p-6 text-center">
                <h3 class="text-xl font-bold text-gray-900 mb-2">Estamos cerrados</h3>
                <p class="text-sm text-gray-400 mb-1">El punto de venta no está disponible en este momento.</p>
                <template x-if="tienda.apertura">
                    <p class="text-sm text-gray-600 mb-5">Horario: <span class="font-bold" x-text="tienda.apertura + ' – ' + tienda.cierre"></span></p>
                </template>
                <a href="/" class="block w-full bg-gray-900 text-white font-bold py-3.5 rounded-2xl text-sm">Volver al inicio</a>
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
function menuApp() {
    return {
        cargando: true, cargandoAdicionales: false, modal: null,
        menu: [], categorias: [], categoriaFiltro: '0',
        carrito: [], valorDomicilio: 0, tienda: {}, tiendaAbierta: true,
        productoActual: {}, categoriaActual: {}, cantidad: 1,
        adicionalesProducto: [], seleccionAdicionales: {}, puedoAgregar: false,
        formaPagoSeleccionada: '',
        cupon: { codigo: '', aplicado: false, descuento: 0, porcentaje: 0, mensaje: '', valido: null },
        validandoCupon: false, cuponError: '',
        formasPago: [
            { valor: 'Efectivo', texto: 'Efectivo', icono: '💵', desc: 'Paga al recibir tu pedido' },
            { valor: 'Datafono', texto: 'Datáfono', icono: '💳', desc: 'Terminal en la entrega' },
        ],
        cliente: { nombre: '', correo: '', celular: '', complemento: '' },
        clienteValido: false, enviando: false, errorEnvio: '',

        get menuFiltrado() {
            if (this.categoriaFiltro === '0') return this.menu;
            return this.menu.filter(c => String(c.comboid) === String(this.categoriaFiltro));
        },
        get totalCarrito() { return this.carrito.reduce((s, i) => s + i.total, 0); },
        get totalConDomicilio() { return this.totalCarrito + parseInt(this.valorDomicilio) - this.cupon.descuento; },
        get subtotalActual() {
            const base = parseInt(this.productoActual.precio || 0) * this.cantidad;
            const adics = Object.values(this.seleccionAdicionales).reduce((s, id) => {
                const a = this.adicionalesProducto.flatMap(g => g.adicionales).find(x => String(x.adicionalesid) === String(id));
                return s + (a ? parseInt(a.precio || 0) * this.cantidad : 0);
            }, 0);
            return base + adics;
        },

        async iniciar() {
            if (!localStorage.getItem('ciudad')) { window.location.href = '/'; return; }
            const tienda = localStorage.getItem('punto');
            await Promise.all([this.cargarMenu(tienda), this.cargarCombos(tienda), this.cargarAdicionesBase(tienda)]);
            this.cargando = false;
        },

        async cargarMenu(tienda) {
            const res = await this.apiPost('{{ route("api.menu") }}', { tienda });
            const data = await res.json();
            this.menu = data;
            this.categorias = data.map(c => ({ combo: c.combo, comboid: c.comboid }));
            if (data.length > 0) {
                const d = data[0];
                this.tienda = { foto: d.foto, nombre: d.tiendanombre, descripcion: d.tiendadescripcion, tiempoEntrega: d.tiendatiempoentrega, apertura: d.tiendaapertura, cierre: d.tiendacierre };
                this.valorDomicilio = parseInt(d.tiendadelivery) || 0;
                localStorage.setItem('valordomicilio', this.valorDomicilio);
                if (parseInt(d.tiendahorario) === 0) this.modal = 'cerrado';
                if (parseInt(d.tiendaestado) === 0) { this.tiendaAbierta = false; this.modal = 'cerrado'; }
            }
        },

        async cargarCombos(tienda) {
            const res = await this.apiPost('{{ route("api.combos") }}', { tienda });
            localStorage.setItem('combos', await res.text());
        },

        async cargarAdicionesBase(tienda) {
            const res = await this.apiPost('{{ route("api.adiciones") }}', { tienda });
            localStorage.setItem('adiciones', await res.text());
        },

        scrollToCategory(id) {
            const el = document.getElementById('cat-' + id);
            if (el) el.scrollIntoView({ behavior: 'smooth', block: 'start' });
        },

        async abrirProducto(prod, cat) {
            this.productoActual = { ...prod, foto: prod.fotoproducto };
            this.categoriaActual = cat;
            this.cantidad = 1; this.seleccionAdicionales = {};
            this.adicionalesProducto = []; this.puedoAgregar = false;
            this.cargandoAdicionales = true; this.modal = 'producto';
            try {
                const res = await this.apiPost('{{ route("api.producto") }}', { producto: prod.id });
                const adicionales = await res.json();
                this.adicionalesProducto = adicionales;
                if (adicionales.length === 0) this.puedoAgregar = true;
            } finally { this.cargandoAdicionales = false; }
        },

        verificarAdicionales() {
            const requeridos = this.adicionalesProducto.length;
            const completados = Object.values(this.seleccionAdicionales).filter(v => v !== '').length;
            this.puedoAgregar = completados >= requeridos;
        },

        avanzarGrupo(idcategoria) {
            const idx = this.adicionalesProducto.findIndex(g => g.idcategoria == idcategoria);
            const siguiente = this.adicionalesProducto[idx + 1];
            if (siguiente) {
                setTimeout(() => {
                    const el = document.getElementById('grupo-' + siguiente.idcategoria);
                    if (el) el.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }, 150);
            }
        },

        agregarAlCarrito() {
            const combos = JSON.parse(localStorage.getItem('combos') || '[]');
            const adicionesBase = JSON.parse(localStorage.getItem('adiciones') || '[]');
            const combo = combos.find(c => String(c.ID) === String(this.productoActual.id)) || {
                codintegracion: this.productoActual.id, nombre: this.productoActual.nombre, precio: this.productoActual.precio
            };
            const adicionales = Object.entries(this.seleccionAdicionales)
                .filter(([,v]) => v !== '')
                .map(([,id]) => adicionesBase.find(a => String(a.ID) === String(id)))
                .filter(Boolean);
            const totalAdics = adicionales.reduce((s, a) => s + parseInt(a.precio || 0), 0);
            const total = (parseInt(combo.precio || 0) + totalAdics) * this.cantidad;
            const catKey = this.categoriaActual.combo || 'combos';
            const pedidoGrupo = {}; pedidoGrupo[catKey] = adicionales;
            this.carrito.push({ nombre: combo.nombre, cantidad: this.cantidad, adicionales, total, cabecera: combo, pedido: pedidoGrupo });
            this.modal = null;
        },

        quitarDelCarrito(idx) {
            this.carrito.splice(idx, 1);
            if (this.carrito.length === 0) this.modal = null;
        },

        async aplicarCupon() {
            if (!this.cupon.codigo.trim()) return;
            this.validandoCupon = true;
            this.cupon.valido = null;
            try {
                const res = await this.apiPost('{{ route("api.cupon") }}', {
                    code:   this.cupon.codigo.trim().toUpperCase(),
                    amount: this.totalCarrito + parseInt(this.valorDomicilio),
                    phone:  this.cliente.celular || '',
                });
                const data = await res.json();
                if (data.valid) {
                    this.cupon = {
                        codigo:     this.cupon.codigo.trim().toUpperCase(),
                        aplicado:   true,
                        valido:     true,
                        descuento:  parseInt(data.discount_amount) || 0,
                        porcentaje: parseFloat(data.discount_value) || 0,
                        mensaje:    data.message,
                    };
                } else {
                    this.cupon = {
                        codigo:     this.cupon.codigo,
                        aplicado:   false,
                        valido:     false,
                        descuento:  0,
                        porcentaje: 0,
                        mensaje:    data.message || 'Cupón no válido.',
                    };
                }
            } catch (e) {
                this.cupon.valido   = false;
                this.cupon.aplicado = false;
                this.cupon.mensaje  = 'Error al validar el cupón.';
            } finally {
                this.validandoCupon = false;
            }
        },

        quitarCupon() {
            this.cupon = { codigo: '', aplicado: false, descuento: 0, porcentaje: 0, mensaje: '', valido: null };
        },

        async enviarPedido() {
            this.enviando = true; this.errorEnvio = '';
            const payload = {
                pdv: localStorage.getItem('punto'), ciudad: localStorage.getItem('ciudad'),
                nombreciudad: localStorage.getItem('nombreciudad') || '',
                direccion: localStorage.getItem('direccion'), nombre: this.cliente.nombre,
                correo: this.cliente.correo, celular: this.cliente.celular,
                complemento: [localStorage.getItem('complemento') || '', this.cliente.complemento || ''].filter(Boolean).join(' '), formapago: this.formaPagoSeleccionada,
                cabeceras: JSON.stringify(this.carrito.map(i => i.cabecera)),
                pedidos: JSON.stringify(this.carrito.map(i => i.pedido)),
                cantidades: JSON.stringify(this.carrito.map(i => ({ cantidad: i.cantidad }))),
                totales: JSON.stringify(this.carrito.map(i => ({ total: i.total }))),
                contador: this.carrito.length, total: this.totalConDomicilio,
                valordomicilio: this.valorDomicilio, fcm: localStorage.getItem('fcm') || '',
                cupon_codigo:     this.cupon.aplicado ? this.cupon.codigo : '',
                cupon_descuento:  this.cupon.aplicado ? this.cupon.descuento : 0,
                cupon_porcentaje: this.cupon.aplicado ? this.cupon.porcentaje : 0,
            };
            try {
                const res = await this.apiPost('{{ route("api.pedido") }}', payload);
                if (res.ok) {
                    const json = await res.json();
                    this.cuponError = json.cupon_error || '';
                    this.carrito = []; this.modal = 'confirmado';
                    ['pedidos','contador','cantidades','totales','cabeceras','totalenpedido'].forEach(k => localStorage.removeItem(k));
                } else {
                    const err = await res.json();
                    this.errorEnvio = err.message || 'Error al enviar el pedido.';
                }
            } catch (e) { this.errorEnvio = 'Error de conexión.'; }
            finally { this.enviando = false; }
        },

        validarCliente() {
            const { nombre, correo, celular } = this.cliente;
            this.clienteValido = nombre.length >= 3 && correo.includes('@') && celular.length >= 7;
        },

        abrirModal(n) { this.modal = n; },
        cerrarModal() { this.modal = null; },

        apiPost(url, data) {
            return fetch(url, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content, 'Content-Type': 'application/json' },
                body: JSON.stringify(data),
            });
        },

        formatNum(num) {
            if (!num && num !== 0) return '-';
            return Math.floor(Math.abs(Number(num))).toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
        },
    }
}
</script>
@endpush
