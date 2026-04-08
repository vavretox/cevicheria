# Sistema POS - Cevichería

Sistema de Punto de Venta para restaurante de ceviche y bebidas desarrollado con Laravel, MySQL y Bootstrap.

## Características

- **3 Tipos de Usuario:**
  - Administrador: Gestión completa del sistema
  - Cajero: Procesamiento de pedidos y cobros
  - Mesero: Toma de pedidos con interfaz visual

- **Funcionalidades Principales:**
  - Interfaz visual con imágenes de productos
  - Gestión de pedidos en tiempo real
  - Impresión de boletas de venta
  - Reportes de ventas
  - CRUD completo de productos y usuarios

## Requisitos

- PHP >= 8.1
- Composer
- MySQL >= 5.7
- Node.js y NPM

## Instalación

1. Clonar el repositorio
2. Instalar dependencias de PHP:
```bash
composer install
```

3. Instalar dependencias de Node:
```bash
npm install
npm run build
```

4. Configurar archivo .env:
```bash
cp .env.example .env
php artisan key:generate
```

5. Configurar base de datos en .env:
```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=cevicheria_pos
DB_USERNAME=root
DB_PASSWORD=
```

6. Ejecutar migraciones y seeders:
```bash
php artisan migrate --seed
```

7. Crear enlace simbólico para almacenamiento:
```bash
php artisan storage:link
```

8. Iniciar servidor:
```bash
php artisan serve
```

## Usuarios por Defecto

- **Admin:** admin@cevicheria.com / password
- **Cajero:** cajero@cevicheria.com / password
- **Mesero:** mesero@cevicheria.com / password

## Estructura del Proyecto

- `/app/Models` - Modelos de datos
- `/app/Http/Controllers` - Controladores
- `/database/migrations` - Migraciones de base de datos
- `/resources/views` - Vistas Blade
- `/public/images` - Imágenes de productos
