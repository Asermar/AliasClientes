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
    /** Tipo de alias que usa este plugin (catálogo aliastypes) */
    const ALIAS_TYPE = 'client';

    public function init(): void
    {
        $this->loadExtension(new Extension\Controller\EditCliente());
        $this->loadExtension(new Extension\Model\Cliente());
        $this->loadExtension(new Extension\Model\Alias());
    }

    public function uninstall(): void
    {
    }

    public function update(): void
    {
        // aseguramos que el tipo de alias 'client' exista en el catálogo
        $type = new AliasType();
        if (false === $type->loadWhere([Where::eq('aliastype', self::ALIAS_TYPE)])) {
            $type->aliastype = self::ALIAS_TYPE;
            $type->description = 'Cliente';
            $type->save();
        }
    }
}
