{{-- Filtros / Buscador --}}
<form method="GET" action="{{ route('muni.dashboard') }}"
      class="bg-white p-4 rounded-xl shadow flex flex-wrap gap-3 items-end">
  <div class="flex-1 min-w-[260px]">
    <label class="block text-sm text-gray-700 mb-1">Buscar organización</label>
    <input type="text" name="q" value="{{ $q }}" placeholder="Nombre o Personalidad Jurídica"
           class="w-full border rounded-md px-3 py-2">
  </div>

  <div>
    <label class="block text-sm text-gray-700 mb-1">Período</label>
    <select name="periodo_id" class="border rounded-md px-3 py-2">
      <option value="">Todos</option>
      @foreach($periodos as $p)
        <option value="{{ $p->id }}" @selected($periodoSel==$p->id)>
          {{ $p->anio }} ({{ $p->estado }})
        </option>
      @endforeach
    </select>
  </div>

  <div class="ml-auto flex gap-2">
    <a href="{{ route('muni.export.xlsx', request()->query()) }}"
       class="bg-gray-100 text-gray-800 px-4 py-2 rounded-md ring-1 ring-gray-300 hover:bg-gray-200">
      Descargar Excel
    </a>
    <a href="{{ route('muni.export.pdf', request()->query()) }}"
       class="bg-gray-100 text-gray-800 px-4 py-2 rounded-md ring-1 ring-gray-300 hover:bg-gray-200">
      Descargar PDF
    </a>
  </div>
</form>

{{-- Tabla de organizaciones --}}
<div class="bg-white rounded-xl shadow overflow-hidden">
  <div class="px-4 py-3 border-b">
    <h3 class="font-semibold">Organizaciones</h3>
  </div>

  <div class="overflow-x-auto">
    <table class="min-w-full divide-y divide-gray-200">
      <thead class="bg-gray-50">
        <tr>
          <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Organización</th>
          <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">PJ</th>
          <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Formularios</th>
          <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Beneficiarios</th>
          <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Por tramo</th>
          <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Acciones</th>
        </tr>
      </thead>
      <tbody class="bg-white divide-y divide-gray-200">
        @forelse($organizaciones as $org)
          @php
            $fStats   = $formStats[$org->id] ?? collect();
            $abiertos = $fStats->firstWhere('estado','abierto')->c ?? 0;
            $cerrados = $fStats->firstWhere('estado','cerrado')->c ?? 0;
            $totalBen = $benTotals[$org->id] ?? 0;

            $tramoCounts = collect();
            if(isset($benByTramo[$org->id])) {
              foreach($benByTramo[$org->id] as $row){
                $tramoCounts[$row->tramo_id] = $row->c;
              }
            }
          @endphp
          <tr>
            <td class="px-4 py-3 text-sm text-gray-900">{{ $org->nombre }}</td>
            <td class="px-4 py-3 text-sm text-gray-700">{{ $org->personalidad_juridica }}</td>
            <td class="px-4 py-3 text-sm">
              <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium bg-green-100 text-green-800">
                Abiertos: {{ $abiertos }}
              </span>
              <span class="ml-2 inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium bg-gray-100 text-gray-800">
                Cerrados: {{ $cerrados }}
              </span>
            </td>
            <td class="px-4 py-3 text-sm">{{ $totalBen }}</td>
            <td class="px-4 py-3 text-sm">
              <div class="flex flex-wrap gap-1">
                @foreach($tramos as $t)
                  @php $c = $tramoCounts[$t->id] ?? 0; @endphp
                  <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium
                               {{ $c>0 ? 'bg-indigo-100 text-indigo-800' : 'bg-gray-100 text-gray-500' }}">
                    {{ $t->nombre_tramo ?? ($t->etiqueta ?? "T{$t->id}") }}: {{ $c }}
                  </span>
                @endforeach
              </div>
            </td>
            <td class="px-4 py-3 text-right">
              <a href="{{ route('muni.org.show', ['id'=>$org->id] + request()->only('periodo_id')) }}"
                 class="text-sm font-medium text-indigo-600 hover:text-indigo-800">
                Ver formularios
              </a>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="6" class="px-4 py-6 text-center text-sm text-gray-500">
              No hay resultados para los filtros aplicados.
            </td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>
