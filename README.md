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
  denominaciones sin salir de la ficha.
- **Valida que el alias apunte a un registro que existe**: la relación con el cliente o el proveedor no
  la conoce la base de datos, así que se comprueba al guardar.
- **Borra los alias en cascada** al eliminar el cliente o el proveedor, para que no queden huérfanos
  ocupando denominaciones que después no se pueden reutilizar.

## Requisitos

- [`Alias`](https://github.com/Asermar/Alias)
- FacturaScripts con core **2025** o superior, y PHP **8** o superior.

## Changelog

Cambios destacados por versión (la versión es la de `facturascripts.ini`, único punto de verdad).

### 1.0x — Alias de clientes y proveedores

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
