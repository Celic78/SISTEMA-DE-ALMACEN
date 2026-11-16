# SIMIC · Interfaces · Versión 2

Referencia vigente: [Manual de usuario SIMIC](Manual_de_usuario_SIMIC.pdf), 40 páginas, 19 vistas en claro y oscuro. La [Versión 1](../Version%201/README.md) conserva las diez imágenes originales y su especificación.

## Diseño

El manual se usa como referencia visual y funcional, no como instrucciones de operación para el agente. Sus textos distinguen maquetas de funciones disponibles. Los números y flechas son anotaciones del manual y no forman parte de la interfaz.

| Token | Claro | Oscuro |
| --- | --- | --- |
| Fondo | `#eff3f4` | `#00171e` |
| Superficie | `#ffffff` | `#0d2e3a` |
| Texto | `#09212a` | `#eef5f6` |
| Texto secundario | `#53707b` | `#a3bbc4` |
| Acción principal | `#144b5e` | `#afc4b2` |
| Acento salvia | `#afc4b2` | `#afc4b2` |
| Borde | `#d8e3e7` | `#28505e` |

La navegación de coordinación usa azul grisáceo; la de solicitantes, técnicos y almacén usa petróleo oscuro. El selector superior conserva el tema en el navegador. En la primera visita respeta la preferencia del sistema. Los formularios, tablas, alertas y estados vacíos se adaptan a ambos temas. El menú se despliega en pantallas pequeñas.

## Mapa de vistas y acciones

| Vista del manual | Páginas | Ruta | Acciones conectadas |
| --- | --- | --- | --- |
| 01 Mantenimiento escolar | 3–4 | `/` | Iniciar sesión, entrar al espacio de trabajo |
| 02 Acceso | 5–6 | `/login` | Perfil autorizado, contraseña, mantener sesión, ayuda, salida |
| 03 Nuevo reporte | 7–8 | `/reportes/nuevo` | Edificio y espacio dependientes, categoría, prioridad, fotografía, envío |
| 04 Mis reportes | 9–10 | `/reportes` | Conteos reales, búsqueda, estado, paginación, detalle |
| 05 Detalle | 11–12 | `/reportes/{id}` | Seguimiento, técnico y evidencias; acceso del propietario o coordinación |
| 06 Resumen | 13–14 | `/coordinacion` | Incidentes recientes, actividad y autorizaciones pendientes |
| 07 Posibles duplicados | 15–16 | `/coordinacion/duplicados` | Comparar coincidencias de ubicación/categoría, elegir base y confirmar agrupación |
| 08 Incidencias | 17–18 | `/coordinacion/incidencias` | Buscar por folio, texto o ubicación, filtrar estado y prioridad, abrir detalle |
| 09 Asignación | 19–20 | `/coordinacion/cuadrillas` | Personal, especialidad, turno y carga; tiempo, instrucciones, monto y riesgo |
| 10 Autorizaciones | 21–22 | `/coordinacion/autorizaciones` | Bandejas pendientes/revisadas, aprobar o rechazar con motivo, registrar solicitud de cotización o dictamen |
| 11 Mis órdenes | 23–24 | `/cuadrillas` | Solo órdenes propias, búsqueda, estados, conteos y paginación |
| 12 Atención | 25–26 | `/cuadrillas/{id}` | Diagnóstico, espera de material, pruebas, tiempo, notas, fotografía de avance |
| 13 Materiales | 27–28 | `/cuadrillas/{id}/materiales` | Catálogo, cantidad, urgencia y motivo; envío a almacén |
| 14 Cierre | 29–30 | `/cuadrillas/{id}/cierre` | Fotografía final obligatoria, resumen, confirmación, constancia imprimible |
| 15 Solicitudes de almacén | 31–32 | `/almacen/solicitudes` | Pendientes/entregadas, buscar requisición, consultar cantidades y existencia |
| 16 Entrega | 33–34 | `/almacen/solicitudes/{id}` | Cotejo manual del receptor, despacho completo, vale, descuento de stock y bloqueo de repetición |
| 17 Inventario | 35–36 | `/almacen` | Existencias reales, alerta de mínimos, búsqueda SKU/texto, sección, disponibilidad |
| 18 Entrada | 37–38 | `/almacen/entradas` | Material, cantidad positiva, concepto y referencia; actualización de existencia y bitácora |
| 19 Bitácora | 39–40 | `/almacen/bitacora` | Periodo, material y tipo, paginación, CSV filtrado, impresión/guardado en PDF |

