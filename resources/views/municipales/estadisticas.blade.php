@extends('layouts.app')

@section('content')
  @include('municipales.partials.header', ['funcionario' => $funcionario])

  <div class="max-w-7xl mx-auto px-4 py-6">
    <div class="flex gap-6">
      @include('municipales.partials.sidebar')

      <div class="flex-1 space-y-6">

        {{-- Filtros --}}
        <form method="GET" class="flex flex-wrap items-end gap-3">
          <div>
            <label class="block text-xs text-gray-600">Período (ID)</label>
            <input type="number" name="periodo_id" value="{{ $periodoId ?? '' }}"
                   class="border rounded px-2 py-1 w-36" placeholder="ID">
          </div>

          <div>
            <label class="block text-xs text-gray-600">Organización (opcional)</label>
            <select name="org_id" class="border rounded px-2 py-1 w-72">
              <option value="">Todas</option>
              @foreach($orgsSelect as $o)
                <option value="{{ $o->id }}" @selected($orgId==$o->id)>{{ $o->nombre }}</option>
              @endforeach
            </select>
          </div>

          <button class="bg-blue-600 text-white px-3 py-2 rounded">Aplicar</button>

          @if(request()->has('periodo_id') || request()->has('org_id'))
            <a href="{{ route('muni.estadisticas') }}"
               class="text-sm text-gray-600 underline">Limpiar</a>
          @endif
        </form>

        {{-- KPIs --}}
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

        {{-- Formularios abiertos / cerrados --}}
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

        {{-- A) Barra simple: estados de organizaciones (ya lo tenías) --}}
        <div class="bg-white rounded-xl shadow p-4">
          <div class="mb-2 font-semibold">Organizaciones por estado</div>
          <canvas id="chartEstados" height="90"></canvas>
        </div>

        {{-- B) Dona: sexo global --}}
        <div class="bg-white rounded-xl shadow p-4">
          <div class="mb-2 font-semibold">Distribución por sexo (global)</div>
          <canvas id="chartSexo" height="90"></canvas>
        </div>

        {{-- C) Apilado por tramo de edad (M/F/U) --}}
        <div class="bg-white rounded-xl shadow p-4">
          <div class="mb-2 font-semibold">Beneficiarios por tramo de edad y sexo</div>
          <canvas id="chartTramos" height="120"></canvas>
          <small class="text-xs text-gray-500">Apilado: M/F/U</small>
        </div>

        {{-- D) Línea: beneficiarios por mes --}}
        <div class="bg-white rounded-xl shadow p-4">
          <div class="mb-2 font-semibold">Beneficiarios por mes</div>
          <canvas id="chartMeses" height="90"></canvas>
        </div>

        {{-- E) Dispersión: edad (meses) vs %RSH (si hay datos) --}}
        @if(count($scatter)>0)
        <div class="bg-white rounded-xl shadow p-4">
          <div class="mb-2 font-semibold">%RSH vs edad (meses)</div>
          <canvas id="chartScatter" height="110"></canvas>
        </div>
        @endif

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

        {{-- Distribución por tipo de organización --}}
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

  {{-- Chart.js (sin estilos extra) --}}
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script>
    // A) Barra simple: estados
    new Chart(document.getElementById('chartEstados'), {
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

    // B) Dona: sexo global
    const sexo = @json($sexoGlobal); // { M: 10, F: 12, U: 2 }
    const donutLabels = Object.keys(sexo);
    const donutData   = Object.values(sexo);
    new Chart(document.getElementById('chartSexo'), {
      type: 'doughnut',
      data: {
        labels: donutLabels,
        datasets: [{ data: donutData }]
      },
      options: { responsive: true }
    });

    // C) Apilado por tramos M/F/U
    new Chart(document.getElementById('chartTramos'), {
      type: 'bar',
      data: {
        labels: @json($stackTramoLabels),
        datasets: [
          { label: 'M', data: @json($stackM) },
          { label: 'F', data: @json($stackF) },
          { label: 'U', data: @json($stackU) },
        ]
      },
      options: {
        responsive: true,
        plugins: { legend: { position: 'top' } },
        scales: {
          x: { stacked: true },
          y: { stacked: true, beginAtZero: true, ticks: { precision:0 } }
        }
      }
    });

    // D) Línea por mes
    new Chart(document.getElementById('chartMeses'), {
      type: 'line',
      data: {
        labels: @json($mesLabels),
        datasets: [{
          label: 'Beneficiarios',
          data: @json($mesData),
          tension: .3
        }]
      },
      options: {
        responsive: true,
        plugins: { legend: { display: false } },
        scales: { y: { beginAtZero: true, ticks: { precision:0 } } }
      }
    });

    // E) Dispersión: edad (meses) vs %RSH
    @if(count($scatter)>0)
    new Chart(document.getElementById('chartScatter'), {
      type: 'scatter',
      data: {
        datasets: [{
          label: '%RSH vs edad (meses)',
          data: @json($scatter),
          pointRadius: 3
        }]
      },
      options: {
        responsive: true,
        scales: {
          x: { type: 'linear', title: { display: true, text: 'Edad (meses)' } },
          y: { beginAtZero: true, title: { display: true, text: '%RSH' } }
        }
      }
    });
    @endif
  </script>
@endsection
