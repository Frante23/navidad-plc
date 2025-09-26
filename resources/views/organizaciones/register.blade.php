@extends('layouts.app')
@section('content')

  {{-- Header de organizaciones --}}
  @include('organizaciones.partials.header')

  <div class="max-w-2xl mx-auto bg-white p-6 rounded-xl shadow mt-6">
    <h1 class="text-2xl font-bold mb-4">Registrar organización</h1>

    {{-- Mensaje en verde después de enviar --}}
    @if(session('status'))
      <div class="mb-4 bg-green-100 text-green-800 border border-green-300 p-3 rounded">
        {{ session('status') }}
      </div>
    @endif

    {{-- Errores en rojo --}}
    @if ($errors->any())
      <div class="mb-4 bg-red-50 text-red-700 p-3 rounded">
        <ul class="list-disc list-inside text-sm">
          @foreach ($errors->all() as $e)
            <li>{{ $e }}</li>
          @endforeach
        </ul>
      </div>
    @endif

    <form method="POST" action="{{ route('organizacion.register.post') }}" class="space-y-4">
      @csrf

      <div>
        <label class="block text-sm">Tipo de organización</label>
        <select name="tipo_organizacion_id" class="w-full border rounded p-2" required>
          <option value="">Seleccione…</option>
          @foreach($tipos as $t)
            <option value="{{ $t->id }}" @selected(old('tipo_organizacion_id')==$t->id)>{{ $t->nombre }}</option>
          @endforeach
        </select>
      </div>

      <div>
        <label class="block text-sm">Nombre de la organización</label>
        <input name="nombre" value="{{ old('nombre') }}" class="w-full border rounded p-2" required>
      </div>

      <div>
        <label class="block text-sm">Personalidad Jurídica</label>
        <input name="personalidad_juridica" value="{{ old('personalidad_juridica') }}" class="w-full border rounded p-2" required>
      </div>

      <div>
        <label class="block text-sm">Domicilio de despacho</label>
        <input name="domicilio_despacho" value="{{ old('domicilio_despacho') }}" class="w-full border rounded p-2">
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
          <label class="block text-sm">Email</label>
          <input type="email" name="email" value="{{ old('email') }}" class="w-full border rounded p-2">
        </div>
        <div>
          <label class="block text-sm">Teléfono contacto</label>
          <input name="telefono_contacto" value="{{ old('telefono_contacto') }}" class="w-full border rounded p-2">
        </div>
      </div>

      <div>
        <label class="block text-sm">Nombre representante</label>
        <input name="nombre_representante" value="{{ old('nombre_representante') }}" class="w-full border rounded p-2">
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
          <label class="block text-sm">Fecha de creación (PJ)</label>
          <input type="date" name="fecha_creacion" value="{{ old('fecha_creacion') }}" class="w-full border rounded p-2">
        </div>
        <div>
          <label class="block text-sm">Observación</label>
          <input name="observacion" value="{{ old('observacion') }}" class="w-full border rounded p-2">
        </div>
      </div>

      <button class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Enviar registro</button>
    </form>

    <div class="text-center mt-4">
      <a class="text-blue-600 hover:underline" href="{{ route('organizacion.login.form') }}">Volver al inicio de sesión</a>
    </div>
  </div>
@endsection
