<?php
/**
 * Copyright (C) 2026 Alexis Serafin <alexis@okodex.com>
 */

namespace FacturaScripts\Plugins\AliasClientes\Extension\Model;

use Closure;
use FacturaScripts\Core\Tools;
use FacturaScripts\Dinamic\Model\Cliente;
use FacturaScripts\Plugins\AliasClientes\Init;

/**
 * FK simulada para el tipo 'client': valida que cod sea un cliente existente.
 *
 * @author Alexis Serafin <alexis@okodex.com>
 */
class Alias
{
    public function test(): Closure
    {
        return function () {
            if ($this->aliastype !== Init::ALIAS_TYPE) {
                return true;
            }

            $cliente = new Cliente();
            if (false === $cliente->load($this->cod)) {
                Tools::log()->warning('alias-cod-not-found', [
                    '%aliastype%' => $this->aliastype,
                    '%cod%' => $this->cod,
                ]);
                return false;
            }

            return true;
        };
    }
}
