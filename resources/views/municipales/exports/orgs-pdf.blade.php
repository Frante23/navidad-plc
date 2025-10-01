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
        <th>ID</th>
        <th>Organización</th>
        <th>PJ</th>
        <th>Estado</th>
        <th>Formularios</th>
        <th>Beneficiarios</th>
        <th>Creada</th>
      </tr>
    </thead>
    <tbody>
      @foreach($rows as $r)
        <tr>
          <td>{{ $r->id }}</td>
          <td>{{ $r->nombre }}</td>
          <td>{{ $r->pj }}</td>
          <td>{{ ucfirst($r->estado) }}</td>
          <td>{{ $r->formularios }}</td>
          <td>{{ $r->beneficiarios }}</td>
          <td>{{ optional($r->created_at)->format('Y-m-d H:i') }}</td>
        </tr>
      @endforeach
    </tbody>
  </table>
</body>
</html>
