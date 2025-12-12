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
        .animate-fade-in-up {
            animation: fadeInUp 0.8s ease-out;
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
        @media (max-width: 768px) {
            .parallax {
                background-attachment: scroll;
            }
        }
        /* Force auth buttons to be visible */
        @media (min-width: 768px) {
            .auth-buttons-container {
                display: flex !important;
                visibility: visible !important;
                opacity: 1 !important;
            }
        }
        .auth-buttons-desktop {
            display: flex !important;
            visibility: visible !important;
            opacity: 1 !important;
            position: relative !important;
            z-index: 100 !important;
        }
        @media (max-width: 767px) {
            .auth-buttons-desktop {
                display: none !important;
            }
        }
        .auth-buttons-mobile {
            display: flex !important;
            visibility: visible !important;
            opacity: 1 !important;
            position: relative !important;
            z-index: 100 !important;
        }
        @media (min-width: 768px) {
            .auth-buttons-mobile {
                display: none !important;
            }
        }
    </style>
</head>
<body class="bg-white dark:bg-zinc-900 text-zinc-900 dark:text-zinc-100 scroll-smooth">
    <!-- Navigation -->
    <nav x-data="{}" class="fixed top-0 w-full bg-white dark:bg-zinc-900 backdrop-blur-md z-50 border-b border-zinc-200 dark:border-zinc-800 shadow-md">
        <div class="container mx-auto px-6 py-4">
            <div class="flex items-center justify-between">
                <!-- Logo -->
                <div class="text-2xl font-bold bg-gradient-to-r from-zinc-900 to-zinc-700 dark:from-white dark:to-zinc-300 bg-clip-text text-transparent">
                    El Talar de Martínez
                </div>
                
                <!-- Desktop Navigation -->
                <div class="hidden md:flex items-center gap-8">
                    <a href="#inicio" class="text-sm font-medium hover:text-zinc-600 dark:hover:text-zinc-400 transition-colors">Inicio</a>
                    <a href="#caracteristicas" class="text-sm font-medium hover:text-zinc-600 dark:hover:text-zinc-400 transition-colors">Características</a>
                    <a href="#galeria" class="text-sm font-medium hover:text-zinc-600 dark:hover:text-zinc-400 transition-colors">Galería</a>
                    <a href="#ubicacion" class="text-sm font-medium hover:text-zinc-600 dark:hover:text-zinc-400 transition-colors">Ubicación</a>
                    <a href="#contacto" class="text-sm font-medium hover:text-zinc-600 dark:hover:text-zinc-400 transition-colors">Contacto</a>
                </div>
                
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section id="inicio" class="relative min-h-screen flex items-center justify-center py-20 overflow-hidden">
        <!-- Background Image -->
        <div class="absolute inset-0 bg-cover bg-center bg-no-repeat opacity-30 dark:opacity-20" style="background-image: url('https://images.unsplash.com/photo-1600607687920-4e2a09cf159d?ixlib=rb-4.0.3&auto=format&fit=crop&w=2070&q=80');"></div>
        <!-- Overlay for better contrast -->
        <div class="absolute inset-0 bg-gradient-to-br from-white/80 via-white/70 to-amber-50/80 dark:from-zinc-900/90 dark:via-zinc-800/90 dark:to-zinc-900/90"></div>
        
        <div class="relative z-10 container mx-auto px-6 py-20 text-center">
            <div class="animate-fade-in-up max-w-4xl mx-auto">
                <h1 class="text-6xl md:text-8xl font-bold mb-6 text-zinc-900 dark:text-white leading-tight">
                    El Talar de<br>
                    <span class="bg-gradient-to-r from-amber-500 to-orange-600 bg-clip-text text-transparent">Martínez</span>
                </h1>
                <p class="text-xl md:text-2xl text-zinc-600 dark:text-zinc-300 mb-20 max-w-3xl mx-auto leading-relaxed">
                    Tu nuevo hogar en un entorno privilegiado. Descubre la excelencia en cada detalle de nuestro complejo habitacional de lujo.
                </p>
                
                <!-- Action Buttons - Lower position with consistent colors -->
                <div class="flex flex-col items-center gap-4 mt-16">
                    <!-- Auth Buttons Row -->
                    <div class="flex items-center justify-center gap-4">
                        @auth
                            <a href="{{ route('dashboard') }}" class="px-8 py-3 bg-gradient-to-r from-amber-500 to-orange-600 text-white rounded-xl text-base font-semibold hover:from-amber-600 hover:to-orange-700 shadow-xl hover:shadow-2xl transform hover:-translate-y-1 transition-all duration-300 border-2 border-white/20 backdrop-blur-sm">
                                Dashboard
                            </a>
                        @else
                            <a href="{{ route('login') }}" class="px-8 py-3 bg-gradient-to-r from-amber-500 to-orange-600 text-white rounded-xl text-base font-semibold hover:from-amber-600 hover:to-orange-700 shadow-xl hover:shadow-2xl transform hover:-translate-y-1 transition-all duration-300 border-2 border-white/20 backdrop-blur-sm">
                                {{ __('Log in') }}
                            </a>
                            <a href="{{ route('register') }}" class="px-8 py-3 bg-gradient-to-r from-amber-500 to-orange-600 text-white rounded-xl text-base font-semibold hover:from-amber-600 hover:to-orange-700 shadow-xl hover:shadow-2xl transform hover:-translate-y-1 transition-all duration-300 border-2 border-white/20 backdrop-blur-sm">
                                {{ __('Sign up') }}
                            </a>
                        @endauth
                    </div>
                    
                    <!-- CTA Buttons Row -->
                    <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
                        <a href="#contacto" class="px-10 py-4 bg-gradient-to-r from-amber-500 to-orange-600 text-white rounded-xl text-lg font-semibold hover:from-amber-600 hover:to-orange-700 shadow-xl hover:shadow-2xl transform hover:-translate-y-1 transition-all duration-300 border-2 border-white/20 backdrop-blur-sm">
                            Solicitar Información
                        </a>
                        <a href="#galeria" class="px-10 py-4 bg-gradient-to-r from-amber-500 to-orange-600 text-white rounded-xl text-lg font-semibold hover:from-amber-600 hover:to-orange-700 shadow-xl hover:shadow-2xl transform hover:-translate-y-1 transition-all duration-300 border-2 border-white/20 backdrop-blur-sm">
                            Ver Galería
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Características Section -->
    <section id="caracteristicas" class="py-24 px-6 bg-gradient-to-b from-white to-zinc-50 dark:from-zinc-900 dark:to-zinc-950">
        <div class="container mx-auto max-w-7xl">
            <div class="text-center mb-16">
                <h2 class="text-5xl md:text-6xl font-bold mb-4 text-zinc-900 dark:text-white">
                    Características del Complejo
                </h2>
                <p class="text-xl text-zinc-600 dark:text-zinc-400 max-w-2xl mx-auto">
                    Descubre todo lo que hace de El Talar de Martínez el lugar perfecto para vivir
                </p>
            </div>
            <div class="grid md:grid-cols-3 gap-8">
                <div class="group relative overflow-hidden rounded-2xl bg-white dark:bg-zinc-800 shadow-lg hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-2">
                    <div class="aspect-video overflow-hidden">
                        <img src="https://images.unsplash.com/photo-1600607687939-ce8a6c25118c?ixlib=rb-4.0.3&auto=format&fit=crop&w=1000&q=80" 
                             alt="Diseño Moderno" 
                             class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                    </div>
                    <div class="p-6">
                        <h3 class="text-2xl font-bold mb-3 text-zinc-900 dark:text-white">Diseño Moderno</h3>
                        <p class="text-zinc-600 dark:text-zinc-400 leading-relaxed">
                            Arquitectura contemporánea con acabados de primera calidad y espacios amplios diseñados para tu comodidad.
                        </p>
                    </div>
                </div>
                <div class="group relative overflow-hidden rounded-2xl bg-white dark:bg-zinc-800 shadow-lg hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-2">
                    <div class="aspect-video overflow-hidden">
                        <img src="https://images.unsplash.com/photo-1441974231531-c6227db76b6e?ixlib=rb-4.0.3&auto=format&fit=crop&w=1000&q=80" 
                             alt="Áreas Verdes" 
                             class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                    </div>
                    <div class="p-6">
                        <h3 class="text-2xl font-bold mb-3 text-zinc-900 dark:text-white">Áreas Verdes</h3>
                        <p class="text-zinc-600 dark:text-zinc-400 leading-relaxed">
                            Espacios comunes con jardines cuidados y áreas recreativas para disfrutar en familia.
                        </p>
                    </div>
                </div>
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
                    Solicita Información
                </h2>
                <p class="text-xl text-zinc-600 dark:text-zinc-300 max-w-2xl mx-auto">
                    Completa el formulario y nos pondremos en contacto contigo
                </p>
            </div>
            <div class="bg-white dark:bg-zinc-800 p-10 rounded-2xl shadow-2xl border border-zinc-200 dark:border-zinc-700">
                <form action="mailto:info@eltalardemartinez.com" method="GET" class="space-y-6">
                    <div class="grid md:grid-cols-2 gap-6">
                        <div>
                            <label for="name" class="block text-sm font-semibold mb-2 text-zinc-900 dark:text-white">Nombre Completo</label>
                            <input type="text" id="name" name="name" required 
                                class="w-full px-5 py-3 border-2 border-zinc-200 dark:border-zinc-700 rounded-xl bg-white dark:bg-zinc-800 text-zinc-900 dark:text-white focus:ring-2 focus:ring-amber-500 focus:border-amber-500 transition-all"
                                placeholder="Tu nombre">
                        </div>
                        <div>
                            <label for="email" class="block text-sm font-semibold mb-2 text-zinc-900 dark:text-white">Email</label>
                            <input type="email" id="email" name="email" required 
                                class="w-full px-5 py-3 border-2 border-zinc-200 dark:border-zinc-700 rounded-xl bg-white dark:bg-zinc-800 text-zinc-900 dark:text-white focus:ring-2 focus:ring-amber-500 focus:border-amber-500 transition-all"
                                placeholder="tu@email.com">
                        </div>
                    </div>
                    <div>
                        <label for="phone" class="block text-sm font-semibold mb-2 text-zinc-900 dark:text-white">Teléfono</label>
                        <input type="tel" id="phone" name="phone" required 
                            class="w-full px-5 py-3 border-2 border-zinc-200 dark:border-zinc-700 rounded-xl bg-white dark:bg-zinc-800 text-zinc-900 dark:text-white focus:ring-2 focus:ring-amber-500 focus:border-amber-500 transition-all"
                            placeholder="+54 11 1234-5678">
                    </div>
                    <div>
                        <label for="message" class="block text-sm font-semibold mb-2 text-zinc-900 dark:text-white">Mensaje</label>
                        <textarea id="message" name="message" rows="5" required
                            class="w-full px-5 py-3 border-2 border-zinc-200 dark:border-zinc-700 rounded-xl bg-white dark:bg-zinc-800 text-zinc-900 dark:text-white focus:ring-2 focus:ring-amber-500 focus:border-amber-500 transition-all resize-none"
                            placeholder="Cuéntanos cómo podemos ayudarte..."></textarea>
                    </div>
                    <div class="text-center pt-4">
                        <button type="submit" 
                            class="px-10 py-4 bg-gradient-to-r from-amber-500 to-orange-600 text-white rounded-xl text-lg font-semibold hover:from-amber-600 hover:to-orange-700 shadow-xl hover:shadow-2xl transform hover:-translate-y-1 transition-all duration-300">
                            Enviar Solicitud
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
                        <li><a href="#caracteristicas" class="hover:text-amber-400 transition-colors">Características</a></li>
                        <li><a href="#galeria" class="hover:text-amber-400 transition-colors">Galería</a></li>
                        <li><a href="#ubicacion" class="hover:text-amber-400 transition-colors">Ubicación</a></li>
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
        @open-modal.window="if ($event.detail === 'auth-modal') { activeTab = 'login'; $dispatch('open-modal', 'auth-modal') }"
        x-init="@if($errors->has('email') || $errors->has('password') || $errors->has('name') || $errors->has('password_confirmation')) $dispatch('open-modal', 'auth-modal'); @endif">
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