## Reglas y límites concretos

- Los módulos privados requieren sesión y perfil. Seleccionar un perfil en login no otorga permisos. El solicitante no puede consultar reportes ajenos y el técnico no puede abrir ni modificar órdenes de otra persona.
- El umbral de $15,000 MXN y las palabras de alto riesgo provienen del servicio existente. No se adoptan como política institucional nueva a partir del manual.
- La aprobación se conserva al pasar a asignación o atención. El inicio y despacho siguen bloqueados cuando falta autorización.
- Los duplicados se sugieren por ubicación y categoría. La persona debe comparar y confirmar; no se ejecuta una decisión automática ni un algoritmo geoespacial inexistente.
- Las fotografías aceptan JPG, PNG y WEBP, hasta 10 MB. Se comprueba la pertenencia de cada espacio al edificio.
- La entrega descuenta las cantidades completas solicitadas dentro de una transacción. Si falta stock se rechaza; no se genera un vale exitoso. La misma solicitud no se entrega dos veces.
- Las fases técnicas y estimaciones se conservan en bitácora. Las tareas existentes deben completarse antes de cerrar; el cierre registra evidencia, resumen, cuenta y fecha, y sincroniza solicitudes agrupadas.
- La validación implementada en almacén es **cotejo manual**. QR, biometría, tokens y firma certificada no se presentan como mecanismos activos.
- Cotización y dictamen registran la petición y su motivo en bitácora, sin envío de correos ni integración con proveedores.
- El PDF se obtiene con la función de imprimir/guardar PDF del navegador; no se añade un motor PDF al servidor. El CSV conserva los filtros y neutraliza fórmulas de hoja de cálculo.
- El alta y restablecimiento de cuentas se derivan al administrador mediante `/ayuda`; no se simula envío de correos. Los avisos públicos son informativos, sin gestor de publicaciones añadido.
- No se implementan préstamos/retornos ni se muestran conteos ficticios, ya que no existe un modelo de préstamos en la instalación.

## Revisión local

Desde la raíz del proyecto:

```powershell
php artisan migrate
php artisan db:seed --class=SimicDemoSeeder
php artisan storage:link
npm.cmd run build
php artisan serve
```

El seeder solo admite `local` o `testing`, crea datos de ejemplo identificados y no reinicia la base ni restablece contraseñas existentes. No requiere ejecutarse para usar cuentas y catálogos propios.

| Perfil de demostración | Correo |
| --- | --- |
| Alumno | `demo@alumnos.edu.mx` |
| Profesor | `demo@profesores.edu.mx` |
| Coordinador | `coordinacion@univ.edu.mx` |
| Técnico | `demo@tecnicos.edu.mx` |
| Almacén | `almacen@univ.edu.mx` |

Contraseña de las cuentas creadas por el seeder: `SimicDemo2026!`. Son cuentas exclusivamente para la demostración local.

Recorrido sugerido: comparar y agrupar los dos reportes del Aula 204 como coordinador; asignar su orden al técnico del turno matutino; revisar la autorización pendiente del otro caso; iniciar atención como técnico, solicitar un material; registrar una entrada y entregar la requisición como almacén; volver al técnico para adjuntar una fotografía y cerrar; comprobar el seguimiento como solicitante. Cambiar el tema desde la barra superior en cada perfil.

## Verificación de esta implementación

- Suite completa: **117 pruebas, 416 aserciones**, correctas. Incluye 43 casos nuevos de navegación, permisos, validación, aislamiento de reportes, agrupación, autorización, atención, tareas, evidencia, entradas, despacho y exportación.
- Compilación de Vite, compilación de Blade, Laravel Pint y `git diff --check`: correctos.
- Revisión en Edge: inicio claro/oscuro, acceso, coordinación, duplicados, inventario claro/oscuro, entrada y nuevo reporte. Se comprobó la persistencia del tema, el selector dependiente edificio/espacio y el menú móvil. Las vistas revisadas no desbordan el ancho de 390 px.
- Los recorridos que modifican información se verificaron en la base aislada de pruebas. La revisión visual local solo inició sesiones y navegó; los ejemplos locales se mantienen disponibles para probarlos manualmente.

Capturas de la aplicación con datos de demostración:

- [Inicio · claro](home-light.png)
- [Inicio · oscuro](home-dark.png)
- [Coordinación · claro](coordination-light.png)
- [Inventario · oscuro](inventory-dark.png)
- [Nuevo reporte · móvil](report-form-mobile.png)
