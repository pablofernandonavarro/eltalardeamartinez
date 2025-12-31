<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    @include('partials.head')
    @php
        $title = 'El Talar de Martínez - Complejo Habitacional';
    @endphp
    <style>
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateX(-20px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }
        .animate-fade-in-up {
            animation: fadeInUp 0.8s ease-out;
        }
        .animate-slide-in {
            animation: slideIn 0.6s ease-out;
        }
        .hero-overlay {
            background: linear-gradient(135deg, rgba(24, 24, 27, 0.85) 0%, rgba(39, 39, 42, 0.75) 100%);
        }
        .parallax {
            background-attachment: fixed;
            background-position: center;
            background-repeat: no-repeat;
            background-size: cover;
        }
        /* Menu mobile */
        .mobile-menu {
            transition: transform 0.3s ease, opacity 0.3s ease;
            transform: translateX(100%);
            opacity: 0;
        }
        .mobile-menu.active {
            transform: translateX(0);
            opacity: 1;
        }
        /* Focus visible para accesibilidad */
        a:focus-visible, button:focus-visible {
            outline: 2px solid #f59e0b;
            outline-offset: 2px;
        }
        /* Smooth scroll */
        html {
            scroll-behavior: smooth;
        }
        [x-cloak] {
            display: none !important;
        }
        /* Button hover effects mejorados */
        .btn-primary {
            position: relative;
            overflow: hidden;
            transition: all 0.3s ease;
        }
        .btn-primary::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 0;
            height: 0;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.2);
            transform: translate(-50%, -50%);
            transition: width 0.6s, height 0.6s;
        }
        .btn-primary:hover::before {
            width: 300px;
            height: 300px;
        }
        @media (max-width: 768px) {
            .parallax {
                background-attachment: scroll;
            }
        }
    </style>
