# Sistema de Diseño y Guía de Estilos UI — SIMIC
> **SIMIC**: Sistema Integral de Mantenimiento e Infraestructura Campus  
> **Institución**: Universidad Metropolitana — Dirección de Obras y Servicios / Almacén Central  
> **Documento**: Especificación Técnica y Visual de Interfaces (`/docs/Interfaces`)

---

## 1. Visión General del Sistema y Enfoque Visual

El diseño visual de **SIMIC** adopta una estética **Enterprise / Gov-Tech Institucional Moderna**. Está optimizado para la alta densidad de datos, precisión operativa y trazabilidad en tiempo real de recursos, cuadrillas y materiales de infraestructura escolar.

### Principios Clave de Diseño
1. **Claridad y Contraste Institucional**: Uso de fondos claros y neutros (`#F8FAFC`, `#FFFFFF`) combinados con un azul marino corporativo profundo (`#0B2239` / `#0F2942`) para elementos principales, botones de acción y encabezados de alta jerarquía.
2. **Jerarquía Paramétrica y Código de Colores Semántico**: Categorización inmediata de criticidad, estados de inventario, turnos laborales y alertas presupuestarias mediante badges y alertas con fondos atenuados (*tinted backgrounds*) y texto saturado de alto contraste.
3. **Control Espacial y Agrupación Modular**: Distribución en tarjetas (*cards*) con bordes sutiles de `1px` (`#E2E8F0`), radios suaves (`6px` a `12px`), sombreados muy tenues (*soft ambient shadows*) y división limpia entre paneles de control, tablas de datos y formularios.
4. **Trazabilidad Foliada**: Estandarización visual de folios técnicos, órdenes de trabajo (OT), vales y SKUs con tipografía semi-monoespaciada o peso semibold.

---

## 2. Mapa y Auditoría de Interfaces Analizadas

A partir de los archivos gráficos en la carpeta `docs/Interfaces/`, se identifican 6 módulos funcionales principales:

| Archivo | Módulo / Vista | Rol / Contexto | Elementos Visuales Destacados |
| :--- | :--- | :--- | :--- |
| `Captura de pantalla 2026-10-26 113546.png` | **Acceso y Registro Institucional** | Público / Todos los roles | Tarjeta centrada con sombra, selector de rol con directiva `SEC-POL-04`, banners de error en rojo suave, directorio de dominios (`@alumnos...`, `@profesores...`) en tabla resumen. |
| `Captura de pantalla 2026-10-26 113625.png` | **Centro de Mando & Coordinación Operativa** | Superusuario / Coordinador | Banner de auditoría activa, 4 KPI cards con micro-métricas y barras de progreso, tarjeta de algoritmo *Anti-Duplicados LBS v2.4* con tabla comparativa de folios y botón de consolidación maestro. |
| `Captura de pantalla 2026-10-26 113708.png` | **Portal Institucional de Reportes (Solicitante)** | Alumno / Docente / Administrativo | Sidebar colapsado a iconos, contadores de histórico/proceso/resueltos, formulario de captura técnica con zona *drag & drop* de evidencias y chips de archivos adjuntos, acordeón de catálogo de edificios, tabla histórica paginada. |
| `Captura de pantalla 2026-10-26 113753.png` | **Consola de Cuadrilla Técnica** | Técnico Especialista | Stepper horizontal de 4 fases (Diagnóstico, Material, Pruebas, Concluido), slider de SLA estimado en tiempo real, widget comparativo fotográfico "Antes / Después" con badges funcionales, acta de entrega técnica foliada con firma. |
| `Captura de pantalla 2026-10-26 113824.png` | **Despacho y Coordinación de Cuadrillas** | Coordinación General | Switcher de turnos laborales (Matutino / Vespertino), matriz de reportes entrantes con criticidad multinivel, panel de estado y asignación de cuadrillas con indicadores en vivo (disponible/ocupado). |
| `Captura de pantalla 2026-10-26 113839.png` | **Módulo de Autorizaciones Especiales** | Coordinador / Auditor | Tarjetas de aprobación para montos excedidos (> $15,000 MXN) y protocolos de trabajo en alturas (NOM-009-STPS-2011), botones diferenciados de aprobación, rechazo y dictamen pericial. |
| `Captura de pantalla 2026-10-26 113938.png` | **Módulo de Abastecimiento y Despacho** | Almacén y Suministros | KPIs de stock crítico y préstamos activos, bandeja horizontal de requisiciones validadas listas para ventanilla con desglose de ítems e indicaciones de racks/pasillos. |
| `Captura de pantalla 2026-10-26 114005.png` | **Catálogo Oficial de Materiales (Parte 1)** | Encargado de Almacén | Filtros por categorías (Eléctrico, Hidráulico, Acabados), tabla de inventario con SKUs formales, conteo de stock disponible vs. en préstamo, umbral de alerta mínima y badges de stock. |
| `Captura de pantalla 2026-10-26 114031.png` | **Catálogo de Materiales & Bitácora (Parte 2)** | Encargado de Almacén | Continuación de SKUs de plomería y acabados, botones de exportación a CSV y PDF oficial foliado. |
| `Captura de pantalla 2026-10-26 114049.png` | **Bitácora Oficial de Salidas y Entregas** | Control de Ventanilla | Tabla de vales foliados (`VAL-2024-XXXX`), registro de técnica/o, método de validación (QR digital, huella biométrica, cotejo), estatus de entrega y retorno de herramienta. |

