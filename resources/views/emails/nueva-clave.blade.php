<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
</head>
<body style="font-family: Arial, sans-serif; color: #2d3748; line-height: 1.6;">

  <p>Estimado/a <strong>{{ $org->nombre }}</strong>,</p>

  <p>Su nueva contraseña para acceder al sistema es:</p>

  <h2 style="color:#2d3748; background-color:#f7fafc; padding:8px 12px; border-radius:6px; display:inline-block;">
    {{ $clave }}
  </h2>

  <p>Para iniciar sesión en la plataforma debe ingresar a la siguiente URL:</p>

  <h3 style="color:#1a202c;">https://navidad.participacionciudadanaplc.cl/</h3>

  <hr style="border:none; border-top:2px solid #e53e3e; margin:24px 0;">

  <div style="border:2px solid #e53e3e; background-color:#fff5f5; color:#c53030; padding:16px; border-radius:8px;">
    <h3 style="margin-top:0; text-transform:uppercase; font-weight:bold;">¡Atención! Documentación necesaria</h3>
    <p style="margin:6px 0 10px;">Para completar correctamente su proceso de inscripción, debe entregar los siguientes documentos en físico en la Municipalidad:</p>
    <ul style="margin:0; padding-left:20px;">
      <li>Certificado de Directorio Vigente de la organización.</li>
      <li>Fotocopia de Cédula de Identidad del/de la Presidente(a) de la organización.</li>
      <li>Fotocopia del Acta de Reunión donde se indique que se solicitarán los juguetes y la fecha aproximada de entrega.</li>
      <li>Dos números de contacto telefónicos y dirección especificada para la entrega de los juguetes.</li>
    </ul>
  </div>

  <hr style="border:none; border-top:2px solid #e2e8f0; margin:24px 0;">

  <p>En caso de cualquier inconveniente, puede contactarse al siguiente correo o teléfono:</p>

  <h3 style="margin:6px 0;">jiturra@padrelascasas.cl – 452590252</h3>

  <p>Atentamente,<br>
  <strong>Programa Navidad – Municipalidad de Padre Las Casas</strong></p>

</body>
</html>
