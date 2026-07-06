<?php
/**
 * Copyright (C) 2026 Oko Digital Experts, S.L.L. (Okodex)
 */

namespace FacturaScripts\Test\Plugins;

use FacturaScripts\Core\Base\MiniLog;
use FacturaScripts\Core\Where;
use FacturaScripts\Dinamic\Model\Alias;
use FacturaScripts\Dinamic\Model\AliasType;
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
 * - Al borrar el cliente/proveedor, sus alias se eliminan (**borrado en cascada**).
 * - Se **rechaza** crear un alias para un código inexistente (FK simulada).
 */
final class AliasClientesTest extends TestCase
{
    use LogErrorsTrait;
    use RandomDataTrait;

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

    protected function ensureType(string $code, string $description): void
    {
        $type = new AliasType();
        if (false === $type->loadWhere([Where::eq('aliastype', $code)])) {
            $type->aliastype = $code;
            $type->description = $description;
            $type->save();
        }
    }

    protected function setUp(): void
    {
        // aseguramos los tipos del catálogo (normalmente los siembra Init::update)
        $this->ensureType(Init::ALIAS_TYPE_CLIENT, 'Cliente');
        $this->ensureType(Init::ALIAS_TYPE_PROVIDER, 'Proveedor');
    }

    protected function tearDown(): void
    {
        $this->logErrors();
    }
}
