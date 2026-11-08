# AliasClientes — guía para Claude Code

Plugin **satélite** de **[Alias](../Alias/CLAUDE.md)** que dota de **alias polimórficos** a las
fichas de **clientes** y **proveedores** del core de FacturaScripts. `version 1.01`,
`min_version 2025`, `min_php 8`, `require = 'Alias'`. Licencia **GPL v3**. Copyright **Okodex** /
autor **Alexis Serafín**. Es submódulo dentro de Mesa_FS.

> **El mecanismo general (alias polimórfico, catálogo `aliastypes`, FK simulada, favorito único,
> patrón de integración de 7 pasos, reparto de responsabilidades) está documentado a fondo en
> `../Alias/CLAUDE.md`.** Este documento solo cubre lo específico de AliasClientes. No re-explicar
> aquí la teoría; enlazar a la sección correspondiente de `Alias/CLAUDE.md`.

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
  los `Alias` del tipo correspondiente cuyo `cod` es `$this->codcliente` / `$this->codproveedor`. Es lo
  esperado (decisión de Alexis, 8-nov-2026; la negativa a borrar es sólo de las localizaciones). Corre
  **después** del borrado, a propósito: si el cliente no se puede borrar (p. ej. por sus documentos),
  sus alias no se pierden. Si un alias no se puede borrar, queda un error (`alias-delete-error`) que lo
  nombra; el borrado del cliente sigue contando como hecho, porque lo está.
- **Renombrado de código** — `changePrimaryColumnValueBefore/After` en las dos extensiones: al cambiar
  `codcliente` o `codproveedor`, los alias pasan al código nuevo con un único UPDATE. El Before guarda
  el código viejo en un almacén privado de la extensión, porque `changeId()` del core sobrescribe
  `original` con el nuevo antes de llamar al After.

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
sobre una entidad existente + **borrado en cascada**, el error cuando un alias no se puede borrar en
la cascada (con una extensión de prueba `deleteBefore` sobre `Alias`, retirada al acabar), el
**renombrado** del código, y **rechazo** de `cod` inexistente. `setUp()` asegura los tipos con
`AliasType::ensure()` (el catálogo no admite altas a mano) y borra los alias de prueba (`CLITEST-…`)
que hubiera dejado una pasada muerta a medias. Usa `RandomDataTrait`
(`getRandomCustomer`/`getRandomSupplier`) y `LogErrorsTrait`.

## Convenciones y gotchas

- **Rebuild/deploy** tras tocar `Init.php`, extensiones o XMLView (regenera `Dinamic`; si no, la
  extensión no se registra). Submódulo: commitear aquí y actualizar el puntero en Mesa_FS.
- Mantener la invariante de `test()` (devolver `true` para tipos ajenos) para no romper la
  convivencia con AliasLocalizaciones / AliasBusCanarias sobre el mismo modelo `Alias`.
- Los tipos sólo por `AliasType::ensure()` en `update()`: el modelo rechaza cualquier otra escritura.
- Cabeceras de autoría Okodex/Alexis, GPL v3 (ver `Alias/CLAUDE.md`).

## Dudas / notas

- `Cron.php` vacío (ver arriba): confirmar si debe eliminarse o si se reserva para uso futuro.
