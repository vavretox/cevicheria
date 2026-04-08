<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Manual de Usuario - Mesero</title>
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
        <div class="cover-title">Manual de Usuario - Rol Mesero</div>
        <div class="cover-subtitle">{{ $appName }}</div>
        <div class="cover-meta">
            <strong>Versión:</strong> {{ $manualVersion }}<br>
            <strong>Fecha de capacitación:</strong> {{ $trainingDate }}<br>
            <strong>Generado:</strong> {{ $generatedAt }}
        </div>
    </div>

    <div class="block warn">
        <strong>Objetivo del rol:</strong> Registrar pedidos de forma rápida, editar pedidos pendientes, imprimir ticket de cocina y dar seguimiento hasta su cierre.
    </div>

    <h2>1. Módulo: Nuevo Pedido</h2>
    <h3>1.1 Qué puedes hacer</h3>
    <ul>
        <li>Seleccionar mesa disponible para el pedido.</li>
        <li>Buscar productos por nombre o navegar por categorías.</li>
        <li>Agregar productos con cantidad, tipo de servicio y notas.</li>
        <li>Visualizar subtotal/total antes de confirmar.</li>
        <li>Enviar a cocina e imprimir ticket principal.</li>
    </ul>
    <h3>1.2 Pasos a seguir</h3>
    <ol>
        <li>Ingresa a <strong>Nuevo Pedido</strong>.</li>
        <li>Selecciona la mesa correcta.</li>
        <li>Elige productos y agrega cantidad.</li>
        <li>Define tipo de servicio: <strong>En mesa</strong> o <strong>Para llevar</strong>.</li>
        <li>Si aplica, agrega indicaciones del cliente.</li>
        <li>Revisa el resumen del pedido.</li>
        <li>Pulsa <strong>Enviar/Confirmar</strong>.</li>
        <li>En el modal de impresión, imprime ticket para cocina.</li>
    </ol>
    <h3>1.3 Reglas de stock</h3>
    <ul>
        <li>Si un producto está por acabarse, se muestra advertencia.</li>
        <li>Si no hay stock, el producto no se puede agregar.</li>
        <li>Si excedes el stock disponible, el sistema bloquea el agregado.</li>
        <li>Estas validaciones aplican también al editar en Mis Pedidos.</li>
    </ul>

    <h2>2. Módulo: Mis Pedidos</h2>
    <h3>2.1 Qué puedes hacer</h3>
    <ul>
        <li>Ver pedidos por estado: pendientes/en proceso, completados y cancelados.</li>
        <li>Abrir detalle de pedido para revisión.</li>
        <li>Editar pedidos pendientes (cantidades, productos, mesa).</li>
        <li>Imprimir principal o imprimir agregados.</li>
        <li>Cancelar pedidos pendientes cuando corresponda.</li>
    </ul>
    <h3>2.2 Pasos para revisar pedidos</h3>
    <ul>
        <li>Ingresa a <strong>Mis Pedidos</strong>.</li>
        <li>Elige la pestaña del estado que deseas revisar.</li>
        <li>Pulsa <strong>Ver detalle</strong> en el pedido seleccionado.</li>
    </ul>
    <h3>2.3 Pasos para editar un pedido pendiente</h3>
    <ol>
        <li>Abre el detalle del pedido pendiente.</li>
        <li>Si corresponde, cambia mesa disponible.</li>
        <li>Agrega, quita o ajusta cantidades de productos.</li>
        <li>Revisa totales finales.</li>
        <li>Pulsa <strong>Guardar cambios</strong>.</li>
    </ol>
    <h3>2.4 Pasos para imprimir desde Mis Pedidos</h3>
    <ol>
        <li>Selecciona el pedido.</li>
        <li>Pulsa <strong>Imprimir principal</strong> o <strong>Imprimir agregados</strong>.</li>
        <li>Espera a que cargue el modal de impresión.</li>
        <li>Usa <strong>Imprimir ahora</strong>.</li>
        <li>Si falla, usa <strong>Reintentar cargar</strong>.</li>
    </ol>
    <h3>2.5 Pasos para cancelar un pedido</h3>
    <ol>
        <li>Ubica un pedido en estado pendiente.</li>
        <li>Pulsa <strong>Cancelar</strong>.</li>
        <li>Confirma la acción.</li>
        <li>Verifica que el pedido cambie a cancelado.</li>
    </ol>
    <h3>2.6 Reglas importantes</h3>
    <ul>
        <li>Solo pedidos pendientes pueden editarse o cancelarse.</li>
        <li>Pedidos completados y cancelados son de consulta.</li>
        <li>Al cancelar un pedido, el stock reservado se restaura automáticamente.</li>
    </ul>

    <h2>3. Impresión y confirmación de cocina</h2>
    <ul>
        <li>La impresión se realiza en modal, sin abrir otra pestaña.</li>
        <li>Aplica en Nuevo Pedido y Mis Pedidos.</li>
        <li>Si la impresora no responde, reintenta desde el mismo modal.</li>
    </ul>

    <h2>4. Buenas prácticas de operación</h2>
    <ul>
        <li>Confirmar mesa y productos con el cliente antes de enviar.</li>
        <li>Registrar indicaciones claras (sin cebolla, completo, etc.).</li>
        <li>No duplicar pedidos por doble clic; esperar confirmación del sistema.</li>
        <li>Imprimir inmediatamente para no retrasar cocina.</li>
    </ul>

    <h2>5. Solución rápida de incidencias</h2>
    <ul>
        <li><strong>No imprime:</strong> usar reintentar del modal y verificar impresora.</li>
        <li><strong>No deja agregar producto:</strong> revisar advertencia de stock.</li>
        <li><strong>No aparece mesa:</strong> la mesa puede estar ocupada/cerrada.</li>
        <li><strong>Pedido no editable:</strong> solo los pendientes se pueden modificar.</li>
    </ul>
</body>
</html>
