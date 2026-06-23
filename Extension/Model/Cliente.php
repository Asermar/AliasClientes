<?php
/**
 * Copyright (C) 2026 Alexis Serafin <alexis@okodex.com>
 */

namespace FacturaScripts\Plugins\AliasClientes\Extension\Model;

use Closure;
use FacturaScripts\Core\Where;
use FacturaScripts\Dinamic\Model\Alias;
use FacturaScripts\Plugins\AliasClientes\Init;

/**
 * Simula el ON DELETE CASCADE: al eliminar un cliente, borra sus alias.
 *
 * @author Alexis Serafin <alexis@okodex.com>
 */
class Cliente
{
    public function delete(): Closure
    {
        return function () {
            $where = [
                Where::eq('aliastype', Init::ALIAS_TYPE),
                Where::eq('cod', $this->codcliente),
            ];
            foreach (Alias::all($where) as $alias) {
                $alias->delete();
            }
        };
    }
}
