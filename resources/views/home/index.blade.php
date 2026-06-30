@extends('layouts.app')

@section('title', 'Sr WOK - Pedidos Online')

@push('head')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<style>
    :root { --rappi: #C62828; --rappi-dark: #B71C1C; }
    .btn-rappi { background-color: var(--rappi); }
    .btn-rappi:hover { background-color: var(--rappi-dark); }
    .btn-rappi:disabled { background-color: #EF9A9A; cursor: not-allowed; }
    .ring-rappi:focus { --tw-ring-color: var(--rappi); }
    input:focus, select:focus { outline: none; box-shadow: 0 0 0 2px #C6282833; border-color: var(--rappi) !important; }
</style>
@endpush

@section('content')
<div x-data="homeApp()" x-init="cargarCiudades()" class="min-h-screen bg-[#F6F6F6] flex flex-col">

    {{-- Header naranja --}}
    <header class="bg-[#C62828] px-4 pt-10 pb-16">
        <div class="max-w-md mx-auto">
            <div class="flex items-center gap-3 mb-2">
                <img src="/img/srwok-portada1.jpg" class="w-10 h-10 rounded-full object-cover border-2 border-white/40" alt="Sr WOK"/>
                <span class="text-white/90 font-semibold text-lg tracking-wide">Sr WOK</span>
            </div>
            <h1 class="text-white text-2xl font-bold leading-snug">¿A dónde enviamos<br>tu pedido?</h1>
        </div>
    </header>

    {{-- Card flotante sobre el header --}}
    <main class="flex-1 max-w-md mx-auto w-full px-4 -mt-8 pb-10">

        {{-- Paso 1: Ciudad --}}
        <div x-show="paso === 'ciudad'" x-transition class="bg-white rounded-2xl shadow-lg p-5 mb-4">
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-widest mb-3">Selecciona tu ciudad</p>

            <template x-if="cargandoCiudades">
                <div class="flex items-center gap-3 py-3">
                    <div class="w-5 h-5 border-2 border-[#C62828] border-t-transparent rounded-full animate-spin flex-shrink-0"></div>
                    <span class="text-sm text-gray-400">Cargando ciudades...</span>
                </div>
            </template>

            <template x-if="!cargandoCiudades">
                <div class="space-y-2">
                    <template x-for="c in ciudades" :key="c.codintegracion">
                        <button
                            @click="ciudadSeleccionada = c.codintegracion; nombreCiudad = c.nombre; paso = 'direccion'"
                            class="w-full flex items-center justify-between px-4 py-3.5 rounded-xl border border-gray-100 hover:border-[#C62828] hover:bg-[#FFEBEE] transition-all text-left group"
                        >
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 bg-[#FFCDD2] rounded-full flex items-center justify-center flex-shrink-0">
                                    <svg class="w-4 h-4 text-[#C62828]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    </svg>
                                </div>
                                <span class="font-medium text-gray-800 text-sm" x-text="c.nombre"></span>
                            </div>
                            <svg class="w-4 h-4 text-gray-300 group-hover:text-[#C62828] transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </button>
                    </template>
                    <p x-show="errorCiudad" class="text-red-500 text-xs mt-1 px-1" x-text="errorCiudad"></p>
                </div>
            </template>
        </div>

        {{-- Paso 2: Dirección --}}
        <div x-show="paso === 'direccion'" x-transition class="space-y-3">

            {{-- Volver + ciudad seleccionada --}}
            <div class="bg-white rounded-2xl shadow-lg p-4 flex items-center gap-3">
                <button @click="paso = 'ciudad'" class="w-8 h-8 flex items-center justify-center rounded-full hover:bg-gray-100 flex-shrink-0 transition-colors">
                    <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                </button>
                <div class="w-8 h-8 bg-[#FFCDD2] rounded-full flex items-center justify-center flex-shrink-0">
                    <svg class="w-4 h-4 text-[#C62828]" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <div>
                    <p class="text-xs text-gray-400">Ciudad</p>
                    <p class="font-semibold text-gray-800 text-sm" x-text="nombreCiudad"></p>
                </div>
            </div>

            {{-- Formulario de dirección --}}
            <div class="bg-white rounded-2xl shadow-lg p-5">
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-widest mb-4">Ingresa tu dirección</p>

                {{-- Preview --}}
                <div class="flex items-center gap-2 bg-[#FFEBEE] border border-[#FFCDD2] rounded-xl px-4 py-3 mb-5">
                    <svg class="w-4 h-4 text-[#C62828] flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                    </svg>
                    <span class="text-sm text-gray-600" x-text="direccionPreview || 'Tu dirección aparecerá aquí...'"></span>
                </div>

                <div class="grid grid-cols-2 gap-3 mb-3">
                    <div>
                        <label class="block text-xs text-gray-400 mb-1.5 font-medium">Tipo de vía</label>
                        <select x-model="dir.tipo" @change="actualizarPreview()" class="w-full border border-gray-200 rounded-xl px-3 py-2.5 text-sm text-gray-700 bg-white">
                            <option value="">Selecciona</option>
                            <option value="CLL">Calle</option>
                            <option value="KRA">Carrera</option>
                            <option value="TRAN">Transversal</option>
                            <option value="DIA">Diagonal</option>
                            <option value="AV">Avenida</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs text-gray-400 mb-1.5 font-medium">Número</label>
                        <input x-model="dir.num1" @input="validarCampoDir($event); actualizarPreview()" type="text" maxlength="6" placeholder="Ej: 15" class="w-full border border-gray-200 rounded-xl px-3 py-2.5 text-sm text-gray-700"/>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3 mb-3">
                    <div>
                        <label class="block text-xs text-gray-400 mb-1.5 font-medium">Orientación</label>
                        <select x-model="dir.orient1" @change="actualizarPreview()" class="w-full border border-gray-200 rounded-xl px-3 py-2.5 text-sm text-gray-700 bg-white">
                            <option value=""></option>
                            <option value="N">Norte</option><option value="S">Sur</option>
                            <option value="OE">Oeste</option><option value="ES">Este</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs text-gray-400 mb-1.5 font-medium">Núm. cruce</label>
                        <input x-model="dir.num2" @input="validarCampoDir($event); actualizarPreview()" type="text" maxlength="6" placeholder="Ej: 20" class="w-full border border-gray-200 rounded-xl px-3 py-2.5 text-sm text-gray-700"/>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3 mb-3">
                    <div>
                        <label class="block text-xs text-gray-400 mb-1.5 font-medium">Orientación</label>
                        <select x-model="dir.orient2" @change="actualizarPreview()" class="w-full border border-gray-200 rounded-xl px-3 py-2.5 text-sm text-gray-700 bg-white">
                            <option value=""></option>
                            <option value="N">Norte</option><option value="S">Sur</option>
                            <option value="OE">Oeste</option><option value="ES">Este</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs text-gray-400 mb-1.5 font-medium">Núm. casa</label>
                        <input x-model="dir.num3" @input="validarCampoDir($event); actualizarPreview()" type="text" maxlength="6" placeholder="Ej: 45" class="w-full border border-gray-200 rounded-xl px-3 py-2.5 text-sm text-gray-700"/>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="block text-xs text-gray-400 mb-1.5 font-medium">
                        Barrio <span class="text-gray-300">(opcional)</span>
                        <span class="block text-gray-300 font-normal mt-0.5">Este campo ayuda a mejorar la ubicación de tu pedido</span>
                    </label>
                    <input x-model="barrio" type="text" placeholder="Ej: El Poblado, Chapinero, Laureles..."
                        class="w-full border border-gray-200 rounded-xl px-3 py-2.5 text-sm text-gray-700"/>
                </div>

                <div class="mb-5">
                    <label class="block text-xs text-gray-400 mb-1.5 font-medium">Complemento <span class="text-gray-300">(opcional)</span></label>
                    <input x-model="complemento" type="text" placeholder="Apto 301, Conjunto Los Pinos, Torre B..."
                        class="w-full border border-gray-200 rounded-xl px-3 py-2.5 text-sm text-gray-700"/>
                </div>

                <p x-show="errorDir" class="text-red-500 text-xs mb-3 px-1" x-text="errorDir"></p>

                <button
                    @click="buscarDireccion()"
                    :disabled="buscando || !dir.tipo || !dir.num1"
                    class="btn-rappi w-full text-white font-semibold py-3.5 rounded-xl transition-colors flex items-center justify-center gap-2 text-sm"
                >
                    <template x-if="buscando">
                        <div class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></div>
                    </template>
                    <span x-text="buscando ? 'Verificando cobertura...' : 'Confirmar dirección'"></span>
                </button>
            </div>
        </div>

        {{-- Paso 3: Confirmación en mapa --}}
        <template x-if="paso === 'mapa'">
            <div class="space-y-3">

                {{-- Volver + dirección --}}
                <div class="bg-white rounded-2xl shadow-lg p-4 flex items-center gap-3">
                    <button @click="if(mapaLeaflet){ mapaLeaflet.remove(); mapaLeaflet = null; } paso = 'direccion'" class="w-8 h-8 flex items-center justify-center rounded-full hover:bg-gray-100 flex-shrink-0 transition-colors">
                        <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                        </svg>
                    </button>
                    <div class="w-8 h-8 bg-[#FFCDD2] rounded-full flex items-center justify-center flex-shrink-0">
                        <svg class="w-4 h-4 text-[#C62828]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-xs text-gray-400">Dirección de entrega</p>
                        <p class="font-semibold text-gray-800 text-sm" x-text="direccionPreview + (barrio.trim() ? ', ' + barrio.trim() : '')"></p>
                    </div>
                </div>

                {{-- Mapa --}}
                <div class="bg-white rounded-2xl shadow-lg overflow-hidden">
                    <div style="position: relative; height: 260px" x-init="$nextTick(() => iniciarMapa())">
                        <div id="mapa-ubicacion" style="height: 100%"></div>
                        {{-- Pin fijo en el centro --}}
                        <div style="position:absolute;top:50%;left:50%;transform:translate(-50%,-100%);z-index:1000;pointer-events:none">
                            <svg width="30" height="36" viewBox="0 0 30 36" fill="none">
                                <path d="M15 0C6.716 0 0 6.716 0 15c0 10.5 15 21 15 21s15-10.5 15-21C30 6.716 23.284 0 15 0z" fill="#C62828"/>
                                <circle cx="15" cy="15" r="5.5" fill="white"/>
                            </svg>
                        </div>
                        {{-- Sombra del pin --}}
                        <div style="position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);z-index:999;width:10px;height:5px;background:rgba(0,0,0,0.25);border-radius:50%;filter:blur(2px);pointer-events:none"></div>
                    </div>
                    <p class="text-xs text-gray-400 text-center py-2">Arrastra el mapa para ajustar el punto de entrega</p>
                    <div class="px-4 pb-4" x-show="geocoding && geocoding.aviso">
                        <div class="flex gap-2 items-start bg-amber-50 border border-amber-200 rounded-xl px-3 py-2.5">
                            <svg class="w-4 h-4 text-amber-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                            <p class="text-xs text-amber-700" x-text="geocoding && geocoding.aviso"></p>
                        </div>
                    </div>
                </div>

                {{-- Botón continuar --}}
                <button
                    @click="window.location.href = '{{ route('menu') }}'"
                    class="btn-rappi w-full text-white font-semibold py-3.5 rounded-xl transition-colors flex items-center justify-center gap-2 text-sm"
                >
                    <span>Ir al menú</span>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </button>

            </div>
        </template>

    </main>

    {{-- Modal sin cobertura --}}
    <div x-show="sinCobertura" x-transition class="fixed inset-0 z-50 flex items-end bg-black/50">
        <div class="bg-white rounded-t-3xl w-full p-6 max-w-lg mx-auto">
            <div class="w-10 h-1 bg-gray-200 rounded-full mx-auto mb-5"></div>
            <div class="text-center mb-5">
                <div class="w-16 h-16 bg-red-50 rounded-full flex items-center justify-center mx-auto mb-3">
                    <svg class="w-8 h-8 text-[#C62828]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                    </svg>
                </div>
                <h3 class="text-lg font-bold text-gray-900">Sin cobertura en tu zona</h3>
                <p class="text-sm text-gray-500 mt-1">Pero puedes llamarnos directamente</p>
            </div>
            <div class="grid grid-cols-2 gap-2 mb-4">
                @foreach([['Armenia','6067359868'],['Bogotá','6017444424'],['Cali','6026959570'],['Ibagué','6082771250'],['Manizales','6068918899'],['Medellín','6046044949'],['Palmira','6022868970'],['Pereira','6063400551'],['Popayán','6028368090'],['Tuluá','6022359880']] as [$c,$t])
                <a href="tel:{{ $t }}" class="flex items-center gap-2 bg-gray-50 hover:bg-gray-100 rounded-xl px-3 py-2.5 transition-colors">
                    <span class="text-base">📞</span>
                    <span class="text-xs font-medium text-gray-700">{{ $c }}</span>
                </a>
                @endforeach
            </div>
            <button @click="sinCobertura = false" class="w-full border-2 border-gray-200 text-gray-700 font-semibold py-3 rounded-xl text-sm hover:bg-gray-50 transition-colors">
                Intentar con otra dirección
            </button>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
function homeApp() {
    return {
        paso: 'ciudad', ciudades: [], cargandoCiudades: true,
        ciudadSeleccionada: '', nombreCiudad: '', errorCiudad: '',
        dir: { tipo: '', num1: '', orient1: '', num2: '', orient2: '', num3: '' },
        barrio: '',
        complemento: '',
        direccionPreview: '', buscando: false, sinCobertura: false, errorDir: '',
        geocoding: null, mapaLeaflet: null, coordenadasSeleccionadas: null,

        async cargarCiudades() {
            try {
                const res = await fetch('{{ route("api.ciudades") }}', {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content, 'Content-Type': 'application/json' },
                    body: JSON.stringify({})
                });
                this.ciudades = await res.json();
            } catch (e) {
                this.errorCiudad = 'Error cargando ciudades.';
            } finally {
                this.cargandoCiudades = false;
            }
        },

        actualizarPreview() {
            const { tipo, num1, orient1, num2, orient2, num3 } = this.dir;
            this.direccionPreview = [tipo, num1, orient1, num2, orient2, num3].filter(Boolean).join(' ');
        },

        validarCampoDir(e) {
            const val = e.target.value.toUpperCase();
            const orientaciones = ['N','S','O','E','OE','ES','NOR','SUR','OES'];
            if (orientaciones.some(o => val.includes(o) && !val.includes('BIS'))) {
                e.target.value = '';
                this.errorDir = 'Usa los selectores de orientación';
                setTimeout(() => this.errorDir = '', 3000);
            }
        },

        async geocodificar() {
            const deptos = {
                'Armenia': 'Quindío', 'Bogotá': 'Bogotá D.C.', 'Cali': 'Valle del Cauca',
                'Ibagué': 'Tolima', 'Manizales': 'Caldas', 'Medellín': 'Antioquia',
                'Palmira': 'Valle del Cauca', 'Pereira': 'Risaralda',
                'Popayán': 'Cauca', 'Tuluá': 'Valle del Cauca',
            };
            const depto = deptos[this.nombreCiudad] || '';
            const partes = [this.direccionPreview, this.barrio.trim(), this.nombreCiudad, depto, 'Colombia'].filter(Boolean);
            try {
                const res = await fetch('{{ route("api.geocodificar") }}', {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content, 'Content-Type': 'application/json' },
                    body: JSON.stringify({ direccion: partes.join(', ') })
                });
                this.geocoding = await res.json();
            } catch (e) {
                this.geocoding = null;
            }
        },

        iniciarMapa() {
            if (this.mapaLeaflet) return;
            const centrosCiudad = {
                'Armenia': [4.534, -75.681], 'Bogotá': [4.624, -74.064], 'Cali': [3.451, -76.532],
                'Ibagué': [4.438, -75.232], 'Manizales': [5.068, -75.517], 'Medellín': [6.244, -75.574],
                'Palmira': [3.540, -76.304], 'Pereira': [4.814, -75.696],
                'Popayán': [2.441, -76.607], 'Tuluá': [4.086, -76.196],
            };
            const lat = this.geocoding?.lat || (centrosCiudad[this.nombreCiudad] || [4.624, -74.064])[0];
            const lng = this.geocoding?.lng || (centrosCiudad[this.nombreCiudad] || [4.624, -74.064])[1];
            const zoom = this.geocoding?.lat ? 16 : 13;
            this.coordenadasSeleccionadas = { lat, lng };
            this.mapaLeaflet = L.map('mapa-ubicacion').setView([lat, lng], zoom);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>'
            }).addTo(this.mapaLeaflet);
            this.mapaLeaflet.on('moveend', () => {
                const c = this.mapaLeaflet.getCenter();
                this.coordenadasSeleccionadas = { lat: c.lat, lng: c.lng };
            });
        },

        async buscarDireccion() {
            this.errorDir = '';
            const { tipo, num1, orient1, num2, orient2, num3 } = this.dir;
            const partes = [tipo, num1, orient1 || '', num2];
            if (orient2) partes.push(orient2);
            if (num3) partes.push(num3);
            const direccion = partes.join(' ');
            if (!tipo || !num1) { this.errorDir = 'Ingresa al menos el tipo de vía y número'; return; }
            this.buscando = true;
            try {
                const res = await fetch('{{ route("api.validar-direccion") }}', {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content, 'Content-Type': 'application/json' },
                    body: JSON.stringify({ ciudad: this.ciudadSeleccionada, direccion })
                });
                const data = await res.json();
                if (parseInt(data) === -1 || data === -1) {
                    this.sinCobertura = true;
                } else {
                    localStorage.setItem('punto', data);
                    localStorage.setItem('ciudad', this.ciudadSeleccionada);
                    localStorage.setItem('nombreciudad', this.nombreCiudad);
                    localStorage.setItem('direccion', direccion);
                    localStorage.setItem('barrio', this.barrio.trim());
                    localStorage.setItem('complemento', this.complemento.trim());
                    await this.geocodificar();
                    this.paso = 'mapa';
                }
            } catch (e) {
                this.errorDir = 'Error al verificar la dirección.';
            } finally {
                this.buscando = false;
            }
        }
    }
}
</script>
@endpush
