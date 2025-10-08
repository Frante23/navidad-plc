@extends('layouts.app')

@section('content')
  @include('municipales.partials.header')

  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
    <div class="flex gap-6">
      @include('municipales.partials.sidebar')

      <main class="flex-1">
        <h1 class="text-2xl font-bold mb-6">Crear funcionario municipal</h1>

        @if ($errors->any())
          <div class="mb-4 p-3 bg-red-100 text-red-700 rounded">
            <ul class="list-disc list-inside">
              @foreach ($errors->all() as $e)
                <li>{{ $e }}</li>
              @endforeach
            </ul>
          </div>
        @endif

        <form method="POST" action="{{ route('funcionarios.store') }}" class="space-y-4 max-w-3xl">
          @csrf

          <div>
            <label class="block text-sm font-medium">Nombre completo</label>
            <input name="nombre_completo" value="{{ old('nombre_completo') }}" class="w-full border rounded p-2" required>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium">RUT</label>
              <input name="rut" value="{{ old('rut') }}" class="w-full border rounded p-2" required>
            </div>
            <div>
              <label class="block text-sm font-medium">Correo</label>
              <input type="email" name="correo" value="{{ old('correo') }}" class="w-full border rounded p-2" required>
            </div>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium">Teléfono contacto</label>
              <input name="telefono_contacto" value="{{ old('telefono_contacto') }}" class="w-full border rounded p-2">
            </div>
            <div>
              <label class="block text-sm font-medium">Cargo</label>
              <input name="cargo" value="{{ old('cargo') }}" class="w-full border rounded p-2">
            </div>
          </div>

          <div>
            <label class="block text-sm font-medium">Contraseña</label>
            <input type="password" name="password" class="w-full border rounded p-2" required>
            <p class="text-xs text-gray-500 mt-1">Mínimo 8 caracteres.</p>
          </div>

          <div class="flex items-center space-x-2">
            <input id="es_admin" type="checkbox" name="es_admin" value="1" class="h-4 w-4" {{ old('es_admin') ? 'checked' : '' }}>
            <label for="es_admin" class="text-sm">Conceder rol administrador</label>
          </div>

          <div class="pt-4">
            <button class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Guardar</button>
            <a href="{{ route('funcionarios.index') }}" class="ml-2 px-4 py-2 border rounded">Cancelar</a>
          </div>
        </form>
      </main>
    </div>
  </div>
@endsection
