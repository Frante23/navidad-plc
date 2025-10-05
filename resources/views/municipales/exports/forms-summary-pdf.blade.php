<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <style>
    body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
    table { border-collapse: collapse; width: 100%; margin-bottom: 20px; }
    th, td { border: 1px solid #999; padding: 6px; }
    th { background: #f2f2f2; }
    .title { margin-bottom: 8px; font-weight: bold; }
    .nota { margin-top: 15px; padding: 8px; border: 1px solid #ccc; background: #fafafa; }
  </style>
</head>
<body>
  <div class="title">
    Organización: {{ $org->nombre }} ({{ $org->personalidad_juridica }})
  </div>

  <table>
    <thead>
      <tr>
        <th>ID</th>
        <th>Periodo</th>
        <th>Estado</th>
        <th>Beneficiarios</th>
        <th>Creado</th>
      </tr>
    </thead>
    <tbody>
      @foreach($rows as $r)
        <tr>
          <td>{{ $r->id }}</td>
          <td>{{ $r->periodo ?? '—' }}</td>
          <td>{{ ucfirst($r->estado) }}</td>
          <td>{{ (int) $r->beneficiarios }}</td>
          <td>{{ \Carbon\Carbon::parse($r->created_at)->format('d-m-Y H:i') }}</td>
        </tr>
      @endforeach
    </tbody>
  </table>

  <div class="nota">
    <strong>Nota municipal:</strong><br>
    {{ $org->nota_muni ?? '—' }}
  </div>
</body>
</html>
