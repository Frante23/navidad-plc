@extends('layouts.app')

@section('content')
  @include('municipales.partials.header', ['funcionario' => $funcionario])

  <div class="max-w-7xl mx-auto px-4 py-6 sm:px-6 lg:px-8 space-y-6">

    <div class="flex items-center justify-between">
      <h2 class="text-xl font-semibold">
        Formularios – {{ $org->nombre }}
        <span class="text-gray-400">({{ $org->personalidad_juridica }})</span>
      </h2>

      <div class="flex items-center gap-2">
        <a href="{{ route('muni.org.export.xlsx', ['id' => $org->id] + request()->only('periodo_id')) }}"
           class="bg-gray-100 text-gray-800 px-4 py-2 rounded-md ring-1 ring-gray-300 hover:bg-gray-200">
          Descargar Excel
        </a>
        <a href="{{ route('muni.org.export.pdf', ['id' => $org->id] + request()->only('periodo_id')) }}"
           class="bg-gray-100 text-gray-800 px-4 py-2 rounded-md ring-1 ring-gray-300 hover:bg-gray-200">
          Descargar PDF
        </a>
      </div>
    </div>

    {{-- Filtro por período --}}
    <form method="GET" class="flex items-center gap-2">
      <select name="periodo_id" class="border rounded-md px-3 py-2">
        <option value="">Todos los períodos</option>
        @foreach($periodos as $p)
          <option value="{{ $p->id }}" @selected(($periodoSel ?? null) == $p->id)>
            {{ $p->anio }} ({{ $p->estado }})
          </option>
        @endforeach
      </select>
      <button class="bg-blue-600 text-white px-4 py-2 rounded-md">Filtrar</button>
    </form>

    {{-- Tabla de formularios --}}
    <div class="bg-white rounded-xl shadow overflow-hidden">
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Periodo</th>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Beneficiarios</th>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Creado</th>
              <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
            </tr>
          </thead>

          <tbody class="bg-white divide-y divide-gray-200">
            @forelse($formularios as $f)
              <tr>
                <td class="px-4 py-3 text-sm">#{{ $f->id }}</td>
                <td class="px-4 py-3 text-sm">{{ $f->periodo?->anio ?? '—' }}</td>
                <td class="px-4 py-3">
                  <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium
                               {{ $f->estado === 'abierto' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                    {{ ucfirst($f->estado) }}
                  </span>
                </td>
                <td class="px-4 py-3 text-sm">{{ $f->beneficiarios_count }}</td>
                <td class="px-4 py-3 text-sm">{{ optional($f->created_at)->format('d-m-Y H:i') }}</td>

                {{-- Acciones por formulario: Excel / PDF / Ver-Editar --}}
                <td class="px-4 py-3 text-right">
                  <div class="inline-flex items-center gap-2">
                    <a href="{{ route('muni.form.export.xlsx', $f->id) }}"
                       class="inline-flex items-center px-3 py-1.5 text-xs font-medium ring-1 ring-inset ring-gray-300 rounded-md hover:bg-gray-100">
                      Excel
                    </a>
                    <a href="{{ route('muni.form.export.pdf', $f->id) }}"
                       class="inline-flex items-center px-3 py-1.5 text-xs font-medium ring-1 ring-inset ring-gray-300 rounded-md hover:bg-gray-100">
                      PDF
                    </a>
                    <a href="{{ route('muni.form.beneficiarios', ['id'=>$f->id] + request()->only('periodo_id')) }}"
                       class="inline-flex items-center px-3 py-1.5 text-xs font-medium ring-1 ring-inset ring-indigo-300 text-indigo-700 rounded-md hover:bg-indigo-50">
                      Ver / Editar
                    </a>
                  </div>
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="6" class="px-4 py-6 text-center text-sm text-gray-500">
                  No hay formularios para esta organización con los filtros aplicados.
                </td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>

      <div class="px-4 py-3 border-t">
        {{ $formularios->links() }}
      </div>
    </div>

    <div>
      <a href="{{ route('muni.dashboard', request()->only('periodo_id')) }}"
         class="inline-flex items-center px-4 py-2 rounded-md ring-1 ring-gray-300 hover:bg-gray-50">
        ← Volver al listado
      </a>
    </div>
  </div>
@endsection
