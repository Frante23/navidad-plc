@extends('layouts.app')

@section('content')
  @include('municipales.partials.header')

  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
    <div class="flex gap-6">
      @include('municipales.partials.sidebar')

      <main class="flex-1">
        <h1 class="text-2xl font-bold mb-6">Funcionarios municipales</h1>

        @if (session('success'))
          <div class="mb-4 p-3 bg-green-100 text-green-800 rounded">{{ session('success') }}</div>
        @endif
        @if (session('error'))
          <div class="mb-4 p-3 bg-red-100 text-red-700 rounded">{{ session('error') }}</div>
        @endif

        {{-- Barra de búsqueda y filtros --}}
        <form method="GET" class="mb-4 bg-white rounded shadow p-4 flex flex-wrap items-end gap-3">
          <div class="flex-1 min-w-[240px]">
            <label class="block text-sm text-gray-700 mb-1">Buscar (RUT o nombre)</label>
            <input type="text" name="q" value="{{ $q ?? '' }}" class="w-full border rounded px-3 py-2"
                   placeholder="Ej: 11.111.111-1 o Juan Pérez">
          </div>

          <div>
            <label class="block text-sm text-gray-700 mb-1">Rol</label>
            <select name="rol" class="border rounded px-3 py-2">
              <option value="todos"   @selected(($rol ?? 'todos')==='todos')>Todos</option>
              <option value="admins"  @selected(($rol ?? '')==='admins')>Admins</option>
              <option value="usuarios"@selected(($rol ?? '')==='usuarios')>Usuarios</option>
            </select>
          </div>

          <div class="ml-auto flex gap-2">
            <button class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded">Aplicar</button>
            <a href="{{ route('funcionarios.index') }}" class="px-4 py-2 border rounded">Limpiar</a>
          </div>
        </form>

        @php
            $baseQuery = request()->except('page');

            $sortLink = function ($col) use ($baseQuery) {
                $currentSort = request('sort', 'nombre_completo');
                $currentDir  = strtolower(request('dir', 'asc')) === 'desc' ? 'desc' : 'asc';
                $nextDir     = ($currentSort === $col && $currentDir === 'asc') ? 'desc' : 'asc';
                return route('funcionarios.index', array_merge($baseQuery, ['sort' => $col, 'dir' => $nextDir]));
            };

            $sortIcon = function ($col) {
                $currentSort = request('sort', 'nombre_completo');
                $currentDir  = strtolower(request('dir', 'asc')) === 'desc' ? 'desc' : 'asc';
                if ($currentSort !== $col) return '';
                return $currentDir === 'asc' ? '▲' : '▼';
            };
            @endphp

            <div class="overflow-x-auto bg-white rounded shadow">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-2 text-left">
                    <a href="{{ $sortLink('nombre_completo') }}" class="inline-flex items-center gap-1 hover:underline">
                        Nombre <span class="text-gray-400">{{ $sortIcon('nombre_completo') }}</span>
                    </a>
                    </th>
                    <th class="px-4 py-2 text-left">
                    <a href="{{ $sortLink('rut') }}" class="inline-flex items-center gap-1 hover:underline">
                        RUT <span class="text-gray-400">{{ $sortIcon('rut') }}</span>
                    </a>
                    </th>
                    <th class="px-4 py-2 text-left">Correo</th>
                    <th class="px-4 py-2 text-left">Teléfono</th>
                    <th class="px-4 py-2 text-left">Cargo</th>
                    <th class="px-4 py-2 text-left">Rol</th>
                    <th class="px-4 py-2 text-center">Admin</th>
                    <th class="px-4 py-2 text-center">Eliminar</th>
                </tr>
                </thead>


            <tbody>
              @forelse ($funcionarios as $f)
                <tr class="border-t hover:bg-gray-50">
                  <td class="px-4 py-2">{{ $f->nombre_completo }}</td>
                  <td class="px-4 py-2">{{ $f->rut }}</td>
                  <td class="px-4 py-2">{{ $f->correo }}</td>
                  <td class="px-4 py-2">{{ $f->telefono_contacto ?? '—' }}</td>
                  <td class="px-4 py-2">{{ $f->cargo ?? '—' }}</td>

                  <td class="px-4 py-2">
                    @if ($f->es_admin)
                      <span class="inline-flex items-center px-2 py-0.5 bg-green-100 text-green-800 rounded text-xs font-medium">
                        Admin
                      </span>
                    @else
                      <span class="inline-flex items-center px-2 py-0.5 bg-gray-100 text-gray-800 rounded text-xs font-medium">
                        Funcionario
                      </span>
                    @endif
                  </td>

                  {{-- Columna Admin (toggle) --}}
                  <td class="px-4 py-2 text-center">
                    <form method="POST" action="{{ route('funcionarios.toggleAdmin', $f->id) }}"
                          onsubmit="return confirm('¿Confirmas cambiar el rol de {{ $f->nombre_completo }}?');"
                          class="inline">
                      @csrf @method('PATCH')
                      <button
                        class="px-3 py-1 rounded text-white text-sm font-medium transition
                               {{ $f->es_admin
                                  ? 'bg-red-500 hover:bg-red-600 focus:ring-2 focus:ring-red-300'
                                  : 'bg-emerald-500 hover:bg-emerald-600 focus:ring-2 focus:ring-emerald-300' }}">
                        {{ $f->es_admin ? 'Quitar' : 'Dar' }}
                      </button>
                    </form>
                  </td>

                  {{-- Columna Eliminar --}}
                  <td class="px-4 py-2 text-center">
                    @if(auth('func')->id() !== $f->id)
                      <form method="POST" action="{{ route('funcionarios.destroy', $f->id) }}"
                            onsubmit="return confirm('¿Seguro que deseas eliminar a {{ $f->nombre_completo }}? Esta acción no se puede deshacer.');"
                            class="inline">
                        @csrf @method('DELETE')
                        <button
                          class="px-3 py-1 rounded text-white text-sm font-medium transition
                                 bg-red-600 hover:bg-red-700 focus:ring-2 focus:ring-red-400">
                          Borrar
                        </button>
                      </form>
                    @else
                      <span class="text-gray-400 text-xs italic">—</span>
                    @endif
                  </td>
                </tr>
              @empty
                <tr><td colspan="8" class="px-4 py-6 text-center text-gray-500">Sin resultados.</td></tr>
              @endforelse
            </tbody>
          </table>
        </div>

        <div class="mt-4">
          {{ $funcionarios->links() }}
        </div>
      </main>
    </div>
  </div>
@endsection
