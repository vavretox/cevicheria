# 🍤 SISTEMA POS CEVICHERÍA - GUÍA DE INSTALACIÓN

## 📋 Requisitos Previos

- PHP >= 8.2
- Composer
- MySQL >= 5.7 o MariaDB >= 10.3
- Node.js >= 18 (opcional, para assets)
- Servidor web (Apache/Nginx)

## 🚀 INSTALACIÓN PASO A PASO

### 1. Descomprimir el proyecto
```bash
# Descomprimir el archivo ZIP en tu servidor
unzip cevicheria-pos.zip
cd cevicheria-pos
```

### 2. Configurar permisos (Linux/Mac)
```bash
# Dar permisos de escritura a directorios necesarios
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

### 3. Configurar archivo .env
```bash
# Copiar el archivo de ejemplo
cp .env.example .env

# Editar el archivo .env y configurar tu base de datos
nano .env
```

**Configuración importante en .env:**
```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=cevicheria_pos    # Nombre de tu base de datos
DB_USERNAME=root               # Tu usuario de MySQL
DB_PASSWORD=                   # Tu contraseña de MySQL
```

### 4. Crear la base de datos
```bash
# Conectarse a MySQL
mysql -u root -p

# Crear la base de datos
CREATE DATABASE cevicheria_pos CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
EXIT;
```

### 5. Instalar dependencias de PHP
```bash
composer install --no-dev --optimize-autoloader
```

### 6. Generar clave de aplicación
```bash
php artisan key:generate
```

### 7. Ejecutar migraciones y poblar datos
```bash
# Esto creará las tablas y agregará datos de prueba
php artisan migrate --seed
```

### 8. Crear enlace simbólico para imágenes
```bash
php artisan storage:link
```

### 9. (Opcional) Compilar assets
```bash
npm install
npm run build
```

## ✅ VERIFICAR INSTALACIÓN

### Probar el servidor de desarrollo:
```bash
php artisan serve
```

Abrir en el navegador: `http://localhost:8000`

### Probar con servidor Apache/Nginx:
Configurar el DocumentRoot apuntando a la carpeta `public/`

## 👥 CREDENCIALES DE ACCESO

Una vez instalado, puedes acceder con estos usuarios:

**Administrador:**
- Email: `admin@cevicheria.com`
- Contraseña: `password`

**Cajero:**
- Email: `cajero@cevicheria.com`
- Contraseña: `password`

**Mesero:**
- Email: `mesero@cevicheria.com`
- Contraseña: `password`

## 🔧 CONFIGURACIÓN DE APACHE (VirtualHost)

Si usas Apache, crea un VirtualHost:

```apache
<VirtualHost *:80>
    ServerName cevicheria-pos.local
    DocumentRoot /ruta/a/cevicheria-pos/public

    <Directory /ruta/a/cevicheria-pos/public>
        Options Indexes FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/cevicheria_error.log
    CustomLog ${APACHE_LOG_DIR}/cevicheria_access.log combined
</VirtualHost>
```

Luego:
```bash
sudo a2ensite cevicheria-pos.conf
sudo systemctl reload apache2
```

## 🔧 CONFIGURACIÓN DE NGINX

Ejemplo de configuración Nginx:

```nginx
server {
    listen 80;
    server_name cevicheria-pos.local;
    root /ruta/a/cevicheria-pos/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

## 📸 AGREGAR IMÁGENES DE PRODUCTOS

Las imágenes de productos se guardan en:
```
storage/app/public/products/
```

Después de ejecutar `php artisan storage:link`, estarán disponibles en:
```
public/storage/products/
```

## 🛠️ SOLUCIÓN DE PROBLEMAS

### Error: "Class not found"
```bash
composer dump-autoload
php artisan config:clear
php artisan cache:clear
```

### Error de permisos
```bash
sudo chmod -R 775 storage bootstrap/cache
sudo chown -R www-data:www-data storage bootstrap/cache
```

### Error de conexión a base de datos
- Verificar credenciales en `.env`
- Verificar que MySQL esté corriendo
- Verificar que la base de datos exista

### Imágenes no se muestran
```bash
php artisan storage:link
```

## 📁 ESTRUCTURA DE ARCHIVOS IMPORTANTES

```
cevicheria-pos/
├── app/
│   ├── Http/Controllers/     # Controladores
│   ├── Models/               # Modelos Eloquent
│   └── Http/Middleware/      # Middleware de roles
├── database/
│   ├── migrations/           # Migraciones de BD
│   └── seeders/              # Datos de prueba
├── resources/
│   └── views/                # Vistas Blade
│       ├── admin/            # Vistas del admin
│       ├── cashier/          # Vistas del cajero
│       ├── waiter/           # Vistas del mesero
│       └── auth/             # Login
├── routes/
│   └── web.php               # Rutas del sistema
├── public/                   # Carpeta pública (DocumentRoot)
└── storage/                  # Archivos y logs
```

## 🎨 PERSONALIZACIÓN

### Cambiar nombre del restaurante:
Editar en `.env`:
```
APP_NAME="Tu Restaurante"
```

### Cambiar IGV (impuesto):
Editar en `app/Models/Order.php`, línea del método `calculateTotal()`:
```php
$this->tax = $this->subtotal * 0.18; // Cambiar 0.18 por tu porcentaje
```

### Agregar más productos:
- Acceder como Admin
- Ir a "Productos" > "Nuevo Producto"
- Completar el formulario y subir imagen

## 📞 SOPORTE

Si tienes problemas, verifica:
1. Versión de PHP >= 8.2
2. Extensiones de PHP requeridas (ver composer.json)
3. Permisos de carpetas storage/ y bootstrap/cache/
4. Configuración correcta de .env
5. Base de datos creada y accesible

## 🎉 ¡LISTO!

Tu sistema POS está instalado y funcionando. 

Para ver una demostración visual de todas las pantallas, abre:
`GUIA_VISUAL_PANTALLAS.html` en tu navegador.
