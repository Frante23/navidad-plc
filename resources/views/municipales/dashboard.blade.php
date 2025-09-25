@extends('layouts.app')

@section('content')
  @include('municipales.partials.header', ['funcionario' => $funcionario])

  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-6">
    <div class="flex gap-6">
      {{-- Lateral izquierdo (menú) --}}
      @include('municipales.partials.sidebar')

      {{-- Contenido principal --}}
      <section class="flex-1 space-y-6">
        @include('municipales.partials.dashboard-body')

        {{-- Paginación del listado de organizaciones --}}
        <div class="bg-white rounded-b-xl px-4 py-3 border">
          {{ $organizaciones->links() }}
        </div>
      </section>
    </div>
  </div>
@endsection
