<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro de Beneficiario</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 min-h-screen flex flex-col">

    <!-- Barra superior -->
    <header class="w-full bg-blue-600 text-white p-4 flex justify-between items-center shadow">
        <h1 class="text-xl font-bold">Formulario de registro de navidad, Ilustre Municipalidad de Padre las Casas</h1>
        <button class="bg-white text-blue-600 font-semibold px-4 py-2 rounded hover:bg-gray-100">Iniciar Sesión</button>
    </header>

    <!-- Contenedor principal -->
    <main class="flex-grow flex items-center justify-center p-4">
        <div class="bg-white p-8 rounded-xl shadow-2xl w-full max-w-lg">
            <h2 class="text-2xl font-bold text-gray-800 mb-6 text-center">Registrar Beneficiario</h2>

            <form class="space-y-4">

                <!-- RUT -->
                <div>
                    <label class="block text-gray-700 font-semibold mb-1" for="rut">RUT</label>
                    <input type="text" id="rut" name="rut" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-400" placeholder="12.345.678-9">
                </div>

                <!-- Nombre completo -->
                <div>
                    <label class="block text-gray-700 font-semibold mb-1" for="nombre">Nombre completo</label>
                    <input type="text" id="nombre" name="nombre" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-400" placeholder="Ej: Juan Pérez">
                </div>

                <!-- Fecha de nacimiento -->
                <div>
                    <label class="block text-gray-700 font-semibold mb-1" for="fecha_nacimiento">Fecha de nacimiento</label>
                    <input type="date" id="fecha_nacimiento" name="fecha_nacimiento" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-400">
                </div>

                <!-- Sexo -->
                <div>
                    <label class="block text-gray-700 font-semibold mb-1" for="sexo">Sexo</label>
                    <select id="sexo" name="sexo" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-400">
                        <option value="">Selecciona</option>
                        <option value="M">Masculino</option>
                        <option value="F">Femenino</option>
                    </select>
                </div>

                <!-- Observaciones -->
                <div>
                    <label class="block text-gray-700 font-semibold mb-1" for="observacion">Observación (opcional)</label>
                    <textarea id="observacion" name="observacion" rows="3" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-400" placeholder="Notas adicionales sobre el beneficiario"></textarea>
                </div>

                <!-- Botón de registro -->
                <button type="submit" class="w-full bg-blue-600 text-white font-bold py-2 rounded-lg hover:bg-blue-700 transition-colors">Registrar Beneficiario</button>
            </form>
        </div>
    </main>

</body>
</html>
