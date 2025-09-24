@extends('layouts.app')

@section('content')
  @include('organizaciones.partials.header', ['organizacion' => $organizacion])

  <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8 space-y-6">

    {{-- Encabezado del detalle --}}
    <div class="flex items-center justify-between">
      <div>
        <h2 class="text-xl font-semibold">Detalle del formulario #{{ $formulario->id }}</h2>
        <p class="text-sm text-gray-600">
          Periodo:
          <span class="font-medium">{{ $formulario->periodo->anio ?? '—' }}</span>
          · Estado:
          <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium
                      {{ $formulario->estado === 'abierto' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
            {{ ucfirst($formulario->estado) }}
          </span>
          · Registrados: <span class="font-medium">{{ $formulario->beneficiarios_count ?? 0 }}</span>
          · Creado: {{ $formulario->created_at?->format('d-m-Y H:i') ?? '—' }}
        </p>
      </div>

      <div class="flex gap-2">
        <a href="{{ route('panel.inicio') }}"
           class="inline-flex items-center rounded-md px-3 py-2 text-sm font-medium ring-1 ring-inset
                  ring-gray-300 text-gray-700 hover:bg-gray-50">
          Volver al panel
        </a>
        @if($formulario->estado === 'abierto')
          {{-- Botón para ir a edición general si quieres --}}
          <a href="{{ route('formulario.show') }}"
             class="hidden md:inline-flex items-center rounded-md px-3 py-2 text-sm font-medium
                    bg-indigo-600 text-white hover:bg-indigo-700">
            Ir a edición
          </a>
        @endif
      </div>
    </div>

    {{-- Tabla de beneficiarios (solo lectura con opción de editar) --}}
    <div class="bg-white shadow-sm ring-1 ring-gray-200 rounded-xl overflow-hidden">
      <div class="px-4 py-3 border-b border-gray-200">
        <h3 class="text-base font-semibold">Beneficiarios</h3>
      </div>

      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">RUT</th>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nombre</th>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nacimiento</th>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Sexo</th>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Dirección</th>
              <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            @forelse ($beneficiarios as $b)
              <tr>
                <td class="px-4 py-3 text-sm text-gray-900">{{ $b->rut }}</td>
                <td class="px-4 py-3 text-sm text-gray-900">{{ $b->nombre_completo }}</td>
                <td class="px-4 py-3 text-sm text-gray-700">
                  {{ \Carbon\Carbon::parse($b->fecha_nacimiento)->format('d-m-Y') }}
                </td>
                <td class="px-4 py-3 text-sm text-gray-700">{{ $b->sexo ?? '—' }}</td>
                <td class="px-4 py-3 text-sm text-gray-700">{{ $b->direccion ?? '—' }}</td>
                <td class="px-4 py-3 text-right">
                  <a href="{{ route('beneficiario.edit', ['id' => $b->id, 'from' => 'show']) }}"
                    class="text-sm font-medium text-indigo-600 hover:text-indigo-800">
                    Editar
                </a>
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="6" class="px-4 py-6 text-center text-sm text-gray-500">
                  No hay beneficiarios registrados en este formulario.
                </td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>

      <div class="px-4 py-3 border-t border-gray-200">
        {{ $beneficiarios->links() }}
      </div>
    </div>

  </div>
@endsection
