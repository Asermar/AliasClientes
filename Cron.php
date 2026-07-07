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

use FacturaScripts\Core\Template\CronClass;

/**
 * El cron de FacturaScripts ejecutará todos los procesos cron de los plugins activos,
 * siempre y cuando haya sido configurado en el sistema o hosting.
 * Así que si necesita ejecutar algo de forma periódica, el mejor lugar es el cron de su plugin.
 *
 * https://facturascripts.com/publicaciones/el-archivo-cron-php-855
 */
class Cron extends CronClass
{
    public function run(): void
    {
        /*
        $this->job('mi-trabajo')
            ->everyDayAt(8)
            ->run(function () {
                // tu código aquí
                // esto se ejecutará cada día a las 8h
            });
        */
    }
}
