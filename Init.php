<?php
/**
 * Copyright (C) 2026 Oko Digital Experts, S.L.L. (Okodex)
 */

namespace FacturaScripts\Plugins\AliasClientes;

use FacturaScripts\Core\Template\InitClass;
use FacturaScripts\Dinamic\Model\AliasType;

/**
 * @author Alexis Serafín <alexis@okodex.com>
 */
class Init extends InitClass
{
    /** Tipo de alias para clientes (catálogo aliastypes) */
    const ALIAS_TYPE_CLIENT = 'client';

    /** Tipo de alias para proveedores (catálogo aliastypes) */
    const ALIAS_TYPE_PROVIDER = 'supplier';

    /** Nombre del plugin, responsable de estos tipos de alias (catálogo aliastypes) */
    const PLUGIN_NAME = 'AliasClientes';

    public function init(): void
    {
        $this->loadExtension(new Extension\Controller\EditCliente());
        $this->loadExtension(new Extension\Controller\EditProveedor());
        $this->loadExtension(new Extension\Model\Cliente());
        $this->loadExtension(new Extension\Model\Proveedor());
        $this->loadExtension(new Extension\Model\Alias());
    }

    public function uninstall(): void
    {
    }

    public function update(): void
    {
        // aseguramos que los tipos de alias existan en el catálogo, indicando el plugin responsable
        AliasType::ensure(self::ALIAS_TYPE_CLIENT, 'Cliente', self::PLUGIN_NAME);
        AliasType::ensure(self::ALIAS_TYPE_PROVIDER, 'Proveedor', self::PLUGIN_NAME);
    }
}
