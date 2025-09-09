<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Página Principal</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 min-h-screen flex flex-col">

    <!-- Barra superior -->
    <header class="w-full bg-blue-600 text-white p-4 flex justify-between items-center shadow">
        <h1 class="text-xl font-bold">Sistema de Registro de Navidad - Ilustre Municipalidad de Padre las Casas</h1>
    </header>

    <!-- Contenido principal -->
    <main class="flex-grow flex items-center justify-center p-6">
        <div class="bg-white p-10 rounded-xl shadow-2xl w-full max-w-md text-center">
            <h2 class="text-2xl font-bold text-gray-800 mb-6">Bienvenido al Sistema</h2>

            <div class="space-y-4">
                <!-- Botón de Organización -->
                <a href="{{ url('/login/organizacion') }}" 
                   class="block w-full bg-blue-600 text-white font-semibold py-3 rounded-lg hover:bg-blue-700 transition-colors">
                    Inicio de Sesión de Organización
                </a>

                <!-- Botón de Funcionarios -->
                <a href="{{ url('/login/funcionarios') }}" 
                   class="block w-full bg-green-600 text-white font-semibold py-3 rounded-lg hover:bg-green-700 transition-colors">
                    Inicio de Sesión de Funcionarios Municipales
                </a>
            </div>
        </div>
    </main>

</body>
</html>
