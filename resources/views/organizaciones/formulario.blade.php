<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro de Beneficiarios</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 min-h-screen flex flex-col">

<header class="w-full bg-blue-600 text-white p-4 flex justify-between items-center shadow">
    <h1 class="text-xl font-bold">Formulario de registro de Navidad - Ilustre Municipalidad de Padre las Casas</h1>
</header>

<main class="flex-grow flex flex-col items-center p-6 space-y-10">



    <script src="https://cdn.jsdelivr.net/npm/rut.js/dist/rut.min.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", () => {
            const rutInput = document.querySelector("input[name='rut']");
            
            rutInput.addEventListener("blur", () => {
                if (RUT.isValid(rutInput.value)) {
                    rutInput.value = RUT.format(rutInput.value); // formatea como 12.345.678-9
                } else {
                    alert("RUT inválido, por favor verifica.");
                    rutInput.focus();
                }
            });
        });
    </script>


    <!-- Mensaje de éxito -->
    @if(session('success_ben'))
        <div class="bg-green-100 text-green-800 p-4 rounded w-full max-w-lg">{{ session('success_ben') }}</div>
    @endif


    <div class="bg-white p-8 rounded-xl shadow-2xl w-full max-w-lg">
        <h2 class="text-2xl font-bold mb-6 text-center">Registrar Beneficiario</h2>


        @if(session('status'))
        <div class="max-w-lg mx-auto mb-6">
            <div class="bg-gradient-to-r from-red-500 to-pink-500 text-white text-center p-4 rounded-xl shadow-lg animate-bounce">
                <h2 class="text-xl font-bold mb-2">🚨 Inscripción cerrada 🚨</h2>
                <p class="text-sm">{{ session('status') }}</p>
                <p class="mt-2 text-xs opacity-90">Si necesita volver a habilitar su acceso, contacte a la Municipalidad.</p>
            </div>
        </div>
        @endif


        @if($errors->any())
            <div class="max-w-lg mx-auto mb-6">
                <div class="bg-red-500 text-white text-center p-4 rounded-xl shadow-lg">
                    <h2 class="text-lg font-bold mb-2"> Error al registrar beneficiario</h2>
                    <ul class="text-sm list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif





        <form method="POST" action="{{ route('beneficiario.store') }}" class="space-y-4">
            @csrf
            
            <input type="hidden" name="formulario_id" value="{{ $formulario->id ?? '' }}">

            <div>
                <label>RUT</label>
                <input type="text" name="rut" class="w-full border rounded p-2" required>
            </div>
            <div>
                <label>Nombre completo</label>
                <input type="text" name="nombre_completo" class="w-full border rounded p-2" required>
            </div>
            <div>
                <label>Fecha de nacimiento</label>
                <input type="date" name="fecha_nacimiento" class="w-full border rounded p-2" required>
            </div>
            <div>
                <label>Sexo</label>
                <select name="sexo" class="w-full border rounded p-2">
                    <option value="">Selecciona</option>
                    <option value="M">Masculino</option>
                    <option value="F">Femenino</option>
                    <option value="U">Unisex (0 a 11 meses)</option>
                </select>
            </div>
            <div>
                <label>Dirección</label>
                <input type="text" name="direccion" class="w-full border rounded p-2" required>
            </div>

            <button type="submit" class="w-full bg-green-600 text-white py-2 rounded hover:bg-green-700">
                Registrar Beneficiario
            </button>
        </form>
    </div>

    <!-- Botón para cerrar inscripción -->
<div class="mt-6 w-full max-w-lg">
    <button 
        type="button" 
        onclick="abrirModal()" 
        class="w-full bg-red-600 text-white py-2 rounded hover:bg-red-700 transition">
        Cerrar Inscripción
    </button>
</div>

<!-- Modal -->
<div id="modalConfirmar" class="fixed inset-0 bg-black bg-opacity-50 hidden justify-center items-center">
    <div class="bg-white rounded-2xl shadow-2xl p-6 w-full max-w-md transform transition-all scale-95">
        <h2 class="text-xl font-bold text-red-600 mb-4 text-center"> Confirmar Cierre</h2>
        <p class="text-gray-700 text-center mb-6">
            ¿Estás seguro que quieres <span class="font-semibold">cerrar la inscripción</span>?<br>
            Una vez cerrada, no podrás registrar más beneficiarios.
        </p>

        <div class="flex justify-between">
            <button onclick="cerrarModal()" class="bg-gray-300 px-4 py-2 rounded hover:bg-gray-400 transition">
                 Cancelar
            </button>
            <form method="POST" action="{{ route('organizacion.cerrar') }}">
                @csrf
                <button type="submit" class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700 transition">
                     Sí, cerrar
                </button>
            </form>
        </div>
    </div>
</div>

    <script>
    function abrirModal() {
        document.getElementById("modalConfirmar").classList.remove("hidden");
        document.getElementById("modalConfirmar").classList.add("flex");
    }
    function cerrarModal() {
        document.getElementById("modalConfirmar").classList.remove("flex");
        document.getElementById("modalConfirmar").classList.add("hidden");
    }
    </script>



    <!-- Lista de Beneficiarios Registrados -->
    <div class="bg-white p-6 rounded-xl shadow-2xl w-full max-w-4xl">
        <h2 class="text-xl font-bold mb-4">Beneficiarios Registrados</h2>

        @if($beneficiarios->isEmpty())
            <p class="text-gray-600">No hay beneficiarios registrados aún.</p>
        @else
            <table class="min-w-full bg-white border border-gray-300 rounded-lg shadow">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="px-4 py-2 border">Folio (ID)</th>
                        <th class="px-4 py-2 border">Nombre Completo</th>
                        <th class="px-4 py-2 border">Fecha Nac.</th>
                        <th class="px-4 py-2 border">Edad</th>
                        <th class="px-4 py-2 border">RUT</th>
                        <th class="px-4 py-2 border">Dirección</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($beneficiarios as $ben)
                        <tr>
                            <td class="px-4 py-2 border">{{ $ben->id }}</td>
                            <td class="px-4 py-2 border">{{ $ben->nombre_completo }}</td>
                            <td class="px-4 py-2 border">{{ $ben->fecha_nacimiento }}</td>
                            <td class="px-4 py-2 border">{{ $ben->edad }}</td>
                            <td class="px-4 py-2 border">{{ $ben->rut_formateado }}</td>
                            <td class="px-4 py-2 border">{{ $ben->direccion }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>



</main>
</body>
</html>
