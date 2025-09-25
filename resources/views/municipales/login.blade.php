<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Login Funcionarios Municipales</title>
  <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 min-h-screen flex flex-col">
  <header class="w-full bg-blue-600 text-white p-4 shadow">
    <div class="max-w-7xl mx-auto flex items-center space-x-4">
      <img src="{{ asset('images/logo_muni1.png') }}" alt="Logo Municipalidad" class="h-16 md:h-20 lg:h-24 w-auto object-contain">
      <h1 class="text-xl md:text-2xl lg:text-3xl font-bold">
        Interfaz Municipal – Ilustre Municipalidad de Padre Las Casas
      </h1>
    </div>
  </header>

  <main class="flex-grow flex items-center justify-center p-6">
    <div class="bg-white p-8 rounded-xl shadow-lg w-full max-w-md">
      <h2 class="text-2xl font-bold text-center mb-6">Inicio de sesión Funcionarios</h2>

      @if ($errors->any())
        <div class="bg-red-100 text-red-600 p-2 rounded mb-4">{{ $errors->first() }}</div>
      @endif
      @if(session('status'))
        <div class="bg-green-100 text-green-700 p-2 rounded mb-4">{{ session('status') }}</div>
      @endif

      <form method="POST" action="{{ route('login.funcionarios.post') }}" class="space-y-4">
        @csrf
        <div>
          <label class="block text-gray-700">Correo institucional</label>
          <input type="email" name="correo" class="w-full border rounded p-2" value="{{ old('correo') }}" required>
        </div>
        <div>
          <label class="block text-gray-700">Contraseña</label>
          <input type="password" name="password" class="w-full border rounded p-2" required>
        </div>
        <label class="inline-flex items-center gap-2 text-sm">
          <input type="checkbox" name="remember" class="border rounded">
          Recordarme
        </label>
        <button class="w-full bg-blue-600 text-white py-2 rounded hover:bg-blue-700">Ingresar</button>
      </form>

      <div class="text-center mt-4">
        <a href="{{ route('organizacion.login.form') }}" class="text-blue-600 hover:underline">
          ¿Volver al login de organizaciones?
        </a>
      </div>
    </div>
  </main>
</body>
</html>