---

## 3. Tokens de Diseño (Design Tokens)

### 3.1. Paleta Cromática

#### Colores Primarios y Neutros Base
```css
:root {
  /* Marca e Institución */
  --color-primary-navy:       #0B2239; /* Azul marino institucional profundo (Navbar, botones primarios, headers) */
  --color-primary-dark:       #071626; /* Hover de acciones primarias y fondos oscuros de acento */
  --color-primary-teal:       #0D9488; /* Acento verde-azulado institucional (Logotipos, badges de soporte) */
  --color-primary-cyan:       #0284C7; /* Enlaces interactivos y selecciones activas */

  /* Lienzo y Superficies */
  --color-bg-canvas:          #F8FAFC; /* Fondo general de la aplicación (Slate-50) */
  --color-bg-surface:         #FFFFFF; /* Fondo de tarjetas, tablas modulares y modales */
  --color-bg-subtle:          #F1F5F9; /* Fondo de campos de formulario deshabilitados, filas alternas o cards anidadas */
  --color-bg-sidebar:         #FFFFFF; /* Sidebar en blanco limpio con separador sutil */

  /* Bordes y Divisores */
  --color-border-subtle:      #E2E8F0; /* Borde estándar de tarjetas, divisores de tablas (Slate-200) */
  --color-border-focus:       #0284C7; /* Borde activo en inputs y tabs seleccionados */
  --color-border-dashed:      #94A3B8; /* Bordes discontinuos de estado o dropzones */

  /* Tipografía */
  --color-text-main:          #0F172A; /* Títulos, datos principales y números de métricas (Slate-900) */
  --color-text-body:          #334155; /* Texto regular de tablas y párrafos explicativos (Slate-700) */
  --color-text-muted:         #64748B; /* Etiquetas secundarias, folios discretos, micro-textos (Slate-500) */
  --color-text-light:         #94A3B8; /* Placeholders y marcas de agua (Slate-400) */
}
```

#### Estados Semánticos y Badges
Los estados combinan fondos pastel con texto y bordes de alta intensidad:

| Estado / Criticidad | Background | Text & Border | Aplicación en Interfaz |
| :--- | :--- | :--- | :--- |
| **Crítico / Peligro / Alerta Mínima** | `#FEE2E2` (Red-100) | `#B91C1C` (Red-700) | Incidencias críticas, stock por debajo del mínimo, presupuestos excedidos, botón de rechazo. |
| **Alta / Precaución / En Revisión** | `#FEF3C7` (Amber-100) | `#B45309` (Amber-700) | Criticidad alta, reporte en revisión, trabajo en alturas, avisos de seguridad. |
| **Media / Proceso / Asignado** | `#E0F2FE` (Sky-100) | `#0369A1` (Sky-700) | Incidencias en atención, asignación técnica, duplicados vinculables, roles institucionales. |
| **Óptimo / Solucionado / Activo** | `#DCFCE7` (Green-100) | `#15803D` (Green-700) | Stock en nivel óptimo, órdenes resueltas, personal activo, disponibilidad de cuadrilla. |
| **Baja / Informativo / Neutro** | `#F1F5F9` (Slate-100) | `#475569` (Slate-600) | Criticidad baja, programaciones regulares, roles secundarios. |

---

### 3.2. Tipografía y Escala Visual

La tipografía utilizada es moderna, sans-serif, limpia y con excelente legibilidad en pantalla (estilo **Inter**, **Instrument Sans** o **Plus Jakarta Sans**).

