# Proyecto de Navidad - v0.1

Posteriormente de clonar el proyecto para tener el .env, debe ejecutar lo siguiente:

```bash
# Instalar dependencias de PHP
composer install

# Instalar dependencias de Node.js
npm install

# Copiar archivo de configuración de entorno
cp .env.example .env

# Generar la clave de la aplicación Laravel
php artisan key:generate
