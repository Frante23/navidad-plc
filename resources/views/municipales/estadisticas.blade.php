@extends('layouts.app')

@section('content')
  @include('municipales.partials.header', ['funcionario' => $funcionario])

  <div class="max-w-7xl mx-auto px-4 py-6">
    <div class="flex gap-6">
      @include('municipales.partials.sidebar')

      <div class="flex-1 space-y-6">

        <form method="GET" class="flex items-center gap-2">
          <label class="text-sm text-gray-600">Período</label>
          <input type="number" name="periodo_id" value="{{ $periodoId ?? '' }}"
                 class="border rounded px-2 py-1 w-32" placeholder="ID">
          <button class="bg-blue-600 text-white px-3 py-2 rounded">Aplicar</button>
          @if(request()->has('periodo_id') && request('periodo_id')!=='')
            <a href="{{ route('muni.estadisticas') }}" class="ml-2 text-sm text-gray-600 underline">Limpiar</a>
          @endif
        </form>

        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
          <div class="bg-white rounded-xl shadow p-4">
            <div class="text-sm text-gray-500">Organizaciones</div>
            <div class="text-2xl font-semibold">{{ $totalOrgs }}</div>
          </div>
          <div class="bg-white rounded-xl shadow p-4">
            <div class="text-sm text-gray-500">Activas</div>
            <div class="text-2xl font-semibold text-green-600">{{ $activos }}</div>
          </div>
          <div class="bg-white rounded-xl shadow p-4">
            <div class="text-sm text-gray-500">Pendientes</div>
            <div class="text-2xl font-semibold text-yellow-600">{{ $pendientes }}</div>
          </div>
          <div class="bg-white rounded-xl shadow p-4">
            <div class="text-sm text-gray-500">Inactivas</div>
            <div class="text-2xl font-semibold text-red-600">{{ $inactivos }}</div>
          </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div class="bg-white rounded-xl shadow p-4">
            <div class="text-sm text-gray-500">Formularios abiertos</div>
            <div class="text-3xl font-semibold text-green-700">{{ $formsAbiertos }}</div>
          </div>
          <div class="bg-white rounded-xl shadow p-4">
            <div class="text-sm text-gray-500">Formularios cerrados</div>
            <div class="text-3xl font-semibold text-gray-700">{{ $formsCerrados }}</div>
          </div>
        </div>

        <div class="bg-white rounded-xl shadow p-4">
          <div class="mb-2 font-semibold">Organizaciones por estado</div>
          <canvas id="chartEstados" height="100"></canvas>
        </div>

        {{-- Top 10 organizaciones por beneficiarios --}}
        <div class="bg-white rounded-xl shadow overflow-hidden">
          <div class="px-4 py-3 border-b font-semibold">Top 10 por beneficiarios</div>
          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">#</th>
                  <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Organización</th>
                  <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Beneficiarios</th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                @foreach($topOrgs as $i => $o)
                  <tr>
                    <td class="px-4 py-2 text-sm">{{ $i+1 }}</td>
                    <td class="px-4 py-2 text-sm">{{ $o->nombre }}</td>
                    <td class="px-4 py-2 text-sm">{{ $o->total_beneficiarios }}</td>
                  </tr>
                @endforeach
              </tbody>
            </table>
          </div>
        </div>

        {{-- Distribución por tipo --}}
        <div class="bg-white rounded-xl shadow overflow-hidden">
          <div class="px-4 py-3 border-b font-semibold">Distribución por tipo de organización</div>
          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Tipo</th>
                  <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Total</th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                @foreach($porTipo as $t)
                  <tr>
                    <td class="px-4 py-2 text-sm">{{ $t->tipo }}</td>
                    <td class="px-4 py-2 text-sm">{{ $t->c }}</td>
                  </tr>
                @endforeach
              </tbody>
            </table>
          </div>
        </div>

      </div> {{-- /flex-1 --}}
    </div> {{-- /flex --}}
  </div> {{-- /container --}}

  {{-- Chart.js CDN --}}
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script>
    const ctx = document.getElementById('chartEstados').getContext('2d');
    new Chart(ctx, {
      type: 'bar',
      data: {
        labels: @json($chartLabels),
        datasets: [{
          label: 'Organizaciones',
          data: @json($chartData)
        }]
      },
      options: {
        responsive: true,
        plugins: { legend: { display: false } },
        scales: { y: { beginAtZero: true, ticks: { precision:0 } } }
      }
    });
  </script>
@endsection


