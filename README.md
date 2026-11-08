# AliasClientes

<p align="center">
  <a href="https://github.com/Asermar/AliasClientes/tags"><img alt="Versión" src="https://img.shields.io/github/v/tag/Asermar/AliasClientes?style=for-the-badge&label=Versi%C3%B3n&color=2E7D6E"></a>
  <img alt="FacturaScripts" src="https://img.shields.io/badge/FacturaScripts-core_2025%2B-2670c9?style=for-the-badge">
  <img alt="Requiere" src="https://img.shields.io/badge/Requiere-Alias-6C4FD8?style=for-the-badge">
  <a href="LICENSE"><img alt="Licencia" src="https://img.shields.io/github/license/Asermar/AliasClientes?style=for-the-badge&color=A42E2B"></a>
</p>

Satélite del plugin [Alias](https://github.com/Asermar/Alias) para **clientes y proveedores**: permite
darles **nombres alternativos** —el nombre comercial con el que se les conoce, una abreviatura, el apodo
de siempre— y localizarlos por cualquiera de ellos.

## Funcionalidades

- **Registra dos tipos de alias** en el catálogo: `client` (clientes) y `supplier` (proveedores).
- **Pestaña de alias en las fichas** de cliente y proveedor, para dar de alta y de baja sus
  denominaciones sin salir de la ficha. Esa pestaña **sólo alcanza los alias de la ficha abierta**:
  lo acota la guarda de `Alias`.
- **Valida que el alias apunte a un registro que existe**: la relación con el cliente o el proveedor no
  la conoce la base de datos, así que se comprueba al guardar.
- **Borra los alias en cascada** al eliminar el cliente o el proveedor, para que no queden huérfanos
  ocupando denominaciones que después no se pueden reutilizar.
- **Al cambiar el código** de un cliente o de un proveedor, sus alias pasan al código nuevo.

## Requisitos

- [`Alias`](https://github.com/Asermar/Alias) **1.03** o superior (la guarda de la pestaña de alias).
- FacturaScripts con core **2025** o superior, y PHP **8** o superior.

## Changelog

Cambios destacados por versión (la versión es la de `facturascripts.ini`, único punto de verdad).

### 1.0x — Alias de clientes y proveedores

- **1.03** — **La pestaña de alias de la ficha deja de alcanzar los alias de otras fichas.** Mostraba
  sólo los del cliente o proveedor abierto, pero el formulario manda el tipo y el código y las
  acciones del núcleo cargan por código: con permiso para modificar clientes se podía crear, editar o
  borrar **cualquier** alias del sistema. Ahora la pestaña pasa por la guarda `AliasTabGuard` de
  `Alias`, que anula con un aviso lo que venga de fuera de la ficha, sin escribir nada.

  > **Al actualizar**: esta versión necesita **`Alias` 1.03 o superior**, que es donde vive la guarda.
  > Instala `Alias` **antes o a la vez**. No hay que desactivar nada previamente, y esto está medido
  > en la instalación de pruebas, no supuesto: con `Alias` 1.02 y este plugin al día, la ficha de
  > cliente no abre —«Class FacturaScripts\Dinamic\Lib\AliasTabGuard not found»— pero el **gestor de
  > plugins sigue funcionando**, así que se arregla instalando `Alias` desde la propia interfaz, sin
  > tocar `plugins.json`. El `require_min` del `.ini` lo deja escrito, pero el núcleo **no lo lee**:
  > es documentación, no una puerta.
- **1.02** — **Los alias siguen al código renombrado.** Al cambiar el `codcliente` o el
  `codproveedor` de una ficha, sus alias pasan al código nuevo; antes se quedaban apuntando a uno que
  ya no existía, así que la entidad perdía sus denominaciones y éstas seguían ocupando sitio en el
  índice sin que nadie pudiera reutilizarlas. Y cuando la cascada de borrado no puede con alguno de
  los alias, **queda un error que lo nombra** en vez de desaparecer en silencio: el cliente se borra
  igual —porque borrado está—, pero se sabe qué alias quedó suelto.
- **1.01** — El campo de alias admite **100 caracteres**, acompañando la ampliación del plugin `Alias`.
  Hacía falta tocarlo aquí: si el widget se quedara en 50, el formulario recortaría en silencio un alias
  que la base de datos sí acepta.
- **1.00** — Primera versión estable: licencia **GPL v3**, compatibilidad declarada de forma explícita
  (core **2025** y PHP **8**) y limpieza del andamiaje vacío que deja el generador de plugins.

> Las versiones `0.x` están en el historial de git; no se reconstruyen aquí para no inventar notas que
> nunca se escribieron.

## Licencia

GNU General Public License v3 — ver [LICENSE](LICENSE).

Copyright (C) 2026 Oko Digital Experts, S.L.L. (Okodex)
@author Alexis Serafín <alexis@okodex.com>
