<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Beneficiario</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 min-h-screen flex flex-col">

<header class="w-full bg-blue-600 text-white p-4 shadow">
    <h1 class="text-xl font-bold text-center">Editar Beneficiario</h1>
</header>

<main class="flex-grow flex flex-col items-center p-6">
    <div class="max-w-lg w-full bg-white p-8 rounded-xl shadow-2xl">
        <form method="POST" action="{{ route('beneficiario.update', $beneficiario->id) }}" class="space-y-4">
            @csrf
            @method('PUT')

            <div>
                <label>RUT</label>
                <input type="text" value="{{ $beneficiario->rut_formateado }}" disabled 
                       class="w-full border rounded p-2 bg-gray-100">
            </div>

            <div>
                <label>Nombre completo</label>
                <input type="text" name="nombre_completo" value="{{ $beneficiario->nombre_completo }}" 
                       class="w-full border rounded p-2" required>
            </div>

            <div>
                <label>Fecha de nacimiento</label>
                <input type="date" name="fecha_nacimiento" value="{{ $beneficiario->fecha_nacimiento }}" 
                       class="w-full border rounded p-2" required>
            </div>

            <div>
                <label>Sexo</label>
                <select name="sexo" class="w-full border rounded p-2">
                    <option value="">Selecciona</option>
                    <option value="M" {{ $beneficiario->sexo == 'M' ? 'selected' : '' }}>Masculino</option>
                    <option value="F" {{ $beneficiario->sexo == 'F' ? 'selected' : '' }}>Femenino</option>
                    <option value="U" {{ $beneficiario->sexo == 'U' ? 'selected' : '' }}>Unisex</option>
                </select>
            </div>

            <div>
                <label>Dirección</label>
                <input type="text" name="direccion" value="{{ $beneficiario->direccion }}" 
                       class="w-full border rounded p-2" required>
            </div>

            <button type="submit" class="w-full bg-blue-600 text-white py-2 rounded hover:bg-blue-700">
                Guardar Cambios
            </button>
        </form>
    </div>
</main>

</body>
</html>
