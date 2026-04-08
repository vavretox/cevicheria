<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Manual de Usuario - Cajero</title>
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
        <div class="cover-title">Manual de Usuario - Rol Cajero</div>
        <div class="cover-subtitle">{{ $appName }}</div>
        <div class="cover-meta">
            <strong>Versión:</strong> {{ $manualVersion }}<br>
            <strong>Fecha de capacitación:</strong> {{ $trainingDate }}<br>
            <strong>Generado:</strong> {{ $generatedAt }}
        </div>
    </div>

    <div class="block warn">
        <strong>Objetivo del rol:</strong> Crear pedidos rápidos, gestionar pedidos pendientes, cobrar ventas, imprimir tickets y controlar caja.
    </div>

    <h2>1. Módulo: Caja y Arqueo (Inicio y cierre)</h2>
    <h3>1.1 Qué puedes hacer</h3>
    <ul>
        <li>Abrir caja al inicio del turno.</li>
        <li>Consultar historial de aperturas y cierres.</li>
        <li>Cerrar caja con monto contado y observaciones.</li>
    </ul>
    <h3>1.2 Pasos a seguir (inicio de jornada)</h3>
    <ol>
        <li>Inicia sesión con usuario de rol <strong>cajero</strong>.</li>
        <li>Ingresa a <strong>Caja y Arqueo</strong>.</li>
        <li>Registra el monto de apertura.</li>
        <li>Confirma apertura para habilitar cobros.</li>
    </ol>
    <h3>1.3 Pasos a seguir (cierre de jornada)</h3>
    <ol>
        <li>Verifica que no queden cobros urgentes pendientes.</li>
        <li>Ingresa a <strong>Caja y Arqueo</strong>.</li>
        <li>Registra monto contado final.</li>
        <li>Escribe observaciones si hay diferencia.</li>
        <li>Confirma cierre de caja.</li>
    </ol>

    <h2>2. Módulo: Pedido Rápido</h2>
    <h3>2.1 Qué puedes hacer</h3>
    <ul>
        <li>Crear pedidos en segundos desde caja.</li>
        <li>Seleccionar mesero y mesa (o delivery sin mesa).</li>
        <li>Agregar productos con cantidad, precio y nota.</li>
        <li>Imprimir cocina al momento de crear pedido.</li>
    </ul>
    <h3>2.2 Pasos a seguir</h3>
    <ol>
        <li>Seleccionar mesero.</li>
        <li>Si el mesero es de mesa: seleccionar mesa desde el tablero.</li>
        <li>Elegir producto y cantidad, pulsar <strong>Agregar</strong>.</li>
        <li>Editar cantidad/precio/nota directamente en el resumen si es necesario.</li>
        <li>Pulsar <strong>Crear Pedido</strong>.</li>
    </ol>
    <h3>2.3 Caso delivery</h3>
    <ul>
        <li>Al elegir mesero delivery, las mesas se desactivan automáticamente.</li>
        <li>No se requiere mesa para crear el pedido.</li>
    </ul>
    <h3>2.4 Reglas de stock en Pedido Rápido</h3>
    <ul>
        <li>Productos sin stock aparecen agotados y no se pueden seleccionar.</li>
        <li>Si el stock es bajo, se muestra advertencia.</li>
        <li>No se permite exceder stock al agregar o ajustar cantidades.</li>
    </ul>

    <h2>3. Módulo: Pedidos pendientes</h2>
    <h3>3.1 Qué puedes hacer</h3>
    <ul>
        <li>Ver pedidos pendientes del turno.</li>
        <li>Abrir detalle completo del pedido.</li>
        <li>Actualizar productos, cantidades, mesa y mesero.</li>
        <li>Cancelar pedido pendiente si corresponde.</li>
    </ul>
    <h3>3.2 Pasos a seguir</h3>
    <ol>
        <li>Desde dashboard, ubica el pedido pendiente.</li>
        <li>Abre el detalle del pedido.</li>
        <li>Realiza ajustes necesarios.</li>
        <li>Guarda cambios y verifica actualización.</li>
    </ol>

    <h2>4. Módulo: Cobro de pedidos</h2>
    <h3>4.1 Qué puedes hacer</h3>
    <ul>
        <li>Procesar cobros por efectivo, QR o mixto.</li>
        <li>Registrar montos de pago y validar total.</li>
        <li>Completar pedido y emitir boleta.</li>
    </ul>
    <h3>4.2 Pasos a seguir</h3>
    <ol>
        <li>Abre pedido pendiente.</li>
        <li>Selecciona método de pago.</li>
        <li>Si es mixto, registra efectivo y QR.</li>
        <li>Verifica que la suma sea igual al total.</li>
        <li>Confirma cobro y procesa pedido.</li>
        <li>Imprime boleta.</li>
    </ol>
    <h3>4.3 Reglas de cobro</h3>
    <ul>
        <li>Métodos permitidos: efectivo, QR, mixto.</li>
        <li>En mixto, la suma de efectivo + QR debe ser igual al total.</li>
        <li>Sin caja abierta no se puede cobrar.</li>
    </ul>

    <h2>5. Módulo: Impresiones</h2>
    <h3>5.1 Qué puedes hacer</h3>
    <ul>
        <li>Imprimir cocina al crear pedido rápido.</li>
        <li>Imprimir comprobantes desde detalle.</li>
        <li>Emitir boleta al cerrar cobro.</li>
    </ul>
    <h3>5.2 Pasos a seguir</h3>
    <ol>
        <li>En pedido rápido puedes activar/desactivar impresión cocina al crear.</li>
        <li>Desde detalle puedes imprimir comprobantes cuando corresponda.</li>
        <li>Verifica impresora conectada antes de horas pico.</li>
    </ol>

    <h2>6. Módulo: Ventas y Stock</h2>
    <h3>6.1 Qué puedes hacer</h3>
    <ul>
        <li>Revisar historial de ventas completadas.</li>
        <li>Filtrar y consultar reportes operativos de caja.</li>
        <li>Monitorear stock de productos en pantalla dedicada.</li>
    </ul>
    <h3>6.2 Pasos a seguir</h3>
    <ol>
        <li>En <strong>Ventas</strong> revisar historial de pedidos completados.</li>
        <li>En <strong>Stock Productos</strong> monitorear existencias y alertas de bajo stock.</li>
        <li>Coordinar con administrador cuando haya quiebres de inventario.</li>
    </ol>

    <h2>7. Solución rápida de incidencias</h2>
    <ul>
        <li><strong>No permite cobrar:</strong> confirmar que la caja esté abierta.</li>
        <li><strong>Stock insuficiente:</strong> ajustar cantidades o reemplazar producto.</li>
        <li><strong>Mesa no disponible:</strong> seleccionar otra mesa habilitada.</li>
        <li><strong>Pago mixto inválido:</strong> corregir montos hasta cuadrar total.</li>
    </ul>
</body>
</html>
