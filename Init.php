<?php
/**
 * Copyright (C) 2026 Alexis Serafin <alexis@okodex.com>
 */

namespace FacturaScripts\Plugins\AliasClientes;

use FacturaScripts\Core\Template\InitClass;
use FacturaScripts\Core\Where;
use FacturaScripts\Dinamic\Model\AliasType;

/**
 * @author Alexis Serafin <alexis@okodex.com>
 */
class Init extends InitClass
{
    /** Tipo de alias para clientes (catálogo aliastypes) */
    const ALIAS_TYPE_CLIENT = 'client';

    /** Tipo de alias para proveedores (catálogo aliastypes) */
    const ALIAS_TYPE_PROVIDER = 'supplier';

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
        // aseguramos que los tipos de alias existan en el catálogo
        $this->ensureAliasType(self::ALIAS_TYPE_CLIENT, 'Cliente');
        $this->ensureAliasType(self::ALIAS_TYPE_PROVIDER, 'Proveedor');
    }

    protected function ensureAliasType(string $code, string $description): void
    {
        $type = new AliasType();
        if (false === $type->loadWhere([Where::eq('aliastype', $code)])) {
            $type->aliastype = $code;
            $type->description = $description;
            $type->save();
        }
    }
}
