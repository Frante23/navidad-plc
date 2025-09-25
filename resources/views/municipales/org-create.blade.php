{{-- resources/views/municipales/org-create.blade.php --}}
@extends('layouts.app')

@section('content')
  @include('municipales.partials.header', ['funcionario' => $funcionario])

  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-6">
    <div class="flex gap-6">
      @include('municipales.partials.sidebar')

      <div class="flex-1">
        <div class="bg-white rounded-xl shadow p-6 space-y-6">
          <h2 class="text-2xl font-bold">Crear nueva agrupación</h2>

          @if ($errors->any())
            <div class="p-3 rounded bg-red-50 text-red-700 text-sm">
              <ul class="list-disc list-inside">
                @foreach ($errors->all() as $e)
                  <li>{{ $e }}</li>
                @endforeach
              </ul>
            </div>
          @endif

          @if (session('status'))
            <div class="p-3 rounded bg-green-50 text-green-700 text-sm">
              {{ session('status') }}
            </div>
          @endif

          <form method="POST" action="{{ route('muni.org.store') }}" class="space-y-5">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">Tipo de organización</label>
                <select name="tipo_organizacion_id" class="w-full border rounded-md px-3 py-2" required>
                  <option value="">Selecciona…</option>
                  @foreach($tipos as $t)
                    <option value="{{ $t->id }}" @selected(old('tipo_organizacion_id')==$t->id)>{{ $t->nombre }}</option>
                  @endforeach
                </select>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Nombre de la organización</label>
                <input type="text" name="nombre" value="{{ old('nombre') }}" class="w-full border rounded-md px-3 py-2" required>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Personalidad Jurídica</label>
                <input type="text" name="personalidad_juridica" value="{{ old('personalidad_juridica') }}" class="w-full border rounded-md px-3 py-2" required>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Domicilio de despacho</label>
                <input type="text" name="domicilio_despacho" value="{{ old('domicilio_despacho') }}" class="w-full border rounded-md px-3 py-2" required>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                <input type="email" name="email" value="{{ old('email') }}" class="w-full border rounded-md px-3 py-2">
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Nombre representante</label>
                <input type="text" name="nombre_representante" value="{{ old('nombre_representante') }}" class="w-full border rounded-md px-3 py-2" required>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Teléfono contacto</label>
                <input type="text" name="telefono_contacto" value="{{ old('telefono_contacto') }}" class="w-full border rounded-md px-3 py-2">
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Fecha de creación de la PJ</label>
                <input type="date" name="fecha_creacion" value="{{ old('fecha_creacion') }}" class="w-full border rounded-md px-3 py-2" required>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Estado</label>
                <select name="estado" class="w-full border rounded-md px-3 py-2">
                  <option value="activo" @selected(old('estado','activo')==='activo')>Activo</option>
                  <option value="inactivo" @selected(old('estado')==='inactivo')>Inactivo</option>
                </select>
              </div>

              <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">Clave de acceso</label>
                <input type="password" name="clave" class="w-full border rounded-md px-3 py-2" required>
                <p class="text-xs text-gray-500 mt-1">Se guardará encriptada.</p>
              </div>

              <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">Observación</label>
                <textarea name="observacion" rows="3" class="w-full border rounded-md px-3 py-2">{{ old('observacion') }}</textarea>
              </div>
            </div>

            <div class="pt-2 flex items-center gap-3">
              <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700">Guardar</button>
              <a href="{{ route('muni.dashboard') }}" class="px-4 py-2 rounded-md ring-1 ring-gray-300 hover:bg-gray-50">Cancelar</a>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
@endsection
