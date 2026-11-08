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

namespace FacturaScripts\Test\Plugins;

use FacturaScripts\Core\Base\MiniLog;
use FacturaScripts\Dinamic\Model\Alias;
use FacturaScripts\Dinamic\Model\AliasType;
use FacturaScripts\Dinamic\Model\Cliente;
use FacturaScripts\Dinamic\Model\Proveedor;
use FacturaScripts\Plugins\AliasClientes\Init;
use FacturaScripts\Test\Traits\LogErrorsTrait;
use FacturaScripts\Test\Traits\RandomDataTrait;
use PHPUnit\Framework\TestCase;

/**
 * @author Alexis Serafín <alexis@okodex.com>
 *
 * @description
 * ## Alias de clientes y proveedores
 *
 * Integra el plugin `Alias` en las fichas de **clientes** y **proveedores**:
 *
 * - Se puede crear un alias asociado a un cliente/proveedor existente.
 * - Al borrar el cliente/proveedor, sus alias se eliminan (**borrado en cascada**), y si alguno
 *   no se puede borrar queda un error en el log.
 * - Al **renombrar** el código del cliente/proveedor, sus alias lo siguen.
 * - Se **rechaza** crear un alias para un código inexistente (FK simulada).
 */
final class AliasClientesTest extends TestCase
{
    use LogErrorsTrait;
    use RandomDataTrait;

    /** prefijo de los alias de prueba, para limpiar los que deje una pasada muerta a medias */
    const ALIAS_PREFIX = 'CLITEST-';

    /**
     * @description Alias de un **cliente**: se crea y, al borrar el cliente, se borra en cascada.
     */
    public function testClientAliasAndCascadeDelete(): void
    {
        $cliente = $this->getRandomCustomer();
        $this->assertTrue($cliente->save(), 'no se pudo crear el cliente');

        $alias = new Alias();
        $alias->aliastype = Init::ALIAS_TYPE_CLIENT;
        $alias->cod = $cliente->codcliente;
        $alias->alias = 'CLI-ALIAS';
        $this->assertTrue($alias->save(), 'no se pudo guardar el alias del cliente');
        $aliasId = $alias->id;

        // al borrar el cliente, sus alias deben eliminarse (cascade simulado)
        $this->assertTrue($cliente->delete());

        $check = new Alias();
        $this->assertFalse($check->load($aliasId), 'el alias debería borrarse al borrar el cliente');
    }

    /**
     * @description No permite guardar un alias de **cliente** para un `codcliente` inexistente.
     */
    public function testClientAliasRejectsMissingCustomer(): void
    {
        $alias = new Alias();
        $alias->aliastype = Init::ALIAS_TYPE_CLIENT;
        $alias->cod = 'NO-EXISTE';
        $alias->alias = 'X';
        $this->assertFalse($alias->save(), 'no debe guardar alias de un cliente inexistente');

        // la validación (FK simulada) registra un warning; limpiamos el log
        MiniLog::clear();
    }

    /**
     * @description Alias de un **proveedor**: se crea y, al borrar el proveedor, se borra en cascada.
     */
    public function testSupplierAliasAndCascadeDelete(): void
    {
        $proveedor = $this->getRandomSupplier();
        $this->assertTrue($proveedor->save(), 'no se pudo crear el proveedor');

        $alias = new Alias();
        $alias->aliastype = Init::ALIAS_TYPE_PROVIDER;
        $alias->cod = $proveedor->codproveedor;
        $alias->alias = 'PROV-ALIAS';
        $this->assertTrue($alias->save(), 'no se pudo guardar el alias del proveedor');
        $aliasId = $alias->id;

        $this->assertTrue($proveedor->delete());

        $check = new Alias();
        $this->assertFalse($check->load($aliasId), 'el alias debería borrarse al borrar el proveedor');
    }

    /**
     * @description No permite guardar un alias de **proveedor** para un `codproveedor` inexistente.
     */
    public function testSupplierAliasRejectsMissingSupplier(): void
    {
        $alias = new Alias();
        $alias->aliastype = Init::ALIAS_TYPE_PROVIDER;
        $alias->cod = 'NO-EXISTE';
        $alias->alias = 'Y';
        $this->assertFalse($alias->save(), 'no debe guardar alias de un proveedor inexistente');

        MiniLog::clear();
    }

