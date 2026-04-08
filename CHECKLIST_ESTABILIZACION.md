# Checklist de Estabilizacion

Este proyecto entra en etapa de estabilizacion.
Objetivo: corregir bugs reales de operacion sin agregar modulos nuevos.

## Reglas de esta etapa

- No abrir nuevos modulos.
- No cambiar logica de negocio salvo que corrija un bug real.
- Priorizar errores que bloqueen venta, cocina, caja o reportes.
- Toda correccion debe probarse en el flujo real afectado.

## Flujo diario sugerido

1. Abrir caja.
2. Crear pedido en mesero.
3. Imprimir cocina.
4. Editar pedido y probar ultimos agregados.
5. Cambiar mesa de un pedido pendiente.
6. Cobrar en caja.
7. Imprimir recibo.
8. Revisar reportes.
9. Registrar entrada o salida de bebidas.
10. Cerrar caja y revisar arqueo.

## QA por rol

### Administrador

- Usuarios:
  - crear mesero normal
  - crear mesero delivery
  - editar rol y canal de atencion
- Categorias:
  - crear, editar y eliminar
  - validar nombres con comillas
- Productos:
  - crear bebida y alimento
  - validar precio, stock e imagen
- Mesas:
  - crear, editar, reservar, cerrar
  - revisar actividad de pedidos y cambios de mesa
- Almacen bebidas:
  - entrada por unidad
  - entrada por caja
  - salida manual
  - impresion de historial
- Reportes:
  - filtros
  - impresion normal
  - impresion termica
- Caja y arqueo:
  - ver sesiones abiertas
  - ver sesiones cerradas
  - imprimir arqueo

### Mesero

- Nuevo pedido:
  - seleccionar mesa
  - agregar bebida
  - agregar ceviche con indicaciones
  - agregar plato fuerte
  - usar en mesa y para llevar
- Mis pedidos:
  - editar cantidades
  - agregar productos
  - cambiar mesa
  - cancelar pedido pendiente
- Impresion cocina:
  - pedido completo
  - ultimos agregados
  - pedido mixto en mesa y para llevar
  - confirmar que bebidas no salgan en cocina

### Cajero

- Dashboard:
  - crear pedido rapido
  - crear pedido delivery sin mesa
  - imprimir cocina al crear
- Detalle del pedido:
  - editar productos
  - cambiar mesa
  - revisar historial
- Cobro:
  - efectivo
  - QR
  - efectivo + QR
- Caja:
  - apertura
  - cierre
  - diferencia
  - impresion
- Ventas:
  - ver detalle
  - imprimir

## Registro de incidencias

Usar este formato:

- Fecha:
- Rol:
- Modulo:
- Pasos para reproducir:
- Resultado esperado:
- Resultado actual:
- Severidad:
- Evidencia:

## Cierre de estabilizacion

La etapa puede considerarse estable cuando:

- no haya errores bloqueantes en venta o cobro
- cocina imprima correctamente en pedidos normales y editados
- caja abra y cierre sin inconsistencias
- reportes impriman con datos consistentes
- almacen de bebidas mantenga stock coherente
