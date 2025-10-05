<table border="1" cellspacing="0" cellpadding="4">
  <thead>
    <tr>
      <th colspan="5" style="text-align:left;">
        Organización: {{ $org->nombre }} ({{ $org->personalidad_juridica }})
      </th>
    </tr>
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

<table border="1" cellspacing="0" cellpadding="4" style="margin-top:10px;">
  <tr>
    <th style="background:#f2f2f2; text-align:left;">Nota municipal</th>
  </tr>
  <tr>
    <td>{{ $org->nota_muni ?? '—' }}</td>
  </tr>
</table>
