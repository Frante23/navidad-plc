<table border="1">
    <thead>
        <tr>
            <th>Organización</th>
            <th>PJ</th>
            <th>FormularioID</th>
            <th>Estado</th>
            <th>Periodo</th>
            <th>BeneficiarioID</th>
            <th>RUT</th>
            <th>Nombre</th>
            <th>Fecha Nac.</th>
            <th>Sexo</th>
            <th>Dirección</th>
            <th>Tramo</th>
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
            <td>{{ $r->beneficiario_creado }}</td>
        </tr>
    @endforeach
    </tbody>
</table>
