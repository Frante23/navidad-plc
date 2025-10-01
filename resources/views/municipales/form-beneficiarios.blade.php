@extends('layouts.app')

@section('content')
  @include('municipales.partials.header', ['funcionario' => $funcionario])

  <div class="max-w-7xl mx-auto px-4 py-6">

    <div class="flex items-center justify-between mb-4">
      <a href="{{ route('muni.org.show', $backParams) }}"
         class="inline-flex items-center px-4 py-2 rounded-md ring-1 ring-gray-300 hover:bg-gray-50">
        ← Volver a la organización
      </a>

      <div class="text-sm text-gray-600">
        <span class="font-medium">Formulario #{{ $form->id }}</span>
        • Periodo: {{ $form->periodo?->anio ?? '—' }}
        • Estado: <span class="uppercase">{{ $form->estado }}</span>
      </div>
    </div>

    <div class="bg-white rounded-xl shadow overflow-hidden">

      {{-- Header tabla con acciones globales --}}
      <div class="px-4 py-3 border-b flex items-center justify-between">
        <div class="text-lg font-semibold">
          Beneficiarios – {{ $form->organizacion?->nombre }} <span class="text-gray-400">(ID: Form {{ $form->id }})</span>
        </div>

        <div class="inline-flex items-center gap-2">
          <a href="{{ route('muni.form.export.xlsx', $form->id) }}"
             class="px-4 py-2 text-sm rounded-md ring-1 ring-gray-300 hover:bg-gray-50">
            Excel
          </a>
          <a href="{{ route('muni.form.export.pdf', $form->id) }}"
             class="px-4 py-2 text-sm rounded-md ring-1 ring-gray-300 hover:bg-gray-50">
            PDF
          </a>

          {{-- Botón que guarda TODOS los %RSH/observaciones --}}
          <form id="bulkForm" method="POST" action="{{ route('muni.form.ben.bulkSave', $form->id) }}">
            @csrf
          </form>
          <button type="submit" form="bulkForm"
                  class="px-4 py-2 text-sm rounded-md bg-blue-600 text-white hover:bg-blue-700">
            Guardar cambios
          </button>
        </div>
      </div>

      {{-- Tabla --}}
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">ID</th>
              <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Nombre</th>
              <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">RUT</th>
              <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">% RSH</th>
              <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Observaciones</th>
              <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Estado</th>
              <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase">Acciones</th>
            </tr>
          </thead>

          <tbody class="bg-white divide-y divide-gray-200">
          @foreach($beneficiarios as $b)
            <tr>
              <td class="px-3 py-2 text-sm">{{ $b->id }}</td>
              <td class="px-3 py-2 text-sm">{{ $b->nombre_completo }}</td>
              <td class="px-3 py-2 text-sm">{{ $b->rut }}</td>

              {{-- Inputs asociados al form global via form="bulkForm" --}}
              <td class="px-3 py-2 text-sm">
                <input type="number" min="0" max="100"
                       name="items[{{ $b->id }}][porcentaje_rsh]"
                       value="{{ old("items.$b->id.porcentaje_rsh", $b->porcentaje_rsh) }}"
                       class="w-24 border rounded px-2 py-1 text-sm"
                       form="bulkForm">
              </td>

              <td class="px-3 py-2 text-sm">
                <input type="text"
                       name="items[{{ $b->id }}][observaciones]"
                       value="{{ old("items.$b->id.observaciones", $b->observaciones) }}"
                       class="w-80 border rounded px-2 py-1 text-sm"
                       form="bulkForm">
              </td>

              <td class="px-3 py-2 text-sm">
                @php
                  $tag = $b->aceptado === 1 ? ['bg-green-100','text-green-800','Aceptado']
                       : ($b->aceptado === 0 ? ['bg-red-100','text-red-800','Rechazado']
                       : ['bg-yellow-100','text-yellow-800','Pendiente']);
                @endphp
                <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $tag[0] }} {{ $tag[1] }}">
                  {{ $tag[2] }}
                </span>
              </td>

              <td class="px-3 py-2 text-right text-sm space-x-1">
                {{-- Aceptar / Rechazar por fila (se mantienen en formularios independientes) --}}
                <form method="POST" action="{{ route('muni.ben.review', $b->id) }}" class="inline">
                  @csrf
                  <input type="hidden" name="accion" value="aceptar">
                  <button class="px-3 py-1 text-xs rounded ring-1 ring-green-300 text-green-700 hover:bg-green-50">
                    Aceptar
                  </button>
                </form>
                <form method="POST" action="{{ route('muni.ben.review', $b->id) }}" class="inline">
                  @csrf
                  <input type="hidden" name="accion" value="rechazar">
                  <button class="px-3 py-1 text-xs rounded ring-1 ring-red-300 text-red-700 hover:bg-red-50">
                    Rechazar
                  </button>
                </form>
              </td>
            </tr>
          @endforeach
          </tbody>
        </table>
      </div>

      <div class="px-4 py-3 border-t">
        {{ $beneficiarios->links() }}
      </div>
    </div>

    @if(session('status'))
      <div class="mt-4 text-sm text-green-700 bg-green-50 border border-green-200 px-3 py-2 rounded">
        {{ session('status') }}
      </div>
    @endif
  </div>
@endsection