    /**
     * @description Si un alias no se puede borrar en la cascada del **cliente**, queda un error.
     */
    public function testClientCascadeReportsAliasDeleteFailure(): void
    {
        $cliente = $this->getRandomCustomer();
        $this->assertTrue($cliente->save(), 'no se pudo crear el cliente');
        $alias = $this->makeAlias(Init::ALIAS_TYPE_CLIENT, $cliente->codcliente, 'CLI-NODEL');

        $this->assertCascadeReportsFailure($cliente, $alias);
    }

    /**
     * @description Al renombrar `codcliente`, los alias del cliente pasan al código nuevo.
     */
    public function testClientRenameMovesAliases(): void
    {
        $cliente = $this->getRandomCustomer();
        $this->assertTrue($cliente->save(), 'no se pudo crear el cliente');
        $alias = $this->makeAlias(Init::ALIAS_TYPE_CLIENT, $cliente->codcliente, 'CLI-RENAME');

        $newCode = $this->freeCode(Cliente::class);
        $this->assertTrue($cliente->changeId($newCode), 'no se pudo renombrar el cliente');
        $this->assertAliasCod($alias, $newCode);

        $this->assertTrue($cliente->delete());
    }

    /**
     * @description Si un alias no se puede borrar en la cascada del **proveedor**, queda un error.
     */
    public function testSupplierCascadeReportsAliasDeleteFailure(): void
    {
        $proveedor = $this->getRandomSupplier();
        $this->assertTrue($proveedor->save(), 'no se pudo crear el proveedor');
        $alias = $this->makeAlias(Init::ALIAS_TYPE_PROVIDER, $proveedor->codproveedor, 'PROV-NODEL');

        $this->assertCascadeReportsFailure($proveedor, $alias);
    }

    /**
     * @description Al renombrar `codproveedor`, los alias del proveedor pasan al código nuevo.
     */
    public function testSupplierRenameMovesAliases(): void
    {
        $proveedor = $this->getRandomSupplier();
        $this->assertTrue($proveedor->save(), 'no se pudo crear el proveedor');
        $alias = $this->makeAlias(Init::ALIAS_TYPE_PROVIDER, $proveedor->codproveedor, 'PROV-RENAME');

        $newCode = $this->freeCode(Proveedor::class);
        $this->assertTrue($proveedor->changeId($newCode), 'no se pudo renombrar el proveedor');
        $this->assertAliasCod($alias, $newCode);

        $this->assertTrue($proveedor->delete());
    }

    protected function setUp(): void
    {
        // un alias de prueba que sobrevive a su entidad (test cortado a medias) quedaría huérfano
        Alias::table()->whereLike('alias', self::ALIAS_PREFIX . '%')->delete();

        // aseguramos los tipos del catálogo, como hace Init::update
        AliasType::ensure(Init::ALIAS_TYPE_CLIENT, 'Cliente', Init::PLUGIN_NAME);
        AliasType::ensure(Init::ALIAS_TYPE_PROVIDER, 'Proveedor', Init::PLUGIN_NAME);
    }

    protected function tearDown(): void
    {
        $this->logErrors();
    }

    private function assertAliasCod(Alias $alias, string $expected): void
    {
        $reload = new Alias();
        $this->assertTrue($reload->load($alias->id));
        $this->assertSame($expected, (string)$reload->cod, 'el alias debe seguir al código renombrado');
    }

    /** la entidad se borra, pero el alias que no se pudo borrar deja un error en el log */
    private function assertCascadeReportsFailure($entity, Alias $alias): void
    {
        // a partir de aquí, borrar alias falla
        Alias::addExtension(new class {
            public function deleteBefore()
            {
                return function () {
                    return false;
                };
            }
        });

        MiniLog::clear();
        try {
            $this->assertTrue($entity->delete(), 'la entidad sí se borra');
        } finally {
            Alias::removeExtension('deleteBefore');
        }

        $this->assertContains('alias-delete-error', array_column(MiniLog::read('', ['error']), 'original'));
        $this->assertTrue($alias->exists(), 'el alias que no se pudo borrar sigue ahí');
        MiniLog::clear();

        $this->assertTrue($alias->delete());
    }

    /** un código de 10 caracteres como máximo que no usa nadie en la tabla del modelo */
    private function freeCode(string $class): string
    {
        do {
            $code = 'R' . mt_rand(100000, 999999);
        } while ((new $class())->load($code));
        return $code;
    }

    private function makeAlias(string $type, string $cod, string $text): Alias
    {
        $alias = new Alias();
        $alias->aliastype = $type;
        $alias->cod = $cod;
        $alias->alias = self::ALIAS_PREFIX . $text . '-' . mt_rand(1, 99999);
        $this->assertTrue($alias->save(), 'no se pudo guardar el alias ' . $text);
        return $alias;
    }
}
