<?php
/**
 * Copyright (C) 2026 Alexis Serafin <alexis@okodex.com>
 */

namespace FacturaScripts\Plugins\AliasClientes\Extension\Model;

use Closure;
use FacturaScripts\Core\Tools;
use FacturaScripts\Dinamic\Model\Cliente;
use FacturaScripts\Dinamic\Model\Proveedor;
use FacturaScripts\Plugins\AliasClientes\Init;

/**
 * FK simulada: valida que cod sea un registro existente del modelo asociado al tipo.
 *
 * @author Alexis Serafin <alexis@okodex.com>
 */
class Alias
{
    public function test(): Closure
    {
        return function () {
            $map = [
                Init::ALIAS_TYPE_CLIENT => Cliente::class,
                Init::ALIAS_TYPE_PROVIDER => Proveedor::class,
            ];

            $class = $map[$this->aliastype] ?? null;
            if (null === $class) {
                return true;
            }

            $model = new $class();
            if (false === $model->load($this->cod)) {
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
