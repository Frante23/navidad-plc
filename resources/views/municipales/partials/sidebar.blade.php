@php($user = auth('func')->user())

<aside class="hidden md:block w-64 shrink-0">
  <nav class="bg-white rounded-xl shadow p-4 space-y-1 sticky top-6">

    {{-- SIEMPRE visible para funcionarios (admin y no-admin) --}}
    <a href="{{ route('muni.dashboard') }}"
       class="flex items-center gap-3 px-3 py-2 rounded-md hover:bg-gray-50
              {{ request()->routeIs('muni.dashboard') ? 'bg-gray-100 font-semibold' : '' }}">
      <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
              d="M3 12l9-9 9 9M4 10v10a1 1 0 001 1h5m4 0h5a1 1 0 001-1V10" />
      </svg>
      <span>Panel general</span>
    </a>

    {{-- SOLO ADMIN: Gestión --}}
    @if($user && $user->es_admin)
      <div class="pt-3 pb-1 text-xs uppercase tracking-wide text-gray-400">Gestión</div>

      <a href="{{ route('muni.org.create') }}"
         class="flex items-center gap-3 px-3 py-2 rounded-md hover:bg-gray-50
                {{ request()->routeIs('muni.org.create') ? 'bg-gray-100 font-semibold' : '' }}">
        <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 6v12m6-6H6" />
        </svg>
        <span>Crear agrupación</span>
      </a>

      <a href="{{ route('muni.org.pendientes') }}"
         class="flex items-center gap-3 px-3 py-2 rounded-md hover:bg-gray-50
                {{ request()->routeIs('muni.org.pendientes') ? 'bg-gray-100 font-semibold' : '' }}">
        <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                d="M9 12h6m2 0a8 8 0 10-16 0 8 8 0 0016 0z" />
        </svg>
        <span>Habilitación de organizaciones</span>
      </a>

      <a href="{{ route('muni.duplicados') }}"
         class="flex items-center gap-3 px-3 py-2 rounded-md hover:bg-gray-50
                {{ request()->routeIs('muni.duplicados') ? 'bg-gray-100 font-semibold' : '' }}">
        <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                d="M17 20h5v-2a4 4 0 00-4-4h-1M9 20H4v-2a4 4 0 014-4h1m6-6a4 4 0 11-8 0 4 4 0 018 0m10 4a4 4 0 11-8 0 4 4 0 018 0" />
        </svg>
        <span>RUTs duplicados</span>
      </a>
    @endif

    <div class="pt-3 pb-1 text-xs uppercase tracking-wide text-gray-400">Reportes</div>

    {{-- Reportes visibles para TODOS --}}
    <a href="{{ route('muni.orgs.export.pdf', request()->only('periodo_id')) }}"
       class="flex items-center gap-3 px-3 py-2 rounded-md hover:bg-gray-50
              {{ request()->routeIs('muni.orgs.export.pdf') ? 'bg-gray-100 font-semibold' : '' }}">
      <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
              d="M12 20h9M12 4h9M3 8h18M3 16h18" />
      </svg>
      <span>Exportar listado de organizaciones (PDF)</span>
    </a>

    <a href="{{ route('muni.estadisticas', request()->only('periodo_id')) }}"
       class="flex items-center gap-3 px-3 py-2 rounded-md hover:bg-gray-50
              {{ request()->routeIs('muni.estadisticas') ? 'bg-gray-100 font-semibold' : '' }}">
      <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
              d="M3 3v18h18M7 13v5m4-9v9m4-13v13" />
      </svg>
      <span>Estadísticas</span>
    </a>

    {{-- SOLO ADMIN: administración de funcionarios --}}
    @if($user && $user->es_admin)
      <div class="pt-3 pb-1 text-xs uppercase tracking-wide text-gray-400">Administración</div>

      <a href="{{ route('funcionarios.index') }}"
        class="flex items-center gap-3 px-3 py-2 rounded-md hover:bg-gray-50
                {{ request()->routeIs('funcionarios.index') ? 'bg-gray-100 font-semibold' : '' }}">
        <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                d="M12 12c2.21 0 4-1.79 4-4S14.21 4 12 4 8 5.79 8 8s1.79 4 4 4zm0 2c-3.33 0-6 2.67-6 6h12c0-3.33-2.67-6-6-6z" />
        </svg>
        <span>Funcionarios municipales</span>
      </a>

      <a href="{{ route('funcionarios.create') }}"
        class="flex items-center gap-3 px-3 py-2 rounded-md hover:bg-gray-50
                {{ request()->routeIs('funcionarios.create') ? 'bg-gray-100 font-semibold' : '' }}">
        <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 6v12m6-6H6" />
        </svg>
        <span>Nuevo funcionario</span>
      </a>



      <a href="{{ route('muni.auditoria') }}"
        class="flex items-center gap-3 px-3 py-2 rounded-md hover:bg-gray-50
                {{ request()->routeIs('muni.auditoria') ? 'bg-gray-100 font-semibold' : '' }}">
        <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                d="M4 7h16M4 12h16M4 17h16"/>
        </svg>
        <span>Auditoría</span>
      </a>
    @endif


  </nav>
</aside>
