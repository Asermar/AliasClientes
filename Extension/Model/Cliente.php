<?php
/**
 * Copyright (C) 2026 Oko Digital Experts, S.L.L. (Okodex)
 */

namespace FacturaScripts\Plugins\AliasClientes\Extension\Model;

use Closure;
use FacturaScripts\Core\Where;
use FacturaScripts\Dinamic\Model\Alias;
use FacturaScripts\Plugins\AliasClientes\Init;

/**
 * Simula el ON DELETE CASCADE: al eliminar un cliente, borra sus alias.
 *
 * @author Alexis Serafín <alexis@okodex.com>
 */
class Cliente
{
    public function delete(): Closure
    {
        return function () {
            $where = [
                Where::eq('aliastype', Init::ALIAS_TYPE_CLIENT),
                Where::eq('cod', $this->codcliente),
            ];
            foreach (Alias::all($where) as $alias) {
                $alias->delete();
            }
        };
    }
}