</head>
<body class="bg-white dark:bg-zinc-900 text-zinc-900 dark:text-zinc-100 scroll-smooth">
    <!-- Navigation -->
    <nav x-data="{ mobileMenuOpen: false }" @keydown.escape.window="mobileMenuOpen = false" class="fixed top-0 w-full bg-white/95 dark:bg-zinc-900/95 backdrop-blur-md z-50 border-b border-zinc-200 dark:border-zinc-800 shadow-lg transition-all duration-300">
        <div class="max-w-7xl mx-auto px-4 py-3">
            <div class="flex items-center justify-between">
                <!-- Logo -->
                <a href="#inicio" class="text-base md:text-lg lg:text-xl font-bold bg-gradient-to-r from-amber-600 to-orange-600 bg-clip-text text-transparent hover:scale-105 transition-transform whitespace-nowrap flex-shrink-0" aria-label="El Talar de Martínez - Inicio">
                    <span class="hidden lg:inline">El Talar de Martínez</span>
                    <span class="lg:hidden">TM</span>
                </a>
                
                <!-- Desktop Navigation (desde sm en adelante) -->
                <div class="hidden sm:flex items-center gap-4 lg:gap-6 flex-1 lg:flex-initial justify-end">
                    <a href="#inicio" class="text-xs lg:text-sm font-medium text-zinc-700 dark:text-zinc-300 hover:text-amber-600 dark:hover:text-amber-400 transition-colors relative group whitespace-nowrap" aria-label="Ir a Inicio">
                        Inicio
                        <span class="absolute bottom-0 left-0 w-0 h-0.5 bg-amber-600 group-hover:w-full transition-all duration-300"></span>
                    </a>
                    <a href="#novedades" class="text-xs lg:text-sm font-medium text-zinc-700 dark:text-zinc-300 hover:text-amber-600 dark:hover:text-amber-400 transition-colors relative group whitespace-nowrap" aria-label="Ver Novedades">
                        Novedades
                        <span class="absolute bottom-0 left-0 w-0 h-0.5 bg-amber-600 group-hover:w-full transition-all duration-300"></span>
                    </a>
                    <a href="#horarios" class="text-xs lg:text-sm font-medium text-zinc-700 dark:text-zinc-300 hover:text-amber-600 dark:hover:text-amber-400 transition-colors relative group whitespace-nowrap" aria-label="Ver Horarios">
                        Horarios
                        <span class="absolute bottom-0 left-0 w-0 h-0.5 bg-amber-600 group-hover:w-full transition-all duration-300"></span>
                    </a>
                    <a href="#informacion" class="text-xs lg:text-sm font-medium text-zinc-700 dark:text-zinc-300 hover:text-amber-600 dark:hover:text-amber-400 transition-colors relative group whitespace-nowrap" aria-label="Ver Información">
                        Info
                        <span class="absolute bottom-0 left-0 w-0 h-0.5 bg-amber-600 group-hover:w-full transition-all duration-300"></span>
                    </a>
                    <a href="#contacto" class="text-xs lg:text-sm font-medium text-zinc-700 dark:text-zinc-300 hover:text-amber-600 dark:hover:text-amber-400 transition-colors relative group whitespace-nowrap" aria-label="Ir a Contacto">
                        Contacto
                        <span class="absolute bottom-0 left-0 w-0 h-0.5 bg-amber-600 group-hover:w-full transition-all duration-300"></span>
                    </a>
                    
                    <!-- Auth Buttons Desktop -->
                    @guest
                    <div class="flex items-center gap-2 lg:gap-3 ml-4 pl-4 border-l border-zinc-300 dark:border-zinc-700">
                        <a
                            href="{{ route('login') }}"
                            class="px-4 py-2 text-sm font-bold text-amber-700 dark:text-amber-300 bg-amber-100 dark:bg-amber-900/30 hover:bg-amber-200 dark:hover:bg-amber-900/50 rounded-lg transition-all duration-300 border-2 border-amber-300 dark:border-amber-700 whitespace-nowrap shadow-md"
                            aria-label="Iniciar sesión"
                        >
                            Ingresar
                        </a>
                        <a
                            href="{{ route('register') }}"
                            class="px-4 py-2 bg-gradient-to-r from-amber-500 to-orange-600 text-white rounded-lg text-sm font-bold hover:from-amber-600 hover:to-orange-700 shadow-lg hover:shadow-xl transform hover:scale-105 transition-all duration-300 whitespace-nowrap"
                            aria-label="Registrarse"
                        >
                            Registrarse
                        </a>
                    </div>
                    @else
                    <div class="flex items-center gap-3 ml-4 pl-4 border-l border-zinc-300 dark:border-zinc-700">
                        <div class="flex items-center gap-2 text-sm">
                            <div class="flex items-center justify-center w-8 h-8 rounded-full bg-gradient-to-r from-amber-500 to-orange-600 text-white font-bold text-xs">
                                {{ auth()->user()->initials() }}
                            </div>
                            <div class="hidden lg:flex flex-col">
                                <span class="text-xs font-semibold text-zinc-900 dark:text-white">{{ auth()->user()->name }}</span>
                                <span class="text-xs text-zinc-500 dark:text-zinc-400">{{ auth()->user()->role?->label() ?? 'Sin rol asignado' }}</span>
                            </div>
                        </div>
                        <a href="{{ route('dashboard') }}" class="px-4 py-2 bg-gradient-to-r from-amber-500 to-orange-600 text-white rounded-lg text-sm font-bold hover:from-amber-600 hover:to-orange-700 shadow-lg hover:shadow-xl transform hover:scale-105 transition-all duration-300 whitespace-nowrap" aria-label="Ir al dashboard">
                            Mi Portal
                        </a>
                        <form method="POST" action="{{ route('logout') }}" class="inline">
                            @csrf
                            <button type="submit" class="px-3 py-2 text-sm font-medium text-zinc-700 dark:text-zinc-300 hover:text-red-600 dark:hover:text-red-400 transition-colors" aria-label="Cerrar sesión">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                                </svg>
                            </button>
                        </form>
                    </div>
                    @endguest
                </div>

                <!-- Mobile Menu Button -->
                <button @click="mobileMenuOpen = !mobileMenuOpen" class="sm:hidden p-2 rounded-lg hover:bg-zinc-100 dark:hover:bg-zinc-800 transition-colors" aria-label="Abrir menú" aria-expanded="false" :aria-expanded="mobileMenuOpen">
                    <svg x-show="!mobileMenuOpen" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                    <svg x-show="mobileMenuOpen" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true" x-cloak>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <!-- Mobile Menu -->
            <div
                x-show="mobileMenuOpen"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                class="sm:hidden fixed inset-0 top-[56px] bg-white/95 dark:bg-zinc-900/95 backdrop-blur-md"
                role="dialog"
                aria-label="Acceso al portal"
                x-cloak
            >
                <div class="h-full flex flex-col items-center justify-center px-6 py-10">
                    <div class="w-full max-w-sm text-center space-y-6">
                        <h2 class="text-2xl font-bold text-zinc-900 dark:text-white">Acceso al Portal</h2>
                        <p class="text-sm text-zinc-600 dark:text-zinc-400">Ingresá o creá tu cuenta para continuar</p>

                        @guest
                        <div class="grid gap-3">
                            <a
                                href="{{ route('login') }}"
                                x-on:click="mobileMenuOpen = false"
                                class="block px-6 py-4 rounded-xl text-center text-base font-bold text-amber-700 dark:text-amber-300 bg-amber-100 dark:bg-amber-900/30 hover:bg-amber-200 dark:hover:bg-amber-900/50 transition-all shadow-md border-2 border-amber-300 dark:border-amber-700"
                            >
                                🔐 Ingresar
                            </a>
                            <a
                                href="{{ route('register') }}"
                                x-on:click="mobileMenuOpen = false"
                                class="block px-6 py-4 rounded-xl text-center text-base bg-gradient-to-r from-amber-500 to-orange-600 text-white font-bold hover:from-amber-600 hover:to-orange-700 transition-all shadow-lg"
                            >
                                ✨ Registrarse
                            </a>
                        </div>
                        @else
                        <div class="grid gap-3">
                            <div class="flex items-center gap-3 px-6 py-4 rounded-xl bg-zinc-100 dark:bg-zinc-800 border-2 border-zinc-200 dark:border-zinc-700">
                                <div class="flex items-center justify-center w-12 h-12 rounded-full bg-gradient-to-r from-amber-500 to-orange-600 text-white font-bold">
                                    {{ auth()->user()->initials() }}
                                </div>
                                <div class="flex-1 text-left">
                                    <p class="font-bold text-zinc-900 dark:text-white">{{ auth()->user()->name }}</p>
                                    <p class="text-xs text-zinc-600 dark:text-zinc-400">{{ auth()->user()->email }}</p>
                                    <p class="text-xs text-amber-600 dark:text-amber-400 font-semibold">{{ auth()->user()->role?->label() ?? 'Sin rol asignado' }}</p>
                                </div>
                            </div>
                            <a href="{{ route('dashboard') }}" class="block px-6 py-4 rounded-xl text-center text-base bg-gradient-to-r from-amber-500 to-orange-600 text-white font-bold hover:from-amber-600 hover:to-orange-700 transition-all shadow-lg">
                                🏠 Mi Portal
                            </a>
                            <form method="POST" action="{{ route('logout') }}" class="w-full">
                                @csrf
                                <button type="submit" class="w-full px-6 py-3 rounded-xl text-center text-sm font-semibold text-red-600 dark:text-red-400 bg-red-50 dark:bg-red-900/20 hover:bg-red-100 dark:hover:bg-red-900/30 transition-colors border-2 border-red-200 dark:border-red-800">
                                    Cerrar Sesión
                                </button>
                            </form>
                        </div>
                        @endguest

                        <button type="button" @click="mobileMenuOpen = false" class="w-full px-6 py-3 rounded-xl text-center text-sm font-semibold text-zinc-700 dark:text-zinc-300 bg-zinc-100 dark:bg-zinc-800 hover:bg-zinc-200 dark:hover:bg-zinc-700 transition-colors">
                            Cerrar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section id="inicio" class="relative min-h-screen flex items-center justify-center pt-24 pb-12 overflow-hidden">
        <!-- Background Image -->
        <div class="absolute inset-0 bg-cover bg-center bg-no-repeat" style="background-image: url('https://lh3.googleusercontent.com/p/AF1QipPtJDE7Eu24Dsiib9i3qJfCHRnA47PWBnhX5QS-=w1920-h1080-k-no');" role="img" aria-label="Vista del complejo habitacional El Talar de Martínez"></div>
        <!-- Overlay sutil para mejor legibilidad del texto -->
        <div class="absolute inset-0 bg-gradient-to-b from-black/30 via-black/20 to-black/40 dark:from-black/50 dark:via-black/40 dark:to-black/60"></div>
        
        <div class="relative z-10 container mx-auto px-6 py-12 text-center">
            <div class="animate-fade-in-up max-w-5xl mx-auto">
                <!-- Badge superior -->
                <div class="inline-flex items-center gap-2 px-5 py-3 mb-6 bg-white/95 dark:bg-zinc-800/95 backdrop-blur-md rounded-full shadow-2xl border-2 border-amber-300 dark:border-amber-600">
                    <span class="flex h-2 w-2">
                        <span class="animate-ping absolute inline-flex h-2 w-2 rounded-full bg-green-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2 w-2 bg-green-500"></span>
                    </span>
                    <span class="text-sm font-bold text-zinc-800 dark:text-white">Portal para Propietarios e Inquilinos</span>
                </div>

                <h1 class="text-5xl sm:text-6xl lg:text-7xl font-bold mb-6 text-white drop-shadow-2xl leading-tight">
                    Bienvenido al Portal de<br>
                    <span class="bg-gradient-to-r from-amber-400 via-orange-400 to-orange-500 bg-clip-text text-transparent drop-shadow-lg">El Talar de Martínez</span>
                </h1>
                <p class="text-lg sm:text-xl lg:text-2xl text-white dark:text-zinc-100 mb-8 max-w-3xl mx-auto leading-relaxed drop-shadow-lg">
                    Tu espacio digital para estar al día con todas las novedades del complejo, consultar horarios de amenidades, acceder a servicios y gestionar tu residencia.
                </p>
            </div>
        </div>

        <!-- Scroll indicator -->
        <div class="absolute bottom-8 left-1/2 transform -translate-x-1/2 animate-bounce">
            <a href="#novedades" class="flex flex-col items-center gap-2 px-4 py-2 bg-white/90 dark:bg-zinc-800/90 backdrop-blur-sm rounded-full shadow-lg hover:bg-amber-500 hover:text-white transition-all duration-300 border border-zinc-200 dark:border-zinc-700" aria-label="Desplazarse hacia abajo">
                <span class="text-xs font-bold text-zinc-800 dark:text-white">Ver Novedades</span>
                <svg class="w-5 h-5 text-zinc-800 dark:text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"/>
                </svg>
            </a>
        </div>
    </section>

    <!-- Sección de Novedades -->
    <section id="novedades" class="py-24 px-6 bg-gradient-to-b from-white to-zinc-50 dark:from-zinc-900 dark:to-zinc-950">
        <div class="container mx-auto max-w-7xl">
            <div class="text-center mb-16">
                <span class="inline-block px-4 py-2 mb-4 text-sm font-semibold text-amber-600 dark:text-amber-400 bg-amber-100 dark:bg-amber-900/30 rounded-full">Últimas Novedades</span>
                <h2 class="text-5xl md:text-6xl font-bold mb-4 text-zinc-900 dark:text-white">
                    Novedades del Complejo
                </h2>
                <p class="text-xl text-zinc-600 dark:text-zinc-400 max-w-2xl mx-auto">
                    Mantenete informado sobre las últimas noticias, eventos y actualizaciones
                </p>
            </div>

            <div class="grid md:grid-cols-3 gap-8">
                @forelse ($news as $item)
                    @php
                        $colors = $item->getColorClasses();
                    @endphp
                    <article class="group bg-white dark:bg-zinc-800 rounded-2xl shadow-lg hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-2 overflow-hidden">
                        <div class="relative">
                            @if($item->is_featured)
                                <div class="absolute top-4 left-4 z-10">
                                    <span class="inline-block px-3 py-1 bg-red-500 text-white text-xs font-bold rounded-full">NUEVO</span>
                                </div>
                            @endif
                            <div class="h-48 {{ $colors['bg'] }} flex items-center justify-center">
                                {!! $item->getIconSvg() !!}
                            </div>
                        </div>
                        <div class="p-6">
                            <div class="flex items-center gap-2 text-sm text-zinc-500 dark:text-zinc-400 mb-3">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                                <time datetime="{{ $item->event_date->format('Y-m-d') }}">{{ $item->event_date->format('d \\d\\e F, Y') }}</time>
                            </div>
                            <h3 class="text-xl font-bold text-zinc-900 dark:text-white mb-2">{{ $item->title }}</h3>
                            <p class="text-zinc-600 dark:text-zinc-400 mb-4">
                                {{ $item->description }}
                            </p>
                        </div>
                    </article>
                @empty
                    <div class="col-span-3 text-center py-12">
                        <svg class="w-16 h-16 mx-auto text-zinc-400 dark:text-zinc-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M12 12h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <p class="text-xl text-zinc-600 dark:text-zinc-400">No hay novedades disponibles en este momento.</p>
                    </div>
                @endforelse
            </div>

            <div class="text-center mt-12">
                @guest
                <a href="{{ route('login') }}" class="inline-flex items-center gap-2 px-8 py-4 bg-gradient-to-r from-amber-500 to-orange-600 text-white rounded-xl text-lg font-semibold hover:from-amber-600 hover:to-orange-700 shadow-xl hover:shadow-2xl transform hover:-translate-y-1 transition-all duration-300">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
                    </svg>
                    Ingresar para ver todas las novedades
                </a>
                @else
                <a href="{{ route('admin.dashboard') }}" class="inline-flex items-center gap-2 px-8 py-4 bg-gradient-to-r from-amber-500 to-orange-600 text-white rounded-xl text-lg font-semibold hover:from-amber-600 hover:to-orange-700 shadow-xl hover:shadow-2xl transform hover:-translate-y-1 transition-all duration-300">
                    Ver todas las novedades
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </a>
                @endguest
            </div>
        </div>
    </section>

    <!-- Horarios y Amenidades Section -->
    <section id="horarios" class="py-24 px-6 bg-white dark:bg-zinc-900">
        <div class="container mx-auto max-w-7xl">
            <div class="text-center mb-16">
                <span class="inline-block px-4 py-2 mb-4 text-sm font-semibold text-blue-600 dark:text-blue-400 bg-blue-100 dark:bg-blue-900/30 rounded-full">Información de Uso</span>
                <h2 class="text-5xl md:text-6xl font-bold mb-4 text-zinc-900 dark:text-white">
                    Horarios de Amenidades
                </h2>
                <p class="text-xl text-zinc-600 dark:text-zinc-400 max-w-2xl mx-auto">
                    Consultá los horarios de uso de las instalaciones del complejo
                </p>
            </div>

            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8 mb-12">
                <!-- Pileta -->
                <div class="group bg-gradient-to-br from-blue-50 to-cyan-50 dark:from-blue-900/20 dark:to-cyan-900/20 rounded-2xl shadow-lg hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-2 overflow-hidden border border-blue-200 dark:border-blue-800">
                    <div class="p-8">
                        <div class="flex items-center gap-4 mb-6">
                            <div class="w-16 h-16 bg-blue-500 rounded-full flex items-center justify-center shadow-lg">
                                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"/>
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-2xl font-bold text-zinc-900 dark:text-white">Pileta</h3>
                                <p class="text-sm text-blue-600 dark:text-blue-400 font-semibold">Temporada de Verano</p>
                            </div>
                        </div>
                        <div class="space-y-3 text-zinc-700 dark:text-zinc-300">
                            <div class="flex items-start gap-3">
                                <svg class="w-5 h-5 text-blue-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <div>
                                    <p class="font-semibold text-zinc-900 dark:text-white">Lunes a Viernes</p>
                                    <p class="text-sm">9:00 - 13:00 hs / 15:00 - 22:00 hs</p>
                                </div>
                            </div>
                            <div class="flex items-start gap-3">
                                <svg class="w-5 h-5 text-blue-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <div>
                                    <p class="font-semibold text-zinc-900 dark:text-white">Fines de Semana</p>
                                    <p class="text-sm">10:00 - 20:00 hs</p>
                                </div>
                            </div>
                            <div class="mt-4 p-3 bg-blue-100 dark:bg-blue-900/40 rounded-lg">
                                <p class="text-xs text-blue-800 dark:text-blue-200">
                                    <strong>Importante:</strong> Menores de 12 años deben estar acompañados por un adulto
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Gimnasio -->
                <div class="group bg-gradient-to-br from-orange-50 to-red-50 dark:from-orange-900/20 dark:to-red-900/20 rounded-2xl shadow-lg hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-2 overflow-hidden border border-orange-200 dark:border-orange-800">
                    <div class="p-8">
                        <div class="flex items-center gap-4 mb-6">
                            <div class="w-16 h-16 bg-orange-500 rounded-full flex items-center justify-center shadow-lg">
                                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 15a4 4 0 004 4h9a5 5 0 10-.1-9.999 5.002 5.002 0 10-9.78 2.096A4.001 4.001 0 003 15z"/>
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-2xl font-bold text-zinc-900 dark:text-white">Gimnasio</h3>
                                <p class="text-sm text-orange-600 dark:text-orange-400 font-semibold">Todo el Año</p>
                            </div>
                        </div>
                        <div class="space-y-3 text-zinc-700 dark:text-zinc-300">
                            <div class="flex items-start gap-3">
                                <svg class="w-5 h-5 text-orange-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <div>
                                    <p class="font-semibold text-zinc-900 dark:text-white">Todos los días</p>
                                    <p class="text-sm">7:00 - 23:00 hs</p>
                                </div>
                            </div>
                            <div class="mt-4 p-3 bg-orange-100 dark:bg-orange-900/40 rounded-lg">
                                <p class="text-xs text-orange-800 dark:text-orange-200">
                                    <strong>Recordatorio:</strong> Uso exclusivo para mayores de 16 años. Traer toalla.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- SUM (Salón de Usos Múltiples) -->
                <div class="group bg-gradient-to-br from-green-50 to-emerald-50 dark:from-green-900/20 dark:to-emerald-900/20 rounded-2xl shadow-lg hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-2 overflow-hidden border border-green-200 dark:border-green-800">
                    <div class="p-8">
                        <div class="flex items-center gap-4 mb-6">
                            <div class="w-16 h-16 bg-green-500 rounded-full flex items-center justify-center shadow-lg">
                                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-2xl font-bold text-zinc-900 dark:text-white">SUM</h3>
                                <p class="text-sm text-green-600 dark:text-green-400 font-semibold">Con Reserva Previa</p>
                            </div>
                        </div>
                        <div class="space-y-3 text-zinc-700 dark:text-zinc-300">
                            <div class="flex items-start gap-3">
                                <svg class="w-5 h-5 text-green-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <div>
                                    <p class="font-semibold text-zinc-900 dark:text-white">Sábados y Domingos</p>
                                    <p class="text-sm">Horario flexible según reserva</p>
                                </div>
                            </div>
                            <div class="mt-4 p-3 bg-green-100 dark:bg-green-900/40 rounded-lg">
                                <p class="text-xs text-green-800 dark:text-green-200">
                                    <strong>Reservas:</strong> Con 15 días de anticipación en administración
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Espacios Comunes -->
                <div class="group bg-gradient-to-br from-purple-50 to-pink-50 dark:from-purple-900/20 dark:to-pink-900/20 rounded-2xl shadow-lg hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-2 overflow-hidden border border-purple-200 dark:border-purple-800">
                    <div class="p-8">
                        <div class="flex items-center gap-4 mb-6">
                            <div class="w-16 h-16 bg-purple-500 rounded-full flex items-center justify-center shadow-lg">
                                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-2xl font-bold text-zinc-900 dark:text-white">Espacios Comunes</h3>
                                <p class="text-sm text-purple-600 dark:text-purple-400 font-semibold">Acceso Libre</p>
                            </div>
                        </div>
                        <div class="space-y-3 text-zinc-700 dark:text-zinc-300">
                            <div class="flex items-start gap-3">
                                <svg class="w-5 h-5 text-purple-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                <p class="text-sm">Quincho y parrillas</p>
                            </div>
                            <div class="flex items-start gap-3">
                                <svg class="w-5 h-5 text-purple-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                <p class="text-sm">Área de juegos infantiles</p>
                            </div>
                            <div class="flex items-start gap-3">
                                <svg class="w-5 h-5 text-purple-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                <p class="text-sm">Senderos peatonales</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Seguridad -->
                <div class="group bg-gradient-to-br from-red-50 to-rose-50 dark:from-red-900/20 dark:to-rose-900/20 rounded-2xl shadow-lg hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-2 overflow-hidden border border-red-200 dark:border-red-800">
                    <div class="p-8">
                        <div class="flex items-center gap-4 mb-6">
                            <div class="w-16 h-16 bg-red-500 rounded-full flex items-center justify-center shadow-lg">
                                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-2xl font-bold text-zinc-900 dark:text-white">Seguridad</h3>
                                <p class="text-sm text-red-600 dark:text-red-400 font-semibold">24 horas</p>
                            </div>
                        </div>
                        <div class="space-y-3 text-zinc-700 dark:text-zinc-300">
                            <div class="flex items-start gap-3">
                                <svg class="w-5 h-5 text-red-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                                </svg>
                                <div>
                                    <p class="font-semibold text-zinc-900 dark:text-white">Emergencias</p>
                                    <p class="text-sm">Interno 100</p>
                                </div>
                            </div>
                            <div class="mt-4 p-3 bg-red-100 dark:bg-red-900/40 rounded-lg">
                                <p class="text-xs text-red-800 dark:text-red-200">
                                    <strong>Control de acceso:</strong> Solicitar autorización previa para visitas
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Administración -->
                <div class="group bg-gradient-to-br from-amber-50 to-yellow-50 dark:from-amber-900/20 dark:to-yellow-900/20 rounded-2xl shadow-lg hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-2 overflow-hidden border border-amber-200 dark:border-amber-800">
                    <div class="p-8">
                        <div class="flex items-center gap-4 mb-6">
                            <div class="w-16 h-16 bg-amber-500 rounded-full flex items-center justify-center shadow-lg">
                                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-2xl font-bold text-zinc-900 dark:text-white">Administración</h3>
                                <p class="text-sm text-amber-600 dark:text-amber-400 font-semibold">Atención al Propietario</p>
                            </div>
                        </div>
                        <div class="space-y-3 text-zinc-700 dark:text-zinc-300">
                            <div class="flex items-start gap-3">
                                <svg class="w-5 h-5 text-amber-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <div>
                                    <p class="font-semibold text-zinc-900 dark:text-white">Lunes a Viernes</p>
                                    <p class="text-sm">9:00 - 17:00 hs</p>
                                </div>
                            </div>
                            <div class="mt-4 p-3 bg-amber-100 dark:bg-amber-900/40 rounded-lg">
                                <p class="text-xs text-amber-800 dark:text-amber-200">
                                    <strong>Contacto:</strong> admin@eltalardemartinez.com
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="text-center">
                @guest
                <a href="{{ route('login') }}" class="inline-flex items-center gap-2 px-8 py-4 bg-gradient-to-r from-amber-500 to-orange-600 text-white rounded-xl text-lg font-semibold hover:from-amber-600 hover:to-orange-700 shadow-xl hover:shadow-2xl transform hover:-translate-y-1 transition-all duration-300">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
                    </svg>
                    Ingresar para solicitar reservas
                </a>
                @else
                <a href="{{ route('admin.dashboard') }}" class="inline-flex items-center gap-2 px-8 py-4 bg-gradient-to-r from-amber-500 to-orange-600 text-white rounded-xl text-lg font-semibold hover:from-amber-600 hover:to-orange-700 shadow-xl hover:shadow-2xl transform hover:-translate-y-1 transition-all duration-300">
                    Solicitar reservas en el portal
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </a>
                @endguest
            </div>
        </div>
    </section>

    <!-- Información para Propietarios e Inquilinos -->
    <section id="informacion" class="py-24 px-6 bg-gradient-to-b from-zinc-50 to-white dark:from-zinc-950 dark:to-zinc-900">
        <div class="container mx-auto max-w-7xl">
            <div class="text-center mb-16">
                <span class="inline-block px-4 py-2 mb-4 text-sm font-semibold text-indigo-600 dark:text-indigo-400 bg-indigo-100 dark:bg-indigo-900/30 rounded-full">Guía de Residentes</span>
                <h2 class="text-5xl md:text-6xl font-bold mb-4 text-zinc-900 dark:text-white">
                    Información para Residentes
                </h2>
                <p class="text-xl text-zinc-600 dark:text-zinc-400 max-w-2xl mx-auto">
                    Todo lo que necesitás saber como propietario o inquilino del complejo
                </p>
            </div>

            <div class="grid md:grid-cols-2 gap-8 mb-12">
                <!-- Para Propietarios -->
                <div class="bg-white dark:bg-zinc-800 rounded-2xl shadow-xl overflow-hidden">
                    <div class="bg-gradient-to-r from-amber-500 to-orange-600 p-6">
                        <div class="flex items-center gap-4">
                            <div class="w-14 h-14 bg-white/20 backdrop-blur-sm rounded-full flex items-center justify-center">
                                <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                                </svg>
                            </div>
                            <h3 class="text-3xl font-bold text-white">Para Propietarios</h3>
                        </div>
                    </div>
                    <div class="p-8 space-y-6">
                        <div class="flex items-start gap-4">
                            <div class="w-10 h-10 bg-amber-100 dark:bg-amber-900/30 rounded-lg flex items-center justify-center flex-shrink-0">
                                <svg class="w-5 h-5 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                            </div>
                            <div>
                                <h4 class="font-bold text-zinc-900 dark:text-white mb-2">Expensas y Pagos</h4>
                                <p class="text-zinc-600 dark:text-zinc-400 text-sm leading-relaxed">Las expensas vencen el día 10 de cada mes. Pagá online desde tu portal o en nuestras oficinas. Consultá el detalle de gastos mensuales.</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-4">
                            <div class="w-10 h-10 bg-amber-100 dark:bg-amber-900/30 rounded-lg flex items-center justify-center flex-shrink-0">
                                <svg class="w-5 h-5 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                                </svg>
                            </div>
                            <div>
                                <h4 class="font-bold text-zinc-900 dark:text-white mb-2">Asambleas</h4>
                                <p class="text-zinc-600 dark:text-zinc-400 text-sm leading-relaxed">Participá de las asambleas ordinarias y extraordinarias. Te notificaremos con 15 días de anticipación a tu correo registrado.</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-4">
                            <div class="w-10 h-10 bg-amber-100 dark:bg-amber-900/30 rounded-lg flex items-center justify-center flex-shrink-0">
                                <svg class="w-5 h-5 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                            </div>
                            <div>
                                <h4 class="font-bold text-zinc-900 dark:text-white mb-2">Modificaciones</h4>
                                <p class="text-zinc-600 dark:text-zinc-400 text-sm leading-relaxed">Cualquier modificación en tu unidad debe ser aprobada por el consorcio. Enviá tu proyecto a administración con 30 días de anticipación.</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-4">
                            <div class="w-10 h-10 bg-amber-100 dark:bg-amber-900/30 rounded-lg flex items-center justify-center flex-shrink-0">
                                <svg class="w-5 h-5 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                            <div>
                                <h4 class="font-bold text-zinc-900 dark:text-white mb-2">Seguros</h4>
                                <p class="text-zinc-600 dark:text-zinc-400 text-sm leading-relaxed">El complejo cuenta con seguro de consorcio. Te recomendamos contratar un seguro individual para tu unidad y contenido.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Para Inquilinos -->
                <div class="bg-white dark:bg-zinc-800 rounded-2xl shadow-xl overflow-hidden">
                    <div class="bg-gradient-to-r from-blue-500 to-indigo-600 p-6">
                        <div class="flex items-center gap-4">
                            <div class="w-14 h-14 bg-white/20 backdrop-blur-sm rounded-full flex items-center justify-center">
                                <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                                </svg>
                            </div>
                            <h3 class="text-3xl font-bold text-white">Para Inquilinos</h3>
                        </div>
                    </div>
                    <div class="p-8 space-y-6">
                        <div class="flex items-start gap-4">
                            <div class="w-10 h-10 bg-blue-100 dark:bg-blue-900/30 rounded-lg flex items-center justify-center flex-shrink-0">
                                <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/>
                                </svg>
                            </div>
                            <div>
                                <h4 class="font-bold text-zinc-900 dark:text-white mb-2">Acceso a Amenidades</h4>
                                <p class="text-zinc-600 dark:text-zinc-400 text-sm leading-relaxed">Como inquilino, tenés acceso completo a todas las amenidades del complejo. Registrate en el portal para gestionar reservas.</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-4">
                            <div class="w-10 h-10 bg-blue-100 dark:bg-blue-900/30 rounded-lg flex items-center justify-center flex-shrink-0">
                                <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                </svg>
                            </div>
                            <div>
                                <h4 class="font-bold text-zinc-900 dark:text-white mb-2">Accesos y Seguridad</h4>
                                <p class="text-zinc-600 dark:text-zinc-400 text-sm leading-relaxed">Recibí tus credenciales de acceso en la administración. Informá a seguridad sobre tus visitas frecuentes para agilizar ingresos.</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-4">
                            <div class="w-10 h-10 bg-blue-100 dark:bg-blue-900/30 rounded-lg flex items-center justify-center flex-shrink-0">
                                <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                </svg>
                            </div>
                            <div>
                                <h4 class="font-bold text-zinc-900 dark:text-white mb-2">Reglamento</h4>
                                <p class="text-zinc-600 dark:text-zinc-400 text-sm leading-relaxed">Es importante conocer y respetar el reglamento interno. Consultalo en el portal o solicita una copia en administración.</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-4">
                            <div class="w-10 h-10 bg-blue-100 dark:bg-blue-900/30 rounded-lg flex items-center justify-center flex-shrink-0">
                                <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z"/>
                                </svg>
                            </div>
                            <div>
                                <h4 class="font-bold text-zinc-900 dark:text-white mb-2">Mantenimiento y Reparaciones</h4>
                                <p class="text-zinc-600 dark:text-zinc-400 text-sm leading-relaxed">Para problemas en tu unidad, contactá primero al propietario. Para áreas comunes, reportá a través del portal o directamente a administración.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Reglamento Destacado -->
            <div class="bg-gradient-to-r from-zinc-900 to-zinc-800 dark:from-zinc-800 dark:to-zinc-900 rounded-2xl shadow-2xl p-8 md:p-12 text-white">
                <div class="flex flex-col md:flex-row items-center gap-8">
                    <div class="w-20 h-20 bg-amber-500 rounded-2xl flex items-center justify-center flex-shrink-0 transform rotate-3">
                        <svg class="w-10 h-10 text-white transform -rotate-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                    <div class="flex-1 text-center md:text-left">
                        <h3 class="text-3xl font-bold mb-3">Reglamento de Convivencia</h3>
                        <p class="text-zinc-300 text-lg leading-relaxed mb-6">
                            Descargá el reglamento completo para conocer todas las normas de convivencia, uso de espacios comunes, horarios, y derechos y obligaciones de propietarios e inquilinos.
                        </p>
                        <div class="flex flex-col sm:flex-row gap-4 justify-center md:justify-start">
                            <a href="{{ route('regulation.download') }}" class="inline-flex items-center justify-center gap-2 px-6 py-3 bg-amber-500 hover:bg-amber-600 text-white font-semibold rounded-lg transition-colors">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                </svg>
                                Descargar Reglamento
                            </a>
                            <a href="#contacto" class="inline-flex items-center justify-center gap-2 px-6 py-3 bg-white/10 hover:bg-white/20 backdrop-blur-sm text-white font-semibold rounded-lg border border-white/20 transition-colors">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                Consultas
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Servicios Section -->
    <section id="servicios" class="py-24 px-6 bg-white dark:bg-zinc-900">
        <div class="container mx-auto max-w-7xl">
            <div class="text-center mb-16">
                <span class="inline-block px-4 py-2 mb-4 text-sm font-semibold text-amber-600 dark:text-amber-400 bg-amber-100 dark:bg-amber-900/30 rounded-full">Servicios Disponibles</span>
                <h2 class="text-5xl md:text-6xl font-bold mb-4 text-zinc-900 dark:text-white">
                    Servicios del Complejo
                </h2>
                <p class="text-xl text-zinc-600 dark:text-zinc-400 max-w-2xl mx-auto">
                    Accedé a todos los servicios y amenities que tenemos para vos
                </p>
            </div>
            <div class="grid md:grid-cols-3 gap-8" role="list">
                <article class="group relative overflow-hidden rounded-2xl bg-white dark:bg-zinc-800 shadow-lg hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-2 focus-within:ring-2 focus-within:ring-amber-500" role="listitem">
                    <div class="aspect-video overflow-hidden">
                        <img src="https://images.unsplash.com/photo-1600607687939-ce8a6c25118c?ixlib=rb-4.0.3&auto=format&fit=crop&w=1000&q=80" 
                             alt="Interior moderno con diseño contemporáneo" 
                             loading="lazy"
                             class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                    </div>
                    <div class="p-6">
                        <div class="flex items-center gap-2 mb-3">
                            <svg class="w-6 h-6 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                            </svg>
                            <h3 class="text-2xl font-bold text-zinc-900 dark:text-white">Diseño Moderno</h3>
                        </div>
                        <p class="text-zinc-600 dark:text-zinc-400 leading-relaxed">
                            Arquitectura contemporánea con acabados de primera calidad y espacios amplios diseñados para tu comodidad.
                        </p>
                    </div>
                </article>
                <article class="group relative overflow-hidden rounded-2xl bg-white dark:bg-zinc-800 shadow-lg hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-2 focus-within:ring-2 focus-within:ring-amber-500" role="listitem">
                    <div class="aspect-video overflow-hidden">
                        <img src="https://images.unsplash.com/photo-1441974231531-c6227db76b6e?ixlib=rb-4.0.3&auto=format&fit=crop&w=1000&q=80" 
                             alt="Jardines y espacios verdes del complejo" 
                             loading="lazy"
                             class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                    </div>
                    <div class="p-6">
                        <div class="flex items-center gap-2 mb-3">
                            <svg class="w-6 h-6 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <h3 class="text-2xl font-bold text-zinc-900 dark:text-white">Áreas Verdes</h3>
                        </div>
                        <p class="text-zinc-600 dark:text-zinc-400 leading-relaxed">
                            Espacios comunes con jardines cuidados y áreas recreativas para disfrutar en familia.
                        </p>
                    </div>
                </article>
                <div class="group relative overflow-hidden rounded-2xl bg-white dark:bg-zinc-800 shadow-lg hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-2">
                    <div class="aspect-video overflow-hidden">
                        <img src="https://images.unsplash.com/photo-1558618666-fcd25c85cd64?ixlib=rb-4.0.3&auto=format&fit=crop&w=1000&q=80" 
                             alt="Seguridad" 
                             class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                    </div>
                    <div class="p-6">
                        <h3 class="text-2xl font-bold mb-3 text-zinc-900 dark:text-white">Seguridad 24/7</h3>
                        <p class="text-zinc-600 dark:text-zinc-400 leading-relaxed">
                            Control de acceso y vigilancia permanente para garantizar tu tranquilidad y la de tu familia.
                        </p>
                    </div>
                </div>
                <div class="group relative overflow-hidden rounded-2xl bg-white dark:bg-zinc-800 shadow-lg hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-2">
                    <div class="aspect-video overflow-hidden">
                        <img src="https://images.unsplash.com/photo-1506905925346-21bda4d32df4?ixlib=rb-4.0.3&auto=format&fit=crop&w=1000&q=80" 
                             alt="Estacionamiento" 
                             class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                    </div>
                    <div class="p-6">
                        <h3 class="text-2xl font-bold mb-3 text-zinc-900 dark:text-white">Estacionamiento</h3>
                        <p class="text-zinc-600 dark:text-zinc-400 leading-relaxed">
                            Cocheras cubiertas y estacionamientos amplios para residentes y visitantes.
                        </p>
                    </div>
                </div>
                <div class="group relative overflow-hidden rounded-2xl bg-white dark:bg-zinc-800 shadow-lg hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-2">
                    <div class="aspect-video overflow-hidden">
                        <img src="https://images.unsplash.com/photo-1571896349842-33c89424de2d?ixlib=rb-4.0.3&auto=format&fit=crop&w=1000&q=80" 
                             alt="Amenidades" 
                             class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                    </div>
                    <div class="p-6">
                        <h3 class="text-2xl font-bold mb-3 text-zinc-900 dark:text-white">Amenidades</h3>
                        <p class="text-zinc-600 dark:text-zinc-400 leading-relaxed">
                            Piscina climatizada, gimnasio equipado y salón de eventos para disfrutar en comunidad.
                        </p>
                    </div>
                </div>
                <div class="group relative overflow-hidden rounded-2xl bg-white dark:bg-zinc-800 shadow-lg hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-2">
                    <div class="aspect-video overflow-hidden">
                        <img src="https://images.unsplash.com/photo-1514565131-fce0801e5785?ixlib=rb-4.0.3&auto=format&fit=crop&w=1000&q=80" 
                             alt="Ubicación" 
                             class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                    </div>
                    <div class="p-6">
                        <h3 class="text-2xl font-bold mb-3 text-zinc-900 dark:text-white">Ubicación Estratégica</h3>
                        <p class="text-zinc-600 dark:text-zinc-400 leading-relaxed">
                            Cerca de escuelas, centros comerciales y principales vías de acceso a la ciudad.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Galería Section -->
    <!-- NOTA: Reemplaza las URLs de las imágenes con las fotos reales del complejo El Talar de Martínez -->
    <section id="galeria" class="py-24 px-6 bg-gradient-to-b from-zinc-50 to-white dark:from-zinc-950 dark:to-zinc-900">
        <div class="container mx-auto max-w-7xl">
            <div class="text-center mb-16">
                <h2 class="text-5xl md:text-6xl font-bold mb-4 text-zinc-900 dark:text-white">
                    Galería
                </h2>
                <p class="text-xl text-zinc-600 dark:text-zinc-400 max-w-2xl mx-auto">
                    Conoce nuestros espacios y amenidades
                </p>
            </div>
            <div class="grid md:grid-cols-3 gap-6">
                <div class="group relative aspect-square rounded-2xl overflow-hidden shadow-xl hover:shadow-2xl transition-all duration-300 cursor-pointer">
                    <img src="https://images.unsplash.com/photo-1600607687920-4e2a09cf159d?ixlib=rb-4.0.3&auto=format&fit=crop&w=1200&q=80" 
                         alt="Fachada del complejo El Talar de Martínez" 
                         class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                    <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                        <div class="absolute bottom-4 left-4 text-white">
                            <h3 class="text-xl font-bold">Fachada Principal</h3>
                            <p class="text-sm text-zinc-200">El Talar de Martínez</p>
                        </div>
                    </div>
                </div>
                <div class="group relative aspect-square rounded-2xl overflow-hidden shadow-xl hover:shadow-2xl transition-all duration-300 cursor-pointer">
                    <img src="https://images.unsplash.com/photo-1441974231531-c6227db76b6e?ixlib=rb-4.0.3&auto=format&fit=crop&w=1200&q=80" 
                         alt="Jardines y áreas verdes" 
                         class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                    <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                        <div class="absolute bottom-4 left-4 text-white">
                            <h3 class="text-xl font-bold">Jardines</h3>
                            <p class="text-sm text-zinc-200">Espacios verdes</p>
                        </div>
                    </div>
                </div>
                <div class="group relative aspect-square rounded-2xl overflow-hidden shadow-xl hover:shadow-2xl transition-all duration-300 cursor-pointer">
                    <img src="https://images.unsplash.com/photo-1571896349842-33c89424de2d?ixlib=rb-4.0.3&auto=format&fit=crop&w=1200&q=80" 
                         alt="Piscina y área de recreación" 
                         class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                    <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                        <div class="absolute bottom-4 left-4 text-white">
                            <h3 class="text-xl font-bold">Piscina</h3>
                            <p class="text-sm text-zinc-200">Área de recreación</p>
                        </div>
                    </div>
                </div>
                <div class="group relative aspect-square rounded-2xl overflow-hidden shadow-xl hover:shadow-2xl transition-all duration-300 cursor-pointer">
                    <img src="https://images.unsplash.com/photo-1600607687644-c7171b42498b?ixlib=rb-4.0.3&auto=format&fit=crop&w=1200&q=80" 
                         alt="Interiores modernos" 
                         class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                    <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                        <div class="absolute bottom-4 left-4 text-white">
                            <h3 class="text-xl font-bold">Interiores</h3>
                            <p class="text-sm text-zinc-200">Diseño moderno</p>
                        </div>
                    </div>
                </div>
                <div class="group relative aspect-square rounded-2xl overflow-hidden shadow-xl hover:shadow-2xl transition-all duration-300 cursor-pointer">
                    <img src="https://images.unsplash.com/photo-1512917774080-9991f1c4c750?ixlib=rb-4.0.3&auto=format&fit=crop&w=1200&q=80" 
                         alt="Cocheras y estacionamiento" 
                         class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                    <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                        <div class="absolute bottom-4 left-4 text-white">
                            <h3 class="text-xl font-bold">Cocheras</h3>
                            <p class="text-sm text-zinc-200">Estacionamiento seguro</p>
                        </div>
                    </div>
                </div>
                <div class="group relative aspect-square rounded-2xl overflow-hidden shadow-xl hover:shadow-2xl transition-all duration-300 cursor-pointer">
                    <img src="https://images.unsplash.com/photo-1600585154340-be6161a56a0c?ixlib=rb-4.0.3&auto=format&fit=crop&w=1200&q=80" 
                         alt="Vista aérea del complejo" 
                         class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                    <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                        <div class="absolute bottom-4 left-4 text-white">
                            <h3 class="text-xl font-bold">Vista Aérea</h3>
                            <p class="text-sm text-zinc-200">Complejo habitacional</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Ubicación Section -->
    <section id="ubicacion" class="py-24 px-6 bg-gradient-to-b from-white to-zinc-50 dark:from-zinc-900 dark:to-zinc-950">
        <div class="container mx-auto max-w-7xl">
            <div class="text-center mb-16">
                <h2 class="text-5xl md:text-6xl font-bold mb-4 text-zinc-900 dark:text-white">
                    Ubicación
                </h2>
                <p class="text-xl text-zinc-600 dark:text-zinc-400 max-w-2xl mx-auto">
                    En el corazón de Martínez, con acceso a todo lo que necesitas
                </p>
            </div>
            <div class="grid md:grid-cols-2 gap-12 items-center">
                <div class="space-y-6">
                    <h3 class="text-3xl font-bold text-zinc-900 dark:text-white">
                        Ubicación Privilegiada
                    </h3>
                    <p class="text-lg text-zinc-600 dark:text-zinc-400 leading-relaxed">
                        El Talar de Martínez se encuentra en <strong class="text-zinc-900 dark:text-white">Monseñor Larumbe 3151, B1640GZK Martínez</strong>, 
                        una zona estratégica con fácil acceso a las principales vías de comunicación, cerca de centros comerciales, escuelas y servicios esenciales. 
                        La mejor ubicación para tu familia.
                    </p>
                    <div class="grid grid-cols-1 gap-4">
                        <div class="flex items-start gap-4 p-4 rounded-xl bg-white dark:bg-zinc-800 shadow-md">
                            <div class="text-3xl">📍</div>
                            <div>
                                <h4 class="font-semibold text-zinc-900 dark:text-white mb-1">Acceso Rápido</h4>
                                <p class="text-zinc-600 dark:text-zinc-400">Fácil acceso a autopistas principales y transporte público</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-4 p-4 rounded-xl bg-white dark:bg-zinc-800 shadow-md">
                            <div class="text-3xl">🏫</div>
                            <div>
                                <h4 class="font-semibold text-zinc-900 dark:text-white mb-1">Educación</h4>
                                <p class="text-zinc-600 dark:text-zinc-400">Cerca de escuelas y universidades de prestigio</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-4 p-4 rounded-xl bg-white dark:bg-zinc-800 shadow-md">
                            <div class="text-3xl">🛒</div>
                            <div>
                                <h4 class="font-semibold text-zinc-900 dark:text-white mb-1">Comercios</h4>
                                <p class="text-zinc-600 dark:text-zinc-400">Centros comerciales y supermercados a pocos minutos</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-4 p-4 rounded-xl bg-white dark:bg-zinc-800 shadow-md">
                            <div class="text-3xl">🏥</div>
                            <div>
                                <h4 class="font-semibold text-zinc-900 dark:text-white mb-1">Salud</h4>
                                <p class="text-zinc-600 dark:text-zinc-400">Servicios médicos y hospitales cercanos</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="relative rounded-2xl overflow-hidden shadow-2xl">
                    <iframe 
                        src="https://www.google.com/maps?q=Monse%C3%B1or+Larumbe+3151,+B1640GZK+Mart%C3%ADnez,+Provincia+de+Buenos+Aires,+Argentina&output=embed&hl=es&z=16" 
                        width="100%" 
                        height="500" 
                        style="border:0;" 
                        allowfullscreen="" 
                        loading="lazy" 
                        referrerpolicy="no-referrer-when-downgrade"
                        class="w-full h-[500px]">
                    </iframe>
                    <div class="absolute top-4 right-4 bg-white/95 dark:bg-zinc-900/95 backdrop-blur-sm px-4 py-2 rounded-lg shadow-lg border border-zinc-200 dark:border-zinc-700">
                        <p class="text-sm font-semibold text-zinc-900 dark:text-white">📍 Monseñor Larumbe 3151</p>
                        <p class="text-xs text-zinc-600 dark:text-zinc-400">B1640GZK Martínez</p>
                    </div>
                    <a href="https://www.google.com/maps/search/?api=1&query=Monse%C3%B1or+Larumbe+3151,+B1640GZK+Mart%C3%ADnez,+Provincia+de+Buenos+Aires,+Argentina" 
                       target="_blank" 
                       class="absolute bottom-4 left-4 bg-white/95 dark:bg-zinc-900/95 backdrop-blur-sm px-4 py-2 rounded-lg shadow-lg border border-zinc-200 dark:border-zinc-700 hover:bg-white dark:hover:bg-zinc-800 transition-colors">
                        <p class="text-sm font-semibold text-zinc-900 dark:text-white">Abrir en Google Maps →</p>
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Contacto Section -->
    <section id="contacto" class="py-24 px-6 bg-gradient-to-br from-zinc-50 to-white dark:from-zinc-900 dark:to-zinc-800">
        <div class="container mx-auto max-w-5xl">
            <div class="text-center mb-12">
                <h2 class="text-5xl md:text-6xl font-bold mb-4 text-zinc-900 dark:text-white">
                    Contacto
                </h2>
                <p class="text-xl text-zinc-600 dark:text-zinc-300 max-w-2xl mx-auto">
                    ¿Tenés alguna consulta? Completá el formulario y te responderemos a la brevedad
                </p>
            </div>
            <div class="bg-white dark:bg-zinc-800 p-8 md:p-10 rounded-2xl shadow-2xl border border-zinc-200 dark:border-zinc-700">
                <form x-data="{
                    formData: { name: '', email: '', phone: '', message: '' },
                    errors: {},
                    isSubmitting: false,
                    submitted: false,
                    validateField(field) {
                        if (!this.formData[field]) {
                            this.errors[field] = 'Este campo es requerido';
                            return false;
                        }
                        if (field === 'email' && !this.formData.email.includes('@')) {
                            this.errors[field] = 'Email inválido';
                            return false;
                        }
                        delete this.errors[field];
                        return true;
                    },
                    submitForm(e) {
                        e.preventDefault();
                        this.errors = {};
                        let isValid = true;
                        
                        ['name', 'email', 'phone', 'message'].forEach(field => {
                            if (!this.validateField(field)) isValid = false;
                        });
                        
                        if (isValid) {
                            this.isSubmitting = true;
                            setTimeout(() => {
                                this.isSubmitting = false;
                                this.submitted = true;
                            }, 1500);
                        }
                    }
                }" @submit="submitForm" class="space-y-6">
                    
                    <div x-show="!submitted" x-transition>
                        <div class="grid md:grid-cols-2 gap-6">
                            <div>
                                <label for="name" class="block text-sm font-semibold mb-2 text-zinc-900 dark:text-white">
                                    Nombre Completo <span class="text-red-500" aria-label="requerido">*</span>
                                </label>
                                <div class="relative">
                                    <input 
                                        type="text" 
                                        id="name" 
                                        x-model="formData.name"
                                        @blur="validateField('name')"
                                        required 
                                        class="w-full px-5 py-3 border-2 rounded-xl bg-white dark:bg-zinc-800 text-zinc-900 dark:text-white focus:ring-2 focus:ring-amber-500 focus:border-amber-500 transition-all"
                                        :class="errors.name ? 'border-red-500' : 'border-zinc-200 dark:border-zinc-700'"
                                        placeholder="Juan Pérez"
                                        aria-required="true"
                                        aria-invalid="errors.name ? 'true' : 'false'"
                                        aria-describedby="name-error">
                                    <svg x-show="formData.name && !errors.name" class="absolute right-3 top-1/2 transform -translate-y-1/2 w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                    </svg>
                                </div>
                                <p x-show="errors.name" x-text="errors.name" id="name-error" class="mt-1 text-sm text-red-500" role="alert"></p>
                            </div>
                            <div>
                                <label for="email" class="block text-sm font-semibold mb-2 text-zinc-900 dark:text-white">
                                    Email <span class="text-red-500" aria-label="requerido">*</span>
                                </label>
                                <div class="relative">
                                    <input 
                                        type="email" 
                                        id="email" 
                                        x-model="formData.email"
                                        @blur="validateField('email')"
                                        required 
                                        class="w-full px-5 py-3 border-2 rounded-xl bg-white dark:bg-zinc-800 text-zinc-900 dark:text-white focus:ring-2 focus:ring-amber-500 focus:border-amber-500 transition-all"
                                        :class="errors.email ? 'border-red-500' : 'border-zinc-200 dark:border-zinc-700'"
                                        placeholder="juan@example.com"
                                        aria-required="true"
                                        aria-invalid="errors.email ? 'true' : 'false'"
                                        aria-describedby="email-error">
                                    <svg x-show="formData.email && !errors.email && formData.email.includes('@')" class="absolute right-3 top-1/2 transform -translate-y-1/2 w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                    </svg>
                                </div>
                                <p x-show="errors.email" x-text="errors.email" id="email-error" class="mt-1 text-sm text-red-500" role="alert"></p>
                            </div>
                        </div>
                        <div>
                            <label for="phone" class="block text-sm font-semibold mb-2 text-zinc-900 dark:text-white">
                                Teléfono <span class="text-red-500" aria-label="requerido">*</span>
                            </label>
                            <div class="relative">
                                <input 
                                    type="tel" 
                                    id="phone" 
                                    x-model="formData.phone"
                                    @blur="validateField('phone')"
                                    required 
                                    class="w-full px-5 py-3 border-2 rounded-xl bg-white dark:bg-zinc-800 text-zinc-900 dark:text-white focus:ring-2 focus:ring-amber-500 focus:border-amber-500 transition-all"
                                    :class="errors.phone ? 'border-red-500' : 'border-zinc-200 dark:border-zinc-700'"
                                    placeholder="+54 11 1234-5678"
                                    aria-required="true"
                                    aria-invalid="errors.phone ? 'true' : 'false'"
                                    aria-describedby="phone-error">
                                <svg x-show="formData.phone && !errors.phone" class="absolute right-3 top-1/2 transform -translate-y-1/2 w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                            </div>
                            <p x-show="errors.phone" x-text="errors.phone" id="phone-error" class="mt-1 text-sm text-red-500" role="alert"></p>
                        </div>
                        <div>
                            <label for="message" class="block text-sm font-semibold mb-2 text-zinc-900 dark:text-white">
                                Mensaje <span class="text-red-500" aria-label="requerido">*</span>
                            </label>
                            <div class="relative">
                                <textarea 
                                    id="message" 
                                    x-model="formData.message"
                                    @blur="validateField('message')"
                                    rows="5" 
                                    required
                                    class="w-full px-5 py-3 border-2 rounded-xl bg-white dark:bg-zinc-800 text-zinc-900 dark:text-white focus:ring-2 focus:ring-amber-500 focus:border-amber-500 transition-all resize-none"
                                    :class="errors.message ? 'border-red-500' : 'border-zinc-200 dark:border-zinc-700'"
                                    placeholder="Contános qué tipo de unidad te interesa, cuándo querés mudarte, etc."
                                    aria-required="true"
                                    aria-invalid="errors.message ? 'true' : 'false'"
                                    aria-describedby="message-error"></textarea>
                            </div>
                            <p x-show="errors.message" x-text="errors.message" id="message-error" class="mt-1 text-sm text-red-500" role="alert"></p>
                            <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400" x-text="formData.message.length + ' / 500 caracteres'"></p>
                        </div>
                        <div class="flex items-start gap-3 p-4 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-lg">
                            <svg class="w-5 h-5 text-amber-600 dark:text-amber-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <p class="text-sm text-zinc-700 dark:text-zinc-300">
                                Tus datos están protegidos. Solo los usaremos para contactarte sobre tu consulta.
                            </p>
                        </div>
                        <div class="text-center pt-4">
                            <button 
                                type="submit" 
                                :disabled="isSubmitting"
                                class="btn-primary w-full md:w-auto px-10 py-4 bg-gradient-to-r from-amber-500 to-orange-600 text-white rounded-xl text-lg font-semibold hover:from-amber-600 hover:to-orange-700 shadow-xl hover:shadow-2xl transform hover:-translate-y-1 transition-all duration-300 disabled:opacity-50 disabled:cursor-not-allowed disabled:transform-none"
                                aria-label="Enviar solicitud de información">
                                <span x-show="!isSubmitting" class="relative z-10 flex items-center justify-center gap-2">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                                    </svg>
                                    Enviar Solicitud
                                </span>
                                <span x-show="isSubmitting" class="flex items-center justify-center gap-2">
                                    <svg class="animate-spin h-5 w-5" fill="none" viewBox="0 0 24 24" aria-hidden="true">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    Enviando...
                                </span>
                            </button>
                        </div>
                    </div>

                    <!-- Success Message -->
                    <div x-show="submitted" x-transition class="text-center py-12">
                        <div class="inline-flex items-center justify-center w-16 h-16 bg-green-100 dark:bg-green-900/30 rounded-full mb-4">
                            <svg class="w-8 h-8 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                        </div>
                        <h3 class="text-2xl font-bold text-zinc-900 dark:text-white mb-2">¡Gracias por tu interés!</h3>
                        <p class="text-zinc-600 dark:text-zinc-400 mb-6">Recibimos tu consulta y nos pondremos en contacto a la brevedad.</p>
                        <button @click="submitted = false; formData = { name: '', email: '', phone: '', message: '' }" class="text-amber-600 dark:text-amber-400 font-semibold hover:underline">
                            Enviar otra consulta
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-gradient-to-br from-zinc-900 via-zinc-950 to-black text-white py-16 px-6">
        <div class="container mx-auto max-w-7xl">
            <div class="grid md:grid-cols-4 gap-8 mb-12">
                <div class="md:col-span-2">
                    <h3 class="text-2xl font-bold mb-4 bg-gradient-to-r from-amber-400 to-orange-500 bg-clip-text text-transparent">
                        El Talar de Martínez
                    </h3>
                    <p class="text-zinc-400 mb-4 leading-relaxed">
                        Tu nuevo hogar en un entorno privilegiado. Descubre la excelencia en cada detalle de nuestro complejo habitacional de lujo.
                    </p>
                    <div class="flex gap-4">
                        <a href="#" class="w-10 h-10 rounded-full bg-zinc-800 hover:bg-amber-500 flex items-center justify-center transition-colors">
                            <span class="text-white">f</span>
                        </a>
                        <a href="#" class="w-10 h-10 rounded-full bg-zinc-800 hover:bg-amber-500 flex items-center justify-center transition-colors">
                            <span class="text-white">in</span>
                        </a>
                        <a href="#" class="w-10 h-10 rounded-full bg-zinc-800 hover:bg-amber-500 flex items-center justify-center transition-colors">
                            <span class="text-white">ig</span>
                        </a>
                    </div>
                </div>
                <div>
                    <h4 class="font-bold mb-4 text-lg">Enlaces Rápidos</h4>
                    <ul class="space-y-3 text-zinc-400">
                        <li><a href="#inicio" class="hover:text-amber-400 transition-colors">Inicio</a></li>
                        <li><a href="#novedades" class="hover:text-amber-400 transition-colors">Novedades</a></li>
                        <li><a href="#horarios" class="hover:text-amber-400 transition-colors">Horarios</a></li>
                        <li><a href="#informacion" class="hover:text-amber-400 transition-colors">Información</a></li>
                        <li><a href="#contacto" class="hover:text-amber-400 transition-colors">Contacto</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="font-bold mb-4 text-lg">Contacto</h4>
                    <ul class="space-y-3 text-zinc-400">
                        <li class="flex items-start gap-2">
                            <span class="text-amber-400">📧</span>
                            <span>info@eltalardemartinez.com</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="text-amber-400">📞</span>
                            <span>+54 11 1234-5678</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="text-amber-400">📍</span>
                            <span>Monseñor Larumbe 3151<br>B1640GZK Martínez<br>Provincia de Buenos Aires, Argentina</span>
                        </li>
                    </ul>
                </div>
            </div>
            <div class="border-t border-zinc-800 pt-8">
                <div class="flex flex-col md:flex-row justify-between items-center gap-4">
                    <p class="text-zinc-500 text-sm">
                        &copy; {{ date('Y') }} El Talar de Martínez. Todos los derechos reservados.
                    </p>
                    <div class="flex gap-6 text-sm text-zinc-500">
                        <a href="#" class="hover:text-amber-400 transition-colors">Política de Privacidad</a>
                        <a href="#" class="hover:text-amber-400 transition-colors">Términos y Condiciones</a>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <!-- Auth Modal -->
    @guest
    <div
        x-data="{
            activeTab: '{{ ($errors->has('name') || $errors->has('password_confirmation')) ? 'register' : (($errors->has('email') || $errors->has('password') || old('email')) ? 'login' : 'login') }}'
        }"
        @set-auth-tab.window="activeTab = $event.detail?.tab || 'login'"
        x-init="@if($errors->has('email') || $errors->has('password') || $errors->has('name') || $errors->has('password_confirmation')) $dispatch('open-modal', 'auth-modal'); @endif"
    >
        <flux:modal 
            name="auth-modal" 
            :show="$errors->has('email') || $errors->has('password') || $errors->has('name') || $errors->has('password_confirmation')" 
            focusable 
            class="max-w-md">
            <div class="space-y-6">
                <!-- Tabs -->
                <div class="flex border-b border-zinc-200 dark:border-zinc-700">
                    <button 
                        @click="activeTab = 'login'"
                        :class="activeTab === 'login' ? 'border-b-2 border-amber-500 text-amber-600 dark:text-amber-400' : 'text-zinc-600 dark:text-zinc-400'"
                        class="flex-1 py-3 px-4 text-center font-semibold transition-colors">
                        {{ __('Log in') }}
                    </button>
                    <button 
                        @click="activeTab = 'register'"
                        :class="activeTab === 'register' ? 'border-b-2 border-amber-500 text-amber-600 dark:text-amber-400' : 'text-zinc-600 dark:text-zinc-400'"
                        class="flex-1 py-3 px-4 text-center font-semibold transition-colors">
                        {{ __('Sign up') }}
                    </button>
                </div>

                <!-- Login Form -->
                <div x-show="activeTab === 'login'" x-transition>
                    <div class="flex flex-col gap-6">
                        <div class="text-center">
                            <h2 class="text-2xl font-bold text-zinc-900 dark:text-white">{{ __('Log in to your account') }}</h2>
                            <p class="text-sm text-zinc-600 dark:text-zinc-400 mt-1">{{ __('Enter your email and password below to log in') }}</p>
                        </div>

                        <x-auth-session-status class="text-center" :status="session('status')" />

                        <form method="POST" action="{{ route('login.store') }}" class="flex flex-col gap-6">
                            @csrf

                            <flux:input
                                name="email"
                                :label="__('Email address')"
                                :value="old('email')"
                                type="email"
                                required
                                autofocus
                                autocomplete="email"
                                placeholder="email@example.com"
                            />

                            <div class="relative">
                                <flux:input
                                    name="password"
                                    :label="__('Password')"
                                    type="password"
                                    required
                                    autocomplete="current-password"
                                    :placeholder="__('Password')"
                                    viewable
                                />

                                @if (Route::has('password.request'))
                                    <a class="absolute top-0 text-sm end-0 text-amber-600 dark:text-amber-400 hover:text-amber-700 dark:hover:text-amber-300" href="{{ route('password.request') }}">
                                        {{ __('Forgot your password?') }}
                                    </a>
                                @endif
                            </div>

                            <flux:checkbox name="remember" :label="__('Remember me')" :checked="old('remember')" />

                            <flux:button variant="primary" type="submit" class="w-full">
                                {{ __('Log in') }}
                            </flux:button>
                        </form>
                    </div>
                </div>

                <!-- Register Form -->
                <div x-show="activeTab === 'register'" x-transition>
                    <div class="flex flex-col gap-6">
                        <div class="text-center">
                            <h2 class="text-2xl font-bold text-zinc-900 dark:text-white">{{ __('Create an account') }}</h2>
                            <p class="text-sm text-zinc-600 dark:text-zinc-400 mt-1">{{ __('Enter your details below to create your account') }}</p>
                        </div>

                        <x-auth-session-status class="text-center" :status="session('status')" />

                        <form method="POST" action="{{ route('register.store') }}" class="flex flex-col gap-6">
                            @csrf

                            <flux:input
                                name="name"
                                :label="__('Name')"
                                :value="old('name')"
                                type="text"
                                required
                                autofocus
                                autocomplete="name"
                                :placeholder="__('Full name')"
                            />

                            <flux:input
                                name="email"
                                :label="__('Email address')"
                                :value="old('email')"
                                type="email"
                                required
                                autocomplete="email"
                                placeholder="email@example.com"
                            />

                            <flux:input
                                name="password"
                                :label="__('Password')"
                                type="password"
                                required
                                autocomplete="new-password"
                                :placeholder="__('Password')"
                                viewable
                            />

                            <flux:input
                                name="password_confirmation"
                                :label="__('Confirm password')"
                                type="password"
                                required
                                autocomplete="new-password"
                                :placeholder="__('Confirm password')"
                                viewable
                            />

                            <flux:button type="submit" variant="primary" class="w-full">
                                {{ __('Create account') }}
                            </flux:button>
                        </form>
                    </div>
                </div>
            </div>
        </flux:modal>
    </div>
    @endguest

    @fluxScripts
</body>
</html>

