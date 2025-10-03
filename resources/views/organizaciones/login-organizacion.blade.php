<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Login Organización</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/login.css') }}">
</head>
<body class="bg-gray-100 min-h-screen flex flex-col">

    {{-- Header institucional --}}
    <header class="w-full bg-blue-600 text-white p-4 shadow">
        <div class="max-w-7xl mx-auto flex items-center space-x-4">
            <img src="{{ asset('images/logo_muni1.png') }}"
                 alt="Logo Municipalidad"
                 class="h-16 md:h-20 lg:h-24 w-auto object-contain">

            <h1 class="text-xl md:text-2xl lg:text-3xl font-bold">
                Programa Navidad – Ilustre Municipalidad de Padre Las Casas
            </h1>
        </div>
    </header>

    {{-- Contenido principal --}}
    <main class="flex-grow flex items-center justify-center p-6">
        <div class="bg-white p-8 rounded-xl shadow-lg w-full max-w-md">
            <h2 class="text-2xl font-bold text-center mb-6">Inicio de Sesión de Organizaciones</h2>

            {{-- Errores de login --}}
            @if ($errors->any())
                <div class="bg-red-100 text-red-600 p-2 rounded mb-4">
                    {{ $errors->first() }}
                </div>
            @endif

            {{-- Mensaje al cerrar sesión/inscripción --}}
            @if(session('cerrado'))
                <div class="max-w-lg mx-auto mb-6">
                    <div class="bg-red-500 text-white text-center p-4 rounded-xl shadow-lg">
                        <h2 class="text-lg font-bold">Inscripción/Sesión cerrada</h2>
                        <p class="mt-2 text-sm">{{ session('cerrado') }}</p>
                    </div>
                </div>
            @endif

            {{-- Formulario de login --}}
            <form method="POST" action="{{ route('organizacion.login.post') }}" class="space-y-4">
                @csrf
                <div>
                    <label for="personalidad_juridica" class="block text-gray-700">Personalidad Jurídica</label>
                    <input type="text" name="personalidad_juridica" id="personalidad_juridica"
                           class="w-full border rounded p-2" required>
                </div>
                <div>
                    <label for="clave" class="block text-gray-700">Contraseña</label>
                    <input type="password" name="clave" id="clave"
                           class="w-full border rounded p-2" required>
                </div>
                <button type="submit"
                        class="w-full bg-blue-600 text-white py-2 rounded hover:bg-blue-700">
                    Ingresar
                </button>
            </form>

            <div class="text-center mt-4">
                <a href="{{ route('login.funcionarios') }}" class="text-blue-600 hover:underline">
                    ¿Eres funcionario municipal? Inicia sesión aquí
                </a>
            </div>


            <div class="text-center mt-4">
                <a href="{{ route('organizacion.register.form') }}" class="text-blue-600 hover:underline">
                    ¿Aún no estás registrado? Solicita registro aquí
                </a>
            </div>


        </div>
    </main>
</body>
</html>