| Nivel | Tamaño | Peso | Interlineado | Tracking | Caso de Uso |
| :--- | :--- | :--- | :--- | :--- | :--- |
| **Display KPI** | `28px - 32px` | `700 (Bold)` | `1.1` | `-0.02em` | Cifras principales de métricas (`142`, `19`, `8 / 11`, `$38,450.00`). |
| **Page Title** | `20px - 22px` | `700 (Bold)` | `1.2` | `-0.01em` | Título de página (ej. "Centro de Mando & Coordinación Operativa"). |
| **Card Header** | `15px - 16px` | `600 (Semibold)`| `1.3` | `0` | Cabeceras de paneles (ej. "Matriz de Reportes Entrantes"). |
| **Overline / Pill** | `10px - 11px` | `700 (Bold)` | `1.0` | `+0.05em` | Texto superior en mayúsculas (ej. `CONTROL CENTRAL OPERATIVO`, `SEC-POL-04`). |
| **Body Regular** | `13px - 14px` | `400 (Regular)` | `1.4` | `0` | Descripciones de fallas, inputs, textos generales. |
| **Table Data** | `12px - 13px` | `500 (Medium)`  | `1.3` | `0` | Celdas de tablas, nombres técnicos, fechas y ubicaciones. |
| **Monospace / Code**| `12px` | `600 (Semibold)`| `1.2` | `+0.02em` | Folios (`#INC-8834`, `OT-2025-0842-ELE`, SKUs `ELC-PLZ-044`). |

---

### 3.3. Elevaciones, Bordes y Radios

- **Radios de Borde (`border-radius`)**:
  - `4px` (`rounded-sm`): Badges compactos y botones auxiliares de tabla.
  - `6px` a `8px` (`rounded-md`): Campos de entrada (`input`, `select`), botones estándar, chips de archivos.
  - `10px` a `12px` (`rounded-lg` / `rounded-xl`): Tarjetas de métricas, contenedores de sección, modales.
  - `9999px` (`rounded-full`): Pills de roles, contadores de notificación, avatares e indicadores de pulso de sincronización.
- **Bordes**:
  - Predominio de bordes finos `1px solid #E2E8F0` en todas las tarjetas y contenedores.
  - Cajas de arrastre (*dropzone*) con borde discontinuo `1.5px dashed #CBD5E1`.
- **Sombras (`box-shadow`)**:
  - Tarjetas y paneles: `0 1px 3px 0 rgba(0, 0, 0, 0.05), 0 1px 2px -1px rgba(0, 0, 0, 0.05)`.
  - Dropdowns y menús flotantes: `0 10px 15px -3px rgba(0, 0, 0, 0.08), 0 4px 6px -4px rgba(0, 0, 0, 0.04)`.

---

## 4. Biblioteca de Componentes UI y Patrones de Interacción

### 4.1. Barra Superior Institucional (Header)
- **Estructura en 3 Bloques**:
  1. *Izquierda*: Logotipo SIMIC en recuadro teal/navy, nombre formal del sistema y dependencia institucional en subtítulo gris.
  2. *Centro*: Breadcrumbs de navegación jerárquica con separadores `/` (ej. `Campus Central / Dirección de Obras y Servicios / Centro de Mando`).
  3. *Derecha*:
     - Pill de rol activo con icono de candado o credencial (ej. `ROL: Superusuario / Coordinador`).
     - Campana de notificaciones con insignia numérica circular roja/azul (`3`).
     - Avatar con iniciales o foto del personal (`RM`), nombre completo (`Arq. Roberto Mendoza`) y número de empleado institucional (`EMP-884920`).

### 4.2. Menú Lateral de Navegación (Sidebar)
- Soporta dos estados: **Expandido** (icono + texto de sección) y **Compacto** (solo iconos con tooltips).
- Secciones divididas con títulos overline (`OPERACIÓN DE CAMPUS`).
- Estado activo con fondo suave de contraste o contorno resaltado.
- Zona inferior reservada para Configuración (`settings`) y Cerrar Sesión (`logout` en acento rojo).

### 4.3. Tarjetas de Métricas (KPI Cards)
Formato de 4 columnas iguales en el encabezado de los paneles principales:
- **Encabezado interno**: Etiqueta en mayúsculas pequeñas (`INCIDENCIAS ACTIVAS`, `DUPLICADOS AGRUPADOS`) acompañada del icono en esquina superior derecha.
- **Valor numérico grande**: Tipografía display destacada (ej. `142`, `19`, `4`, `8 / 11`).
- **Pie de tarjeta contextual**:
  - *Opción A*: Micro barra de progreso visual con porcentaje.
  - *Opción B*: Delta o turno operativo (ej. `+12 turno matutino`).
  - *Opción C*: Indicador de ahorro o estatus de seguridad (ej. `Ahorro estimado: 36 hrs/hombre`).

