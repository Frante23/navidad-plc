@extends('layouts.app')

@section('content')
  @include('municipales.partials.header')

  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
    <div class="flex gap-6">
      @include('municipales.partials.sidebar')

      <main class="flex-1">
        <h1 class="text-2xl font-bold mb-6">Auditoría de acciones</h1>

        <form method="GET" class="mb-4 bg-white rounded shadow p-4 flex flex-wrap items-end gap-3">
          <div class="min-w-[260px]">
            <label class="block text-sm text-gray-700 mb-1">Buscar</label>
            <input name="q" value="{{ $q }}" class="border rounded px-3 py-2 w-full" placeholder="correo, nombre, acción, texto…">
          </div>
          <div>
            <label class="block text-sm text-gray-700 mb-1">Funcionario</label>
            <select name="actor_id" class="border rounded px-3 py-2">
              <option value="">Todos</option>
              @foreach($funcionarios as $f)
                <option value="{{ $f->id }}" @selected($actorId==$f->id)>{{ $f->nombre_completo }} ({{ $f->correo }})</option>
              @endforeach
            </select>
          </div>
          <div>
            <label class="block text-sm text-gray-700 mb-1">Acción</label>
            <select name="accion" class="border rounded px-3 py-2">
              <option value="">Todas</option>
              @foreach($acciones as $a)
                <option value="{{ $a }}" @selected($accion===$a)>{{ $a }}</option>
              @endforeach
            </select>
          </div>
          <div>
            <label class="block text-sm text-gray-700 mb-1">Desde</label>
            <input type="date" name="desde" value="{{ $desde }}" class="border rounded px-3 py-2">
          </div>
          <div>
            <label class="block text-sm text-gray-700 mb-1">Hasta</label>
            <input type="date" name="hasta" value="{{ $hasta }}" class="border rounded px-3 py-2">
          </div>
          <div class="ml-auto flex gap-2">
            <button class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded">Filtrar</button>
            <a href="{{ route('muni.auditoria') }}" class="px-4 py-2 border rounded">Limpiar</a>
          </div>
        </form>

        <div class="bg-white rounded shadow overflow-x-auto">
          <table class="min-w-full text-sm">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-4 py-2 text-left">Fecha</th>
                <th class="px-4 py-2 text-left">Funcionario</th>
                <th class="px-4 py-2 text-left">Acción</th>
                <th class="px-4 py-2 text-left">Entidad</th>
                <th class="px-4 py-2 text-left">Descripción</th>
                <th class="px-4 py-2 text-left">IP</th>
                <th class="px-4 py-2 text-left">Extra</th>
              </tr>
            </thead>
            <tbody>
              @forelse($logs as $l)
                @php
                  $extra = is_string($l->extra_json) ? json_decode($l->extra_json, true) : $l->extra_json;
                @endphp
                <tr class="border-t">
                  <td class="px-4 py-2">{{ \Carbon\Carbon::parse($l->created_at)->format('d-m-Y H:i:s') }}</td>
                  <td class="px-4 py-2">
                    {{ $l->actor_nombre ?? '—' }}
                    @if($l->actor_correo)
                      <div class="text-xs text-gray-500">{{ $l->actor_correo }}</div>
                    @endif
                  </td>
                  <td class="px-4 py-2">
                    <span class="inline-block px-2 py-0.5 rounded bg-gray-100 text-gray-700">{{ $l->accion }}</span>
                  </td>
                  <td class="px-4 py-2">{{ $l->entidad }} @if($l->entidad_id)#{{ $l->entidad_id }}@endif</td>
                  <td class="px-4 py-2">{{ $l->descripcion }}</td>
                  <td class="px-4 py-2">{{ $l->ip }}</td>
                  <td class="px-4 py-2">
                    @if(!empty($extra))
                      <details class="text-xs">
                        <summary class="cursor-pointer text-indigo-600">ver</summary>
                        <pre class="whitespace-pre-wrap">{{ json_encode($extra, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE) }}</pre>
                      </details>
                    @else
                      —
                    @endif
                  </td>
                </tr>
              @empty
                <tr>
                  <td colspan="7" class="px-4 py-6 text-center text-gray-500">Sin registros.</td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>

        <div class="mt-4">{{ $logs->links() }}</div>
      </main>
    </div>
  </div>
@endsection
