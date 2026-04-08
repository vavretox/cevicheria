<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Manual de Usuario - Administrador</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #111827; line-height: 1.45; }
        h1, h2, h3 { margin: 0 0 8px 0; color: #0f172a; }
        h1 { font-size: 20px; }
        h2 { font-size: 15px; margin-top: 16px; border-bottom: 1px solid #cbd5e1; padding-bottom: 4px; }
        h3 { font-size: 13px; margin-top: 10px; }
        p { margin: 4px 0 8px 0; }
        ul { margin: 4px 0 10px 18px; padding: 0; }
        li { margin: 2px 0; }
        .meta { font-size: 11px; color: #475569; margin-bottom: 10px; }
        .block { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 8px; margin: 8px 0; }
        .warn { background: #fff7ed; border-color: #fdba74; }
        .cover { border: 1px solid #e2e8f0; border-radius: 8px; padding: 14px; margin-bottom: 14px; background: #f8fafc; }
        .cover-logo { text-align: center; margin-bottom: 10px; }
        .cover-logo img { width: 120px; height: auto; border-radius: 8px; }
        .cover-title { text-align: center; font-size: 18px; font-weight: bold; margin-bottom: 6px; }
        .cover-subtitle { text-align: center; font-size: 12px; color: #334155; margin-bottom: 10px; }
        .cover-meta { font-size: 11px; color: #334155; }
    </style>
</head>
<body>
    <div class="cover">
        @if(!empty($logoDataUri))
            <div class="cover-logo">
                <img src="{{ $logoDataUri }}" alt="Logo">
            </div>
        @endif
        <div class="cover-title">Manual de Usuario - Rol Administrador</div>
        <div class="cover-subtitle">{{ $appName }}</div>
        <div class="cover-meta">
            <strong>Versión:</strong> {{ $manualVersion }}<br>
            <strong>Fecha de capacitación:</strong> {{ $trainingDate }}<br>
            <strong>Generado:</strong> {{ $generatedAt }}
        </div>
    </div>

    <div class="block warn">
        <strong>Objetivo del rol:</strong> Configurar la operación, controlar catálogos, usuarios, mesas, inventario de bebidas, reportes y arqueos.
    </div>

    <h2>1. Módulo: Acceso y navegación</h2>
    <h3>1.1 Qué puedes hacer</h3>
    <ul>
        <li>Ingresar con privilegios totales de administración.</li>
        <li>Acceder a todos los módulos de operación.</li>
        <li>Supervisar estado general del sistema desde dashboard.</li>
    </ul>
    <h3>1.2 Pasos a seguir</h3>
    <ol>
        <li>Inicia sesión con usuario de rol <strong>admin</strong>.</li>
        <li>Verifica el menú principal: Dashboard, Productos, Almacén Bebidas, Categorías, Mesas, Usuarios, Caja y Arqueo, Reportes.</li>
        <li>Usa el botón de menú lateral para navegación rápida en móvil/tablet.</li>
    </ol>

    <h2>2. Módulo: Gestión de usuarios</h2>
    <h3>2.1 Qué puedes hacer</h3>
    <ul>
        <li>Crear usuarios admin, cajero y mesero.</li>
        <li>Definir canal de mesero (mesa o delivery).</li>
        <li>Editar, activar o desactivar cuentas.</li>
    </ul>
    <h3>2.2 Pasos para crear usuario</h3>
    <ul>
        <li>Ir a <strong>Usuarios</strong> → <strong>Nuevo</strong>.</li>
        <li>Completar: nombre, email, contraseña, rol.</li>
        <li>Si el rol es mesero, definir canal: <strong>Atención en mesa</strong> o <strong>Delivery</strong>.</li>
        <li>Guardar y validar acceso del usuario creado.</li>
    </ul>
    <h3>2.3 Pasos para editar o desactivar</h3>
    <ul>
        <li>Editar datos cuando cambien funciones o credenciales.</li>
        <li>Desactivar usuarios inactivos (recomendado en lugar de eliminar).</li>
    </ul>

    <h2>3. Módulo: Categorías y productos</h2>
    <h3>3.1 Qué puedes hacer</h3>
    <ul>
        <li>Crear y mantener categorías del menú.</li>
        <li>Registrar productos con precio, estado y stock.</li>
        <li>Actualizar precios y disponibilidad.</li>
    </ul>
    <h3>3.2 Pasos para categorías</h3>
    <ul>
        <li>Crear categorías con nombre claro (ejemplo: Ceviches, Bebidas, Entradas).</li>
        <li>Mantener activas solo las categorías operativas.</li>
    </ul>
    <h3>3.3 Pasos para productos</h3>
    <ul>
        <li>Registrar: nombre, categoría, precio, estado activo, stock.</li>
        <li>Actualizar precio cuando cambie la carta.</li>
        <li>Desactivar productos no disponibles temporalmente.</li>
    </ul>

    <h2>4. Módulo: Almacén de bebidas</h2>
    <h3>4.1 Qué puedes hacer</h3>
    <ul>
        <li>Registrar ingresos y salidas de stock de bebidas.</li>
        <li>Controlar costos de compra por movimiento.</li>
        <li>Consultar e imprimir control de almacén.</li>
    </ul>
    <h3>4.2 Pasos a seguir</h3>
    <ul>
        <li>Registrar ingresos por compra (unidad/caja, costo, total).</li>
        <li>Registrar salidas de inventario manuales cuando corresponda.</li>
        <li>Validar costos para reportes de utilidad en bebidas.</li>
        <li>Usar impresión del almacén para control físico.</li>
    </ul>

    <h2>5. Módulo: Mesas y reservas</h2>
    <h3>5.1 Qué puedes hacer</h3>
    <ul>
        <li>Crear, editar y desactivar mesas.</li>
        <li>Asignar zona y administrar reservas.</li>
        <li>Revisar actividad histórica de cada mesa.</li>
    </ul>
    <h3>5.2 Pasos a seguir</h3>
    <ul>
        <li>Crear mesas por zona.</li>
        <li>Marcar mesa activa/inactiva según operación real.</li>
        <li>Gestionar reservas con nombre y hora.</li>
        <li>Revisar actividad de mesa (pedidos y movimientos).</li>
    </ul>

    <h2>6. Módulo: Caja y arqueos</h2>
    <h3>6.1 Qué puedes hacer</h3>
    <ul>
        <li>Monitorear aperturas y cierres por cajero.</li>
        <li>Revisar diferencias de caja.</li>
        <li>Auditar observaciones de cierre.</li>
    </ul>
    <h3>6.2 Pasos a seguir</h3>
    <ul>
        <li>Desde <strong>Caja y Arqueo</strong>, monitorear aperturas/cierres por cajero.</li>
        <li>Verificar diferencias entre monto esperado y contado.</li>
        <li>Revisar observaciones del cierre para auditoría diaria.</li>
    </ul>

    <h2>7. Módulo: Reportes</h2>
    <h3>7.1 Qué puedes hacer</h3>
    <ul>
        <li>Consultar indicadores por fecha.</li>
        <li>Analizar ventas, pedidos y métodos de pago.</li>
        <li>Exportar información para control y auditoría.</li>
    </ul>
    <h3>7.2 Pasos a seguir</h3>
    <ul>
        <li>Filtrar por rango de fechas.</li>
        <li>Revisar ventas, pedidos, métodos de pago y desempeño operativo.</li>
        <li>Exportar en formatos disponibles (impresión, PDF, XLSX).</li>
    </ul>

    <h2>8. Cierre operativo recomendado (diario)</h2>
    <h3>8.1 Qué verificar</h3>
    <ul>
        <li>Pedidos pendientes y estado de producción.</li>
        <li>Cierres de caja completos.</li>
        <li>Stock crítico para siguiente turno.</li>
        <li>Reporte final del día generado.</li>
    </ul>
    <h3>8.2 Pasos a seguir</h3>
    <ol>
        <li>Confirmar que no queden pedidos pendientes sin atender.</li>
        <li>Validar cierre de caja de todos los cajeros.</li>
        <li>Revisar stock crítico de productos clave.</li>
        <li>Exportar reporte del día.</li>
        <li>Desactivar usuarios temporales si aplica.</li>
    </ol>

    <h2>9. Buenas prácticas</h2>
    <ul>
        <li>No compartir credenciales de administrador.</li>
        <li>No editar pedidos completados sin motivo documentado.</li>
        <li>Registrar cambios de precios antes de apertura de turno.</li>
        <li>Mantener nomenclatura consistente en productos y categorías.</li>
    </ul>
</body>
</html>
