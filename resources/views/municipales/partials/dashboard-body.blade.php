@if(session('status'))
  <div class="mb-4 bg-green-100 text-green-800 border border-green-300 px-4 py-2 rounded">
    {{ session('status') }}
  </div>
@endif

<form method="GET" action="{{ route('muni.dashboard') }}"
      class="bg-white p-4 rounded-xl shadow flex flex-wrap gap-3 items-end"
      id="filtrosForm">

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
        <option value="{{ $p->id }}" @selected(($periodoSel ?? null)==$p->id)>
          {{ $p->anio }} ({{ $p->estado }})
        </option>
      @endforeach
    </select>
  </div>

  <div>
    <label class="block text-sm text-gray-700 mb-1">Estado</label>
    <select name="estado" class="border rounded-md px-3 py-2" id="estadoSelect">
      <option value="">Todos</option>
      <option value="activo"    @selected(($estadoSel ?? '')==='activo')>Activos</option>
      <option value="pendiente" @selected(($estadoSel ?? '')==='pendiente')>Pendientes</option>
      <option value="inactivo"  @selected(($estadoSel ?? '')==='inactivo')>Inactivos</option>
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

{{-- autosubmit opcional si quieres que al cambiar “Estado” se envíe solo --}}
<script>
  document.getElementById('estadoSelect')
    ?.addEventListener('change', () => document.getElementById('filtrosForm').submit());
</script>

{{-- Tabla de organizaciones --}}
<div class="bg-white rounded-xl shadow overflow-hidden mt-6">
  <div class="px-4 py-3 border-b">
    <h3 class="font-semibold">Organizaciones</h3>
  </div>

  <div class="overflow-x-auto">
    <table class="min-w-full divide-y divide-gray-200">
      <thead class="bg-gray-50">
        <tr>
          @php
            $dir = request('direction') === 'asc' ? 'desc' : 'asc';
          @endphp
          <thead class="bg-gray-50">
            <tr>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                <a href="{{ route('muni.dashboard', array_merge(request()->all(), ['sort'=>'nombre','direction'=>$dir])) }}">Organización</a>
              </th>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                <a href="{{ route('muni.dashboard', array_merge(request()->all(), ['sort'=>'personalidad_juridica','direction'=>$dir])) }}">PJ</a>
              </th>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                <a href="{{ route('muni.dashboard', array_merge(request()->all(), ['sort'=>'formularios','direction'=>$dir])) }}">Formularios</a>
              </th>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                <a href="{{ route('muni.dashboard', array_merge(request()->all(), ['sort'=>'beneficiarios','direction'=>$dir])) }}">Beneficiarios</a>
              </th>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                <a href="{{ route('muni.dashboard', array_merge(request()->all(), ['sort'=>'estado','direction'=>$dir])) }}">Estado</a>
              </th>
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
            <td class="px-4 py-3 text-sm">
              @php
                $badge = match($org->estado) {
                  'activo'    => 'bg-green-100 text-green-800',
                  'pendiente' => 'bg-yellow-100 text-yellow-800',
                  default     => 'bg-red-100 text-red-800',
                };
              @endphp
              <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $badge }}">
                {{ ucfirst($org->estado) }}
              </span>
            </td>
            <td class="px-4 py-3 text-right space-x-1">
              <a href="{{ route('muni.org.show', ['id'=>$org->id] + request()->only('periodo_id')) }}"
                 class="text-sm font-medium text-indigo-600 hover:text-indigo-800">
                Ver formularios
              </a>

              {{-- Botones de estado --}}
              @if($org->estado === 'pendiente')
            
              @elseif($org->estado === 'activo')
                <form method="POST" action="{{ route('muni.org.setStatus', $org->id) }}" class="inline">
                  @csrf
                  <input type="hidden" name="estado" value="inactivo">
                  <button class="text-xs px-2 py-1 rounded ring-1 ring-red-300 text-red-700 hover:bg-red-50">
                    Desactivar
                  </button>
                </form>
              @elseif($org->estado === 'inactivo')
                <form method="POST" action="{{ route('muni.org.setStatus', $org->id) }}" class="inline">
                  @csrf
                  <input type="hidden" name="estado" value="activo">
                  <button class="text-xs px-2 py-1 rounded ring-1 ring-green-300 text-green-700 hover:bg-green-50">
                    Activar
                  </button>
                </form>
              @endif
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="7" class="px-4 py-6 text-center text-sm text-gray-500">
              No hay resultados para los filtros aplicados.
            </td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>

<div class="mt-4">
  {{ $organizaciones->links() }}
</div>
