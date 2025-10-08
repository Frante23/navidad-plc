@php($funcionario = $funcionario ?? auth('func')->user())
<header class="bg-blue-600 shadow">
  <div class="mx-auto max-w-7xl px-4 py-4 sm:px-6 lg:px-8 flex items-center justify-between">

    <div class="flex items-center space-x-4">
      <img src="{{ asset('images/logo_muni1.png') }}" 
           alt="Logo Municipalidad" 
           class="h-20 w-auto object-contain">

      <div class="flex flex-col justify-center">
        <h1 class="text-2xl md:text-3xl font-bold tracking-tight text-white">
          Programa Navidad - Municipales – Municipalidad de Padre Las Casas
        </h1>
        <p class="mt-1 text-sm text-blue-100">
          Bienvenido: <span class="font-semibold">{{ $funcionario->nombre_completo ?? 'Funcionario' }}</span>
        </p>
      </div>
    </div>

    <form method="POST" action="{{ route('logout.funcionarios') }}">
        @csrf
        <button type="submit"
                class="hover:bg-red-700 text-white px-4 py-2 rounded-md text-sm font-medium">
            Cerrar sesión
        </button>
    </form>
  </div>
</header>