### 4.4. Sistema de Tablas de Datos
- **Encabezados**:
  - Dos variantes presentes:
    1. *Variante Navy*: Fondo azul oscuro `#0B2239` con texto blanco mayúscula de 11px (usada en tablas operativas prioritarias como anti-duplicados y asignación de cuadrillas).
    2. *Variante Light Slate*: Fondo `#F8FAFC` con texto gris oscuro `#475569` en mayúsculas (usada en catálogos de almacén y bitácoras).
- **Celdas**:
  - Padding vertical compacto (`10px` a `14px`) para maximizar información por pantalla.
  - Resaltado de folios con tipografía bold o monoespaciada.
  - Alineación numérica a la derecha en conteos de inventario y montos financieros.
- **Filtros Integrados**:
  - Barra superior con buscador rápido y botón de opciones.
  - Tabs horizontales con contadores (ej. `Todos los Rubros (16)`, `Eléctrico (6)`, `Hidráulico (7)`).

### 4.5. Flujo de Trabajo y Stepper de Atención Técnica
Componente visual de progreso horizontal en 4 pasos:
1. **Paso 1: Diagnóstico** (Identificación de derivación) — Estado: Completado con checkmark.
2. **Paso 2: Esperando Material** (Vale a Almacén Central) — Estado: En espera / Radio button.
3. **Paso 3: En Pruebas** (Carga y termografía) — Estado: En espera.
4. **Paso 4: Concluido** (Acta técnica emitida) — Estado: En espera.
- Acompañado de aviso en vivo de sincronización con Jefatura e indicador SLA interactivo.

### 4.6. Componente de Evidencias Técnicas "Antes / Después"
Utilizado para el cierre oficial de órdenes de mantenimiento:
- Dos tarjetas simétricas de previsualización fotográfica:
  - Tarjeta 1: Etiqueta roja `FALLA` con la imagen del daño reportado inicial.
  - Tarjeta 2: Etiqueta verde `SOLUCIONADO` con la fotografía de la reparación concluida.
- Botones inferiores para "Reemplazar" o "Cargar Otra".
- Checkbox formal de certificación normativa (ej. *Certifico cumplimiento de la normativa NOM-001-SEDE*).
- Botón principal de firma digital con icono de bolígrafo/sello.

### 4.7. Tarjetas de Autorización Presupuestal y Seguridad
Tarjetas modulares para casos excepcionales con doble llamada a la acción:
- Resaltado del monto solicitado en tipografía roja de gran formato (`$38,450.00 MXN`) frente al límite automatizado (`$15,000.00 MXN`).
- Badges destacados (`Presupuesto Excedido`, `Trabajo en Alturas (>12m)`).
- Grupo de acciones:
  - Botón Secundario (Borde gris / texto azul): `Solicitar Cotización` / `Exigir Dictamen PC`.
  - Botón Peligro (Rojo sólido): `Rechazar Solicitud`.
  - Botón Primario (Navy sólido): `Aprobar Presupuesto` / `Aprobar Protocolo`.

---

## 5. Convenciones de Nomenclatura y Folios Institucionales

El sistema utiliza códigos estandarizados para cada entidad:

| Prefijo / Formato | Entidad | Ejemplo |
| :--- | :--- | :--- |
| `#INC-[0-9]{4}` | Incidencias y reportes iniciales de campus | `#INC-8834`, `#INC-8902` |
| `FOL-[0-9]{4}-[0-9]{4}` | Folio oficial de solicitud del portal | `FOL-2025-0841`, `FOL-2025-0839` |
| `OT-[0-9]{4}-[0-9]{4}-[A-Z]{3}` | Orden de Trabajo Técnica con especialidad | `OT-2025-0842-ELE`, `OT-2024-8846` |
| `FM-[0-9]{4}-[0-9]{4}` | Folio Maestro de incidencias agrupadas | `FM-2024-0988` |
| `VAL-[0-9]{4}-[0-9]{4}` | Vale de despacho de almacén y entrega | `VAL-2024-1102`, `VAL-2024-1105` |
| `EXP-AUT-[0-9]{4}-[0-9]{3}` | Expediente de autorización financiera | `EXP-AUT-2024-041` |
| `SEG-ALT-[0-9]{4}-[0-9]{3}` | Permiso de seguridad para trabajos de riesgo | `SEG-ALT-2024-019` |
| `ELC-[A-Z]{3}-[0-9]{3}` | SKU Almacén: Rubro Eléctrico | `ELC-PLZ-044`, `ELC-BRK-020` |
| `HID-[A-Z]{3}-[0-9]{3}` | SKU Almacén: Rubro Hidráulico / Plomería | `HID-TUB-071`, `HID-STL-014` |
| `ACB-[A-Z]{3}-[0-9]{3}` | SKU Almacén: Acabados e Inmueble | `ACB-PNT-101`, `ACB-SLV-305` |

