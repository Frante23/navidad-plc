<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<style>
  body{ font-family: DejaVu Sans, sans-serif; font-size: 12px; }
  h2{ margin: 0 0 10px 0; }
  table{ width:100%; border-collapse: collapse; }
  th,td{ border:1px solid #888; padding:4px; }
  th{ background:#efefef; }
</style>
</head>
<body>
  <h2>{{ $titulo ?? 'Exportación' }}</h2>


  <table>
    <thead>
      <tr>
        <th>Organización</th>
        <th>PJ</th>
        <th>Formulario</th>
        <th>Estado</th>
        <th>Periodo</th>
        <th>Ben ID</th>
        <th>RUT</th>
        <th>Nombre</th>
        <th>F. Nac</th>
        <th>Sexo</th>
        <th>Dirección</th>
        <th>Tramo</th>

        {{-- ⬇️ Nuevas columnas --}}
        <th>RUT Jefe Hogar</th>
        <th>% RSH</th>
        <th>Observaciones</th>
        <th>Estado revisión</th>

        <th>Creado</th>
      </tr>
    </thead>
    <tbody>
      @foreach($rows as $r)
        <tr>
          <td>{{ $r->organizacion }}</td>
          <td>{{ $r->pj }}</td>
          <td>{{ $r->formulario_id }}</td>
          <td>{{ $r->formulario_estado }}</td>
          <td>{{ $r->periodo_anio }}</td>
          <td>{{ $r->beneficiario_id }}</td>
          <td>{{ $r->rut }}</td>
          <td>{{ $r->nombre_completo }}</td>
          <td>{{ $r->fecha_nacimiento }}</td>
          <td>{{ $r->sexo }}</td>
          <td>{{ $r->direccion }}</td>
          <td>{{ $r->nombre_tramo }}</td>

          {{-- ⬇️ Nuevas celdas con fallback --}}
          <td>{{ $r->rut_jefe_hogar ?? '—' }}</td>
          <td>{{ $r->porcentaje_rsh !== null ? $r->porcentaje_rsh.'%' : '—' }}</td>
          <td>{{ $r->observaciones ?? '—' }}</td>
          <td>
            @php
              $estado = $r->aceptado;
              echo $estado === 1 ? 'Aceptado' : ($estado === 0 ? 'Rechazado' : 'Pendiente');
            @endphp
          </td>

          <td>{{ $r->beneficiario_creado }}</td>
        </tr>
      @endforeach
    </tbody>
  </table>
</body>
</html>
