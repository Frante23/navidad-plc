@extends('layouts.app')

@section('content')
  @include('municipales.partials.header', ['funcionario' => $funcionario ?? null])

  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-6">
    <div class="flex gap-6">
      @include('municipales.partials.sidebar')

      <section class="flex-1 space-y-4">
        <div class="bg-white rounded-xl shadow p-4">
          <div class="flex items-center justify-between">
            <h2 class="text-xl font-semibold">RUTs duplicados</h2>
            <form method="GET" class="flex items-center gap-2">
              <select name="periodo_id" class="border rounded-md px-3 py-2">
                <option value="">Todos los períodos</option>
                @foreach($periodos as $p)
                  <option value="{{ $p->id }}" @selected($periodoSel==$p->id)>{{ $p->anio }} ({{ $p->estado }})</option>
                @endforeach
              </select>
              <button class="bg-blue-600 text-white px-4 py-2 rounded-md">Filtrar</button>
            </form>
          </div>
        </div>

        <div class="bg-white rounded-xl shadow overflow-hidden">
          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">RUT</th>
                  <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Beneficiario</th>
                  <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nac.</th>
                  <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Organización</th>
                  <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">PJ</th>
                  <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Formulario</th>
                  <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Periodo</th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                @forelse($rows as $r)
                  <tr>
                    <td class="px-4 py-3 text-sm font-mono">{{ $r->rut }}</td>
                    <td class="px-4 py-3 text-sm">{{ $r->nombre_completo }}</td>
                    <td class="px-4 py-3 text-sm">{{ $r->fecha_nacimiento }}</td>
                    <td class="px-4 py-3 text-sm">{{ $r->organizacion }}</td>
                    <td class="px-4 py-3 text-sm">{{ $r->pj }}</td>
                    <td class="px-4 py-3 text-sm">#{{ $r->formulario_id }}</td>
                    <td class="px-4 py-3 text-sm">{{ $r->periodo ?? '—' }}</td>
                  </tr>
                @empty
                  <tr>
                    <td colspan="7" class="px-4 py-6 text-center text-sm text-gray-500">
                      No se encontraron duplicados con los filtros actuales.
                    </td>
                  </tr>
                @endforelse
              </tbody>
            </table>
          </div>
          <div class="px-4 py-3 border-t">
            {{ $rows->links() }}
          </div>
        </div>
      </section>
    </div>
  </div>
@endsection
