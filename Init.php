<?php
/**
 * Copyright (C) 2026 Oko Digital Experts, S.L.L. (Okodex)
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see https://www.gnu.org/licenses/.
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
