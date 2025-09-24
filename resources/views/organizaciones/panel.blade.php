@extends('layouts.app')

@section('content')
  @include('organizaciones.partials.header', ['organizacion' => $organizacion])
@if(session('cerrado'))
  <div class="bg-blue-50 border border-blue-200 text-blue-800 text-sm rounded-lg p-3">
    {{ session('cerrado') }}
  </div>
@endif

  <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8 space-y-6">

    {{-- CTA: Nuevo Formulario --}}
    <div class="flex items-center justify-between">
        <h2 class="text-xl font-semibold">Panel de organización</h2>

        <div class="flex gap-2">
            <a href="{{ route('formularios.export.xlsx') }}"
            class="inline-flex items-center rounded-md px-4 py-2 text-sm font-medium ring-1 ring-inset ring-gray-300 text-gray-700 hover:bg-gray-50">
            Descargar Excel
            </a>
            <a href="{{ route('formularios.export.pdf') }}"
            class="inline-flex items-center rounded-md px-4 py-2 text-sm font-medium ring-1 ring-inset ring-gray-300 text-gray-700 hover:bg-gray-50">
            Descargar PDF
            </a>

            @if(!empty($periodoAbierto))
            <a href="{{ route('formulario.show') }}"
                class="inline-flex items-center rounded-md px-4 py-2 text-sm font-medium bg-green-600 text-white hover:bg-green-700">
                Nuevo formulario
            </a>
            @endif
        </div>
    </div>


    {{-- Listado de formularios de la organización --}}
    <div class="bg-white shadow-sm ring-1 ring-gray-200 rounded-xl overflow-hidden">
      <div class="px-4 py-3 border-b border-gray-200">
        <h3 class="text-base font-semibold">Formularios anteriores</h3>
      </div>

      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Periodo</th>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Creado</th>
              <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            @forelse ($formularios as $form)
              <tr>
                <td class="px-4 py-3 text-sm text-gray-900">#{{ $form->id }}</td>

                {{-- Periodo + conteo de beneficiarios --}}
                <td class="px-4 py-3 text-sm text-gray-700">
                  {{ $form->periodo->anio ?? '—' }}
                  @if(isset($form->periodo))
                    <span class="text-gray-400">({{ $form->periodo->estado }})</span>
                  @endif
                  <span class="ml-2 text-xs text-gray-500">
                    · {{ $form->beneficiarios_count ?? 0 }} registrados
                  </span>
                </td>

                <td class="px-4 py-3">
                  <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium
                               {{ $form->estado === 'abierto' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                    {{ ucfirst($form->estado) }}
                  </span>
                </td>

                <td class="px-4 py-3 text-sm text-gray-700">
                  {{ $form->created_at?->format('d-m-Y H:i') ?? '—' }}
                </td>

                {{-- Acción: abrir detalle del formulario --}}
                <td class="px-4 py-3 text-right">
                  <a href="{{ route('formularios.show', $form->id) }}"
                     class="text-sm font-medium text-indigo-600 hover:text-indigo-800">
                    Abrir
                  </a>
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="5" class="px-4 py-6 text-center text-sm text-gray-500">
                  Aún no tienes formularios registrados.
                </td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>

      @if(method_exists($formularios, 'links'))
        <div class="px-4 py-3 border-t border-gray-200">
          {{ $formularios->links() }}
        </div>
      @endif
    </div>

  </div>
@endsection
