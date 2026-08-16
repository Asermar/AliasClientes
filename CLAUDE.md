# AliasClientes — guía para Claude Code

Plugin **satélite** de **[Alias](../Alias/CLAUDE.md)** que dota de **alias polimórficos** a las
fichas de **clientes** y **proveedores** del core de FacturaScripts. `version 1.00`,
`min_version 2025`, `min_php 8`, `require = 'Alias'`. Licencia **GPL v3**. Copyright **Okodex** /
autor **Alexis Serafín**. Es submódulo dentro de Mesa_FS.

> **El mecanismo general (alias polimórfico, catálogo `aliastypes`, FK simulada, favorito único,
> patrón de integración de 7 pasos, reparto de responsabilidades) está documentado a fondo en
> `../Alias/CLAUDE.md`.** Este documento solo cubre lo específico de AliasClientes. No re-explicar
> aquí la teoría; enlazar a la sección correspondiente de `Alias/CLAUDE.md`.

## Dónde se desarrolla

**Cliente prototipo: `Mesa_FS`.** Aquí se desarrolla este plugin. Las demás instalaciones lo
**consumen** como submódulo fijado a un tag, y son fuente de mejoras y arreglos.

Si lees esto desde otra instalación estás en un consumidor, y eso **no te prohíbe trabajar aquí**:
a veces el fallo solo se reproduce en este entorno y arreglarlo desde el prototipo sería trabajar a
ciegas. Lo que se pide es **preguntarlo antes**, no decidirlo en silencio — ni negarse.

Se trabaje donde se trabaje, el arreglo vive en el repo del plugin y hay que **mover el pin** de
esta instalación para que lo reciba: commitear no basta.

## Qué aporta este satélite

Sigue el molde estándar de satélite (ver `Alias/CLAUDE.md`, sección *"Cómo lo integra un plugin
satélite"*). Concretado aquí:

- **Tipos registrados** (`Init::update()` → `AliasType::ensure(...)`), con sus constantes en `Init`:
  | Constante | Código de tipo | Modelo destino | Ficha (EditController) | XMLView |
  |-----------|----------------|----------------|------------------------|---------|
  | `ALIAS_TYPE_CLIENT` | `client` | `Cliente` | `EditCliente` | `EditAliasCliente` |
  | `ALIAS_TYPE_PROVIDER` | `supplier` | `Proveedor` | `EditProveedor` | `EditAliasProveedor` |
  - `Init::PLUGIN_NAME = 'AliasClientes'` es el `plugin` responsable con que se siembran ambos tipos.

- **`Extension\Model\Alias::test()`** — FK simulada con el mapa
  `['client' => Cliente::class, 'supplier' => Proveedor::class]`; para cualquier otro `aliastype`
  devuelve `true` (invariante de convivencia; ver `Alias/CLAUDE.md`).

- **Cascada** (`Extension\Model\Cliente::delete()` / `Extension\Model\Proveedor::delete()`) — borran
  los `Alias` del tipo correspondiente cuyo `cod` es `$this->codcliente` / `$this->codproveedor`.

- **UI** — `Extension\Controller\EditCliente` y `EditProveedor`: cada una añade en `createViews()` su
  EditListView (`addEditListView('EditAlias{Cliente|Proveedor}', 'Alias', 'aliases', 'fa-solid fa-tags')`)
  y en `loadData()` filtra por `aliastype` + `cod = model->id()` (ese `Where` también autorrellena el
  formulario de alta). `model->id()` de `Cliente`/`Proveedor` devuelve `codcliente`/`codproveedor`.

- **Dos XMLView separadas** (`EditAliasCliente.xml`, `EditAliasProveedor.xml`) de **contenido
  idéntico** (columnas `id`/`aliastype`/`cod` en `display="none"`, `alias` y `favorite` visibles).
  Son dos porque hay dos EditListView con nombres distintos; el core resuelve el XMLView por nombre de
  vista, así que no pueden compartir uno.

## Particularidades

- **`Cron.php`**: es la **plantilla estándar del generador de plugins, vacía** (`run()` sin lógica).
  No ejecuta nada; parece arrastre, no funcionalidad real. No añadirle lógica salvo que se pida.
  (Los otros dos satélites no traen `Cron.php`.)
- **Traducciones extensas**: a diferencia de los satélites hermanos (solo es_ES/en_EN), este trae
  ~20 idiomas. Las claves de tipo `client`/`supplier` coinciden con los códigos de `aliastype`, así
  que el filtro de `ListAlias` (que traduce la etiqueta del tipo con `Tools::trans`) las resuelve.

## Tests (`Test/main/AliasClientesTest.php`)

`install-plugins.txt` = `Alias,AliasClientes`. Cubre por cada tipo (client/supplier): alta de alias
sobre una entidad existente + **borrado en cascada**, y **rechazo** de `cod` inexistente (limpia el
MiniLog del warning esperado con `MiniLog::clear()`). `setUp()` siembra los tipos con un helper
`ensureType()` (normalmente los sembraría `Init::update()`). Usa `RandomDataTrait`
(`getRandomCustomer`/`getRandomSupplier`) y `LogErrorsTrait`.

## Convenciones y gotchas

- **Rebuild/deploy** tras tocar `Init.php`, extensiones o XMLView (regenera `Dinamic`; si no, la
  extensión no se registra). Submódulo: commitear aquí y actualizar el puntero en Mesa_FS.
- Mantener la invariante de `test()` (devolver `true` para tipos ajenos) para no romper la
  convivencia con AliasLocalizaciones / AliasBusCanarias sobre el mismo modelo `Alias`.
- No crear `aliastypes` desde la UI; siempre por `AliasType::ensure()` en `update()`.
- Cabeceras de autoría Okodex/Alexis, GPL v3 (ver `Alias/CLAUDE.md`).

## Dudas / notas

- `Cron.php` vacío (ver arriba): confirmar si debe eliminarse o si se reserva para uso futuro.
