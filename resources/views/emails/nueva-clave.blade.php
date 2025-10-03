<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
</head>
<body>
  <p>Estimado/a {{ $org->nombre }},</p>

  <p>Su nueva contraseña para acceder al sistema es:</p>

  <h2 style="color:#2d3748;">{{ $clave }}</h2>

  <p>Atentamente,<br>
  Programa Navidad - Municipalidad de Padre Las Casas</p>
</body>
</html>
