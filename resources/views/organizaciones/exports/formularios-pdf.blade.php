{{-- resources/views/organizaciones/exports/formularios-pdf.blade.php --}}
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <style>
    body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
    table { width: 100%; border-collapse: collapse; }
    th, td { border: 1px solid #ccc; padding: 6px; vertical-align: top; }
    th { background: #f2f2f2; }
    h2 { margin: 0 0 10px 0; }
  </style>
</head>
<body>
  <h2>Programa Navidad – Formularios de {{ $organizacion->nombre ?? $organizacion->id }}</h2>
  <table>
    <thead>
      <tr>
        <th>Formulario</th><th>Estado</th><th>Creado</th><th>Periodo</th>
        <th>ID Ben.</th><th>RUT</th><th>Nombre</th><th>F.Nac.</th><th>Sexo</th><th>Dirección</th>
      </tr>
    </thead>
    <tbody>
      @foreach($formularios as $form)
        @if($form->beneficiarios->isEmpty())
          <tr>
            <td>{{ $form->id }}</td>
            <td>{{ $form->estado }}</td>
            <td>{{ $form->created_at?->format('Y-m-d H:i') }}</td>
            <td>{{ $form->periodo->anio ?? '—' }} ({{ $form->periodo->estado ?? '—' }})</td>
            <td colspan="6" style="text-align:center;color:#777;">Sin beneficiarios</td>
          </tr>
        @else
          @foreach($form->beneficiarios as $b)
            <tr>
              <td>{{ $form->id }}</td>
              <td>{{ $form->estado }}</td>
              <td>{{ $form->created_at?->format('Y-m-d H:i') }}</td>
              <td>{{ $form->periodo->anio ?? '—' }} ({{ $form->periodo->estado ?? '—' }})</td>
              <td>{{ $b->id }}</td>
              <td>{{ $b->rut }}</td>
              <td>{{ $b->nombre_completo }}</td>
              <td>{{ $b->fecha_nacimiento }}</td>
              <td>{{ $b->sexo }}</td>
              <td>{{ $b->direccion }}</td>
            </tr>
          @endforeach
        @endif
      @endforeach
    </tbody>
  </table>
</body>
</html>
