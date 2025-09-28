{{-- resources/views/municipales/form-beneficiarios.blade.php --}}
@extends('layouts.app')

@section('content')
  @include('municipales.partials.header', ['funcionario' => $funcionario])

  <div class="max-w-7xl mx-auto px-4 py-6 sm:px-6 lg:px-8 space-y-6">

    {{-- Breadcrumb / volver --}}
    <div class="flex items-center justify-between">
      <div>
        <a href="{{ route('muni.org.show', $backParams) }}"
           class="inline-flex items-center px-3 py-1.5 text-sm rounded ring-1 ring-gray-300 hover:bg-gray-50">
          ← Volver a la organización
        </a>
      </div>
      <div class="text-sm text-gray-600">
        <span class="font-semibold">Formulario #{{ $form->id }}</span>
        • Periodo: {{ $form->periodo?->anio ?? '—' }}
        • Estado: <span class="uppercase">{{ $form->estado }}</span>
      </div>
    </div>

    {{-- Título --}}
    <div class="bg-white rounded-xl shadow">
      <div class="px-4 py-3 border-b flex items-center justify-between">
        <h2 class="font-semibold">
          Beneficiarios – {{ $form->organizacion?->nombre ?? 'Organización' }}
          <span class="text-gray-400">(ID Form: {{ $form->id }})</span>
        </h2>

        <div class="flex items-center gap-2">
          <a href="{{ route('muni.form.export.xlsx', $form->id) }}"
             class="inline-flex items-center px-3 py-1.5 text-xs font-medium ring-1 ring-gray-300 rounded-md hover:bg-gray-100">
            Excel
          </a>
          <a href="{{ route('muni.form.export.pdf', $form->id) }}"
             class="inline-flex items-center px-3 py-1.5 text-xs font-medium ring-1 ring-gray-300 rounded-md hover:bg-gray-100">
            PDF
          </a>
        </div>
      </div>

      @if(session('status'))
        <div class="px-4 pt-3">
          <div class="mb-3 bg-green-100 text-green-800 border border-green-300 px-4 py-2 rounded">
            {{ session('status') }}
          </div>
        </div>
      @endif

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
          @forelse($beneficiarios as $b)
            @php $formId = "fr_ben_{$b->id}"; @endphp
            <tr>
              <td class="px-3 py-2 text-sm">{{ $b->id }}</td>
              <td class="px-3 py-2 text-sm">{{ $b->nombre_completo }}</td>
              <td class="px-3 py-2 text-sm">{{ $b->rut }}</td>

              <td class="px-3 py-2 text-sm">
                <input type="number" name="porcentaje_rsh" min="0" max="100"
                       value="{{ old('porcentaje_rsh', $b->porcentaje_rsh) }}"
                       class="w-20 border rounded px-2 py-1 text-sm"
                       form="{{ $formId }}">
              </td>

              <td class="px-3 py-2 text-sm">
                <input type="text" name="observaciones"
                       value="{{ old('observaciones', $b->observaciones) }}"
                       class="w-64 border rounded px-2 py-1 text-sm"
                       form="{{ $formId }}">
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
                <form id="{{ $formId }}" method="POST"
                      action="{{ route('muni.ben.review', $b->id) }}"
                      class="inline-flex gap-1">
                  @csrf
                  <button name="accion" value="guardar"
                          class="px-2 py-1 text-xs rounded ring-1 ring-gray-300 hover:bg-gray-50">
                    Guardar
                  </button>
                  <button name="accion" value="aceptar"
                          class="px-2 py-1 text-xs rounded ring-1 ring-green-300 text-green-700 hover:bg-green-50">
                    Aceptar
                  </button>
                  <button name="accion" value="rechazar"
                          class="px-2 py-1 text-xs rounded ring-1 ring-red-300 text-red-700 hover:bg-red-50">
                    Rechazar
                  </button>
                </form>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="7" class="px-4 py-6 text-center text-sm text-gray-500">
                No hay beneficiarios en este formulario.
              </td>
            </tr>
          @endforelse
          </tbody>
        </table>
      </div>

      <div class="px-4 py-3 border-t">
        {{ $beneficiarios->links() }}
      </div>
    </div>

  </div>
@endsection
