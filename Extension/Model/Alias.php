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

namespace FacturaScripts\Plugins\AliasClientes\Extension\Model;

use Closure;
use FacturaScripts\Core\Tools;
use FacturaScripts\Dinamic\Model\Cliente;
use FacturaScripts\Dinamic\Model\Proveedor;
use FacturaScripts\Plugins\AliasClientes\Init;

/**
 * FK simulada: valida que cod sea un registro existente del modelo asociado al tipo.
 *
 * @author Alexis Serafín <alexis@okodex.com>
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
