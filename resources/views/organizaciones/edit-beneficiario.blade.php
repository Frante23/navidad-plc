{{-- resources/views/organizaciones/edit-beneficiario.blade.php --}}
@extends('layouts.app')

@section('content')
  @include('organizaciones.partials.header', ['organizacion' => $beneficiario->organizacion ?? null])

  <div class="mx-auto max-w-2xl px-4 py-6 sm:px-6 lg:px-8 space-y-6">

    <h2 class="text-xl font-semibold">Editar beneficiario: {{ $beneficiario->nombre_completo }}</h2>

    @if ($errors->any())
      <div class="bg-red-50 border border-red-200 text-red-700 text-sm rounded-lg p-3">
        <ul class="list-disc list-inside">
          @foreach ($errors->all() as $e)
            <li>{{ $e }}</li>
          @endforeach
        </ul>
      </div>
    @endif

    <div class="bg-white p-6 rounded-xl shadow space-y-6">
      <form method="POST" action="{{ route('beneficiario.update', $beneficiario->id) }}" class="space-y-4">
        @csrf
        @method('PUT')

        <div>
          <label class="block text-sm font-medium text-gray-700">RUT</label>
          <input type="text" name="rut" value="{{ old('rut', $beneficiario->rut) }}"
                 class="mt-1 w-full border rounded p-2" required>
          <p class="mt-1 text-xs text-gray-500">Puedes escribir con puntos y guion; se limpiará y validará.</p>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700">Nombre completo</label>
          <input type="text" name="nombre_completo" value="{{ old('nombre_completo', $beneficiario->nombre_completo) }}"
                 class="mt-1 w-full border rounded p-2" required>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700">Fecha de nacimiento</label>
          <input type="date" name="fecha_nacimiento" value="{{ old('fecha_nacimiento', $beneficiario->fecha_nacimiento) }}"
                 class="mt-1 w-full border rounded p-2" required>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700">Sexo</label>
          <select name="sexo" class="mt-1 w-full border rounded p-2">
            <option value="">Selecciona</option>
            <option value="M" @selected(old('sexo', $beneficiario->sexo) === 'M')>Masculino</option>
            <option value="F" @selected(old('sexo', $beneficiario->sexo) === 'F')>Femenino</option>
            <option value="U" @selected(old('sexo', $beneficiario->sexo) === 'U')>Unisex</option>
          </select>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700">Dirección</label>
          <input type="text" name="direccion" value="{{ old('direccion', $beneficiario->direccion) }}"
                 class="mt-1 w-full border rounded p-2" required>
        </div>



        <div class="mb-3">
          <label class="block text-sm font-medium text-gray-700">RUT Jefe de Hogar</label>
          <input type="text" name="rut_jefe_hogar"
                value="{{ old('rut_jefe_hogar', $beneficiario->rut_jefe_hogar) }}"
                class="mt-1 block w-full border rounded-md px-3 py-2">
        </div>


        <button type="submit"
                class="w-full bg-blue-600 text-white py-2 rounded hover:bg-blue-700">
          Guardar cambios
        </button>
      </form>

     {{-- Botones de volver (abajo) según origen --}}
    <div class="flex flex-col sm:flex-row gap-3 pt-4 border-t">
    @if($from === 'form')
        <a href="{{ route('formulario.show') }}"
        class="flex-1 text-center bg-gray-200 hover:bg-gray-300 text-gray-800 font-medium py-2 px-4 rounded-lg">
        Volver al formulario (edición)
        </a>
    @else
        <a href="{{ route('formularios.show', $beneficiario->formulario_id) }}"
        class="flex-1 text-center bg-gray-200 hover:bg-gray-300 text-gray-800 font-medium py-2 px-4 rounded-lg">
        Volver al formulario (detalle)
        </a>
        <a href="{{ route('panel.inicio') }}"
        class="flex-1 text-center bg-gray-200 hover:bg-gray-300 text-gray-800 font-medium py-2 px-4 rounded-lg">
        Volver al panel
        </a>
    @endif
    </div>

    </div>
  </div>
@endsection
