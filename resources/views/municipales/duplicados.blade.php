@extends('layouts.app')

@section('content')
  @include('municipales.partials.header', ['funcionario' => $funcionario])

  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-6 flex gap-6">
    @include('municipales.partials.sidebar')

    <div class="flex-1 space-y-6">
      <form method="GET" class="bg-white p-4 rounded-xl shadow flex flex-wrap gap-3 items-end">
        <div>
          <label class="block text-sm text-gray-700 mb-1">Período</label>
          <select name="periodo_id" class="border rounded-md px-3 py-2">
            <option value="">Todos</option>
            @foreach($periodos as $p)
              <option value="{{ $p->id }}" @selected(($periodoSel ?? null)==$p->id)>{{ $p->anio }}</option>
            @endforeach
          </select>
        </div>
        <div class="ml-auto">
          <button class="bg-blue-600 text-white px-4 py-2 rounded">Filtrar</button>
        </div>
      </form>

      {{-- Tabla agrupada por RUT --}}
      <div class="bg-white rounded-xl shadow overflow-hidden">
        <div class="px-4 py-3 border-b flex items-center justify-between">
          <h2 class="font-semibold">Intentos de RUT duplicado (agrupados)</h2>
        </div>
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">RUT</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Intentos</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Último intento</th>
                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Acciones</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              @forelse($agrupado as $row)
                <tr>
                  <td class="px-4 py-3">{{ $row->rut }}</td>
                  <td class="px-4 py-3">{{ $row->intentos }}</td>
                  <td class="px-4 py-3">{{ \Carbon\Carbon::parse($row->ultimo_intento)->format('d-m-Y H:i') }}</td>
                  <td class="px-4 py-3 text-right">
                    <a href="{{ route('muni.duplicados', request()->only('periodo_id') + ['rut'=>$row->rut]) }}"
                       class="text-indigo-600 hover:text-indigo-800">Ver detalle</a>
                  </td>
                </tr>
              @empty
                <tr><td colspan="4" class="px-4 py-6 text-center text-gray-500">Sin intentos.</td></tr>
              @endforelse
            </tbody>
          </table>
        </div>
        <div class="px-4 py-3 border-t">
          {{ $agrupado->links() }}
        </div>
      </div>

      {{-- Detalle por RUT (opcional) --}}
      @if($rutDetalle)
        <div class="bg-white rounded-xl shadow overflow-hidden">
          <div class="px-4 py-3 border-b flex items-center justify-between">
            <h2 class="font-semibold">Detalle de intentos para RUT {{ request('rut') }}</h2>
          </div>
          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Fecha intento</th>
                  <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Org. que intentó</th>
                  <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Form. intento</th>
                  <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Ya inscrito en</th>
                  <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Form. existente</th>
                  <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Fecha inscripción</th>
                  <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">IP</th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                @forelse($rutDetalle as $r)
                  <tr>
                    <td class="px-4 py-3">{{ \Carbon\Carbon::parse($r->intento_fecha)->format('d-m-Y H:i') }}</td>
                    <td class="px-4 py-3">{{ $r->intento_org ?? '—' }}</td>
                    <td class="px-4 py-3">#{{ $r->formulario_id ?? '—' }}</td>
                    <td class="px-4 py-3">{{ $r->existe_org ?? '—' }}</td>
                    <td class="px-4 py-3">#{{ $r->existe_en_form_id ?? '—' }}</td>
                    <td class="px-4 py-3">{{ $r->existe_fecha ? \Carbon\Carbon::parse($r->existe_fecha)->format('d-m-Y H:i') : '—' }}</td>
                    <td class="px-4 py-3">{{ $r->ip ?? '—' }}</td>
                  </tr>
                @empty
                  <tr><td colspan="7" class="px-4 py-6 text-center text-gray-500">Sin detalles.</td></tr>
                @endforelse
              </tbody>
            </table>
          </div>
          <div class="px-4 py-3 border-t">
            {{ $rutDetalle->links() }}
          </div>
        </div>
      @endif
    </div>
  </div>
@endsection