---

## 6. Guía de Implementación en el Proyecto (Tailwind CSS v4 + Blade)

El proyecto cuenta con **Tailwind CSS v4** (`@tailwindcss/vite`) en `resources/css/app.css`. A continuación se detallan las configuraciones y componentes listos para utilizar en las vistas Blade de Laravel.

### 6.1. Configuración de Tema en `resources/css/app.css`

```css
@import 'tailwindcss';

@theme {
  --font-sans: 'Instrument Sans', 'Inter', system-ui, -apple-system, sans-serif;

  /* Colores Corporativos SIMIC */
  --color-brand-navy: #0B2239;
  --color-brand-navy-dark: #071626;
  --color-brand-teal: #0D9488;
  --color-brand-sky: #0284C7;

  /* Superficies */
  --color-surface-canvas: #F8FAFC;
  --color-surface-card: #FFFFFF;
  --color-surface-subtle: #F1F5F9;
}
```

### 6.2. Snippets de Componentes Reutilizables en Blade

#### A. Tarjeta de Métrica (KPI Card)
```html
<div class="bg-white border border-slate-200 rounded-xl p-5 shadow-xs">
    <div class="flex items-center justify-between">
        <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Incidencias Activas</span>
        <div class="p-2 bg-sky-50 text-sky-700 rounded-lg">
            <!-- Icono SVG -->
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
        </div>
    </div>
    <div class="mt-4 flex items-baseline gap-2">
        <span class="text-3xl font-bold text-slate-900 tracking-tight">142</span>
        <span class="text-xs font-medium text-slate-500">+12 turno matutino</span>
    </div>
    <div class="mt-3 w-full bg-slate-100 rounded-full h-1.5">
        <div class="bg-brand-navy h-1.5 rounded-full w-2/3"></div>
    </div>
</div>
```

#### B. Badges de Criticidad y Estado
```html
<!-- Crítico / Presupuesto Excedido -->
<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-red-100 text-red-700 border border-red-200">
    Crítica
</span>

<!-- Alta / Trabajo en Alturas -->
<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-100 text-amber-800 border border-amber-200">
    Alta
</span>

<!-- Asignado / En Proceso -->
<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-sky-100 text-sky-700 border border-sky-200">
    Asignado a Técnico
</span>

<!-- Óptimo / Solucionado -->
<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800 border border-emerald-200">
    Óptimo
</span>
```

#### C. Botón Primario Institucional
```html
<button class="inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-brand-navy hover:bg-brand-navy-dark text-white text-sm font-semibold rounded-lg shadow-xs transition-colors duration-150">
    <span>Pre-notificar Almacén</span>
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
</button>
```

#### D. Encabezado de Tabla Oscura (Estilo Centro de Mando)
```html
<div class="overflow-hidden border border-slate-200 rounded-xl bg-white shadow-xs">
    <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
        <thead class="bg-brand-navy text-white text-xs uppercase tracking-wider">
            <tr>
                <th class="px-4 py-3.5 font-semibold">Folio Origen</th>
                <th class="px-4 py-3.5 font-semibold">Reportante Institucional</th>
                <th class="px-4 py-3.5 font-semibold">Hora</th>
                <th class="px-4 py-3.5 font-semibold">Descripción del Usuario</th>
                <th class="px-4 py-3.5 font-semibold text-right">Acción</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-200 text-slate-700">
            <!-- Filas dinámicas -->
        </tbody>
    </table>
</div>
```

---

## 7. Resumen de Recomendaciones para el Desarrollo

1. **Mantener la consistencia de los códigos y folios**: Siempre preservar las máscaras de entrada para `#INC-`, `OT-`, `VAL-` y códigos SKU para mantener la trazabilidad gráfica mostrada en las capturas.
2. **Priorizar la densidad de información limpia**: Evitar espacios vacíos excesivos o márgenes desproporcionados; el diseño se caracteriza por su eficiencia de espacio para operadores y coordinadores en turnos en vivo.
3. **Validación visual inmediata**: Utilizar siempre los componentes de badges y estados en las tablas para que el usuario identifique alertas críticas a primer golpe de vista.
