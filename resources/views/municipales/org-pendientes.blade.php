@extends('layouts.app')

@section('content')
  @include('municipales.partials.header', ['funcionario' => $funcionario])

  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-6 flex gap-6">
    @include('municipales.partials.sidebar')

    <div class="flex-1">
      <div class="bg-white rounded-xl shadow">
        <div class="px-4 py-3 border-b flex items-center justify-between">
          <h2 class="font-semibold">Organizaciones pendientes</h2>
          @if(session('status'))
            <div class="text-sm text-green-700">{{ session('status') }}</div>
          @endif
        </div>

        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Organización</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">PJ</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Creado</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Acciones</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              @forelse($pendientes as $org)
                <tr>
                  <td class="px-4 py-3">{{ $org->nombre }}</td>
                  <td class="px-4 py-3">{{ $org->personalidad_juridica }}</td>
                  <td class="px-4 py-3">{{ $org->email ?: '—' }}</td>
                  <td class="px-4 py-3">{{ $org->created_at?->format('d-m-Y H:i') }}</td>
                  <td class="px-4 py-3">
                    <div class="flex items-center gap-3">
                      {{-- Aprobar con contraseña --}}
                      <form method="POST" action="{{ route('muni.org.aprobar', $org->id) }}" class="flex items-center gap-2">
                        @csrf
                        <input type="password" name="clave" class="border rounded px-2 py-1 text-sm" placeholder="Nueva contraseña" required>
                        <input type="password" name="clave_confirmation" class="border rounded px-2 py-1 text-sm" placeholder="Confirmar" required>
                        <button class="bg-green-600 text-white text-sm px-3 py-1 rounded hover:bg-green-700">Aprobar</button>
                      </form>

                      {{-- Rechazar --}}
                      <form method="POST" action="{{ route('muni.org.rechazar', $org->id) }}">
                        @csrf
                        <button class="bg-red-600 text-white text-sm px-3 py-1 rounded hover:bg-red-700">Rechazar</button>
                      </form>
                    </div>
                  </td>
                </tr>
              @empty
                <tr><td colspan="5" class="px-4 py-6 text-center text-gray-500">No hay organizaciones pendientes.</td></tr>
              @endforelse
            </tbody>
          </table>
        </div>

        <div class="px-4 py-3 border-t">
          {{ $pendientes->links() }}
        </div>
      </div>
    </div>
  </div>
@endsection
