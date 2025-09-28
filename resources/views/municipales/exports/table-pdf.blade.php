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
  <h2>{{ $titulo }}</h2>

  <table>
    <thead>
      <tr>
        <th>Organización</th>
        <th>PJ</th>
        <th>Formulario</th>
        <th>Periodo</th>
        <th>RUT</th>
        <th>Nombre</th>
        <th>F. Nac</th>
        <th>Sexo</th>
        <th>Dirección</th>
        <th>Tramo</th>
        <th>% RSH</th>
        <th>RUT Jefe Hogar</th>
        <th>Observaciones</th>
        <th>Aceptado</th>
        <th>Creado</th>
      </tr>
    </thead>
    <tbody>
      @foreach($rows as $r)
        <tr>
          <td>{{ $r->organizacion }}</td>
          <td>{{ $r->pj }}</td>
          <td>{{ $r->formulario_id }}</td>
          <td>{{ $r->periodo_anio }}</td>
          <td>{{ $r->rut }}</td>
          <td>{{ $r->nombre_completo }}</td>
          <td>{{ $r->fecha_nacimiento }}</td>
          <td>{{ $r->sexo }}</td>
          <td>{{ $r->direccion }}</td>
          <td>{{ $r->nombre_tramo }}</td>
          <td>{{ $r->porcentaje_rsh }}</td>
          <td>{{ $r->rut_jefe_hogar }}</td>
          <td>{{ $r->observaciones }}</td>
          <td>{{ $r->aceptado ? 'Sí' : 'No' }}</td>
          <td>{{ $r->beneficiario_creado }}</td>
        </tr>
      @endforeach
    </tbody>

  </table>
</body>
</html>
