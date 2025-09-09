<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Login Organización</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen">

    <div class="bg-white p-8 rounded-lg shadow-lg w-full max-w-md">
        <h2 class="text-2xl font-bold text-center mb-6">Inicio de Sesión - Organización</h2>

        @if ($errors->any())
            <div class="bg-red-100 text-red-600 p-2 rounded mb-4">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('organizacion.login.post') }}" class="space-y-4">
            @csrf
            <div>
                <label for="email" class="block text-gray-700">Correo</label>
                <input type="email" name="email" id="email" class="w-full border rounded p-2" required>
            </div>
            <div>
                <label for="clave" class="block text-gray-700">Contraseña</label>
                <input type="password" name="clave" id="clave" class="w-full border rounded p-2" required>
            </div>
            <button type="submit" class="w-full bg-blue-600 text-white py-2 rounded hover:bg-blue-700">
                Ingresar
            </button>
        </form>

        <div class="text-center mt-4">
            <a href="{{ route('login.funcionarios') }}" class="text-blue-600 hover:underline">
                ¿Eres funcionario municipal? Inicia sesión aquí
            </a>
        </div>
    </div>

</body>
</html>
