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
use FacturaScripts\Core\Where;
use FacturaScripts\Dinamic\Model\Alias;
use FacturaScripts\Plugins\AliasClientes\Init;

/**
 * Alias de un cliente: al borrarlo se borran sus alias (simula el ON DELETE CASCADE, que no existe
 * porque alias.cod no tiene FK real), y al renombrar su código sus alias pasan al nuevo.
 *
 * @author Alexis Serafín <alexis@okodex.com>
 */
class Cliente
{
    /**
     * Código anterior de cada registro mientras se renombra: el After lo necesita y el core ya
     * no lo conserva (changeId() sobrescribe original con el nuevo antes de llamarlo).
     */
    private $renaming = [];

    public function changePrimaryColumnValueAfter(): Closure
    {
        $renaming = &$this->renaming;
        return function () use (&$renaming) {
            $old = $renaming[spl_object_id($this)] ?? null;
            unset($renaming[spl_object_id($this)]);
            if (null === $old) {
                return;
            }

            $done = Alias::table()
                ->whereEq('aliastype', Init::ALIAS_TYPE_CLIENT)
                ->whereEq('cod', (string)$old)
                ->update(['cod' => (string)$this->codcliente]);
            if (false === $done) {
                Tools::log()->error('alias-rename-error', [
                    '%aliastype%' => Init::ALIAS_TYPE_CLIENT,
                    '%old%' => $old,
                    '%new%' => $this->codcliente,
                ]);
                return false;
            }

            (new Alias())->clearCache();
        };
    }

    public function changePrimaryColumnValueBefore(): Closure
    {
        $renaming = &$this->renaming;
        return function () use (&$renaming) {
            $renaming[spl_object_id($this)] = $this->codcliente;
        };
    }

    // corre DESPUÉS de borrar: si no se pudiera borrar el registro (p. ej. por sus documentos),
    // sus alias no se pierden. Por eso un alias que falle no cambia el resultado (el registro sí
    // se borró), pero se dice
    public function delete(): Closure
    {
        return function () {
            $where = [
                Where::eq('aliastype', Init::ALIAS_TYPE_CLIENT),
                Where::eq('cod', $this->codcliente),
            ];
            foreach (Alias::all($where) as $alias) {
                if (false === $alias->delete()) {
                    Tools::log()->error('alias-delete-error', [
                        '%alias%' => $alias->alias,
                        '%aliastype%' => Init::ALIAS_TYPE_CLIENT,
                        '%cod%' => $this->codcliente,
                    ]);
                }
            }
        };
    }
}
