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
use FacturaScripts\Core\Lib\ControllerPermissions;
use FacturaScripts\Core\Lib\MultiRequestProtection;
use FacturaScripts\Core\Response;
use FacturaScripts\Core\Session;
use FacturaScripts\Core\Tools;
use FacturaScripts\Dinamic\Model\Alias;
use FacturaScripts\Dinamic\Model\AliasType;
use FacturaScripts\Dinamic\Model\Cliente;
use FacturaScripts\Dinamic\Model\Proveedor;
use FacturaScripts\Dinamic\Model\User;
use FacturaScripts\Plugins\AliasClientes\Init;
use FacturaScripts\Test\Traits\LogErrorsTrait;
use FacturaScripts\Test\Traits\RandomDataTrait;
use PHPUnit\Framework\TestCase;

/**
 * @author Alexis Serafín <alexis@okodex.com>
 *
 * @description
 * ## La pestaña de alias de la ficha sólo toca los alias de esa ficha
 *
 * Se ejecuta el controlador real (`privateCore`) con la petición en `$_GET`/`$_POST`, como llega del
 * navegador. Con la ficha del cliente A abierta, la pestaña de alias:
 *
 * - **no** edita ni borra un alias de otro cliente ni de otro tipo, ni uno a uno ni en lote;
 * - **no** crea un alias con un tipo o un código que no sean los de la ficha (se rechaza, no se corrige);
 * - sí crea, edita y borra los alias del cliente A;
 * - anula la acción tanto si llega por POST como por GET, y deja la página usable después.
 */
final class AliasClientesPestanaTest extends TestCase
{
    use LogErrorsTrait;
    use RandomDataTrait;

    const PAGE = 'EditCliente';

    const PREFIX = 'CLITAB-';

    const TAB = 'EditAliasCliente';

    /** @var User */
    private $admin;

    /** @var Cliente */
    private $clientA;

    /** @var Cliente */
    private $clientB;

    /** @var array */
    private $globals = [];

    /** @var mixed el usuario de sesión de antes, que se restaura: los alias guardan su nick con FK */
    private $sessionUser;

    /** @var Alias alias de otro cliente */
    private $otherEntity;

    /** @var Alias alias de un proveedor (otro tipo) */
    private $otherType;

    /** @var Alias alias del cliente A (la ficha abierta) */
    private $own;

    /** @var Proveedor */
    private $supplier;

    /**
     * @description La acción anulada no deja la página rota: otra petición legítima, con su token, funciona.
     */
    public function testAfterRejectionTheTabStillWorks(): void
    {
        $this->runController(self::TAB, ['action' => 'delete', 'code' => $this->otherEntity->id]);
        $this->assertTrue($this->otherEntity->exists());

        $this->runController(self::TAB, ['action' => 'delete', 'code' => $this->own->id]);
        $this->assertFalse($this->own->exists(), 'tras un rechazo, el borrado legítimo debe funcionar');
    }

    /**
     * @description El borrado en lote con un alias propio y uno ajeno se rechaza entero.
     */
    public function testBulkDeleteWithForeignAliasIsRejected(): void
    {
        $controller = $this->runController(self::TAB, ['action' => 'delete', 'codes' => [$this->own->id, $this->otherEntity->id]]);

        $this->assertTrue($this->own->exists(), 'no debe borrarse ninguno');
        $this->assertTrue($this->otherEntity->exists(), 'no debe borrarse ninguno');
        $this->assertRejected($controller);
    }

    /**
     * @description No borra un alias de otro cliente ni de otro tipo.
     */
    public function testDeleteForeignAliasIsRejected(): void
    {
        foreach ([$this->otherEntity, $this->otherType] as $foreign) {
            $controller = $this->runController(self::TAB, ['action' => 'delete', 'code' => $foreign->id]);
            $this->assertTrue($foreign->exists(), 'el alias ajeno no debe borrarse');
            $this->assertRejected($controller);
        }
    }

    /**
     * @description La acción también se anula si llega por la query string (GET) con los datos en POST.
     */
    public function testDeleteForeignAliasWithActionInQueryIsRejected(): void
    {
        $controller = $this->runController(self::TAB, ['code' => $this->otherEntity->id], ['action' => 'delete']);

        $this->assertTrue($this->otherEntity->exists(), 'el alias ajeno no debe borrarse');
        $this->assertRejected($controller);
    }

    /**
     * @description No edita un alias de otro cliente ni de otro tipo, aunque el formulario mande los de la ficha.
     */
    public function testEditForeignAliasIsRejected(): void
    {
        foreach ([$this->otherEntity, $this->otherType] as $foreign) {
            $controller = $this->runController(self::TAB, [
                'action' => 'edit',
                'code' => $foreign->id,
                'id' => $foreign->id,
                'aliastype' => Init::ALIAS_TYPE_CLIENT,
                'cod' => $this->clientA->codcliente,
                'alias' => self::PREFIX . 'HACK-' . mt_rand(1, 99999),
            ]);
            $this->assertUnchanged($foreign);
            $this->assertRejected($controller);
        }
    }

    /**
     * @description No crea un alias con un tipo o un código que no sean los de la ficha.
     */
    public function testInsertForeignAliasIsRejected(): void
    {
        $cases = [
            [Init::ALIAS_TYPE_CLIENT, $this->clientB->codcliente],
            [Init::ALIAS_TYPE_PROVIDER, $this->supplier->codproveedor],
        ];
        foreach ($cases as [$type, $cod]) {
            $text = self::PREFIX . 'NEW-' . mt_rand(1, 99999);
            $controller = $this->runController(self::TAB, ['action' => 'insert', 'aliastype' => $type, 'cod' => $cod, 'alias' => $text]);
            $this->assertSame(0, Alias::table()->whereEq('alias', $text)->count(), 'no debe crearse el alias ajeno');
            $this->assertRejected($controller);
        }
    }

    /**
     * @description Una ficha **nueva** (sin código) sigue funcionando: se puede crear el cliente.
     */
    public function testNewCustomerFormStillWorks(): void
    {
        $name = 'Cliente pestaña ' . mt_rand(1, 999999);
        $this->runController(self::PAGE, ['action' => 'insert', 'nombre' => $name, 'cifnif' => 'B' . mt_rand(1, 999999)], [], '');

        $created = new Cliente();
        $this->assertTrue($created->loadWhere([\FacturaScripts\Core\Where::eq('nombre', $name)]), 'la ficha nueva debe poder crear el cliente');
        $this->assertTrue($created->delete());
    }

    /**
     * @description La ficha del **proveedor** tampoco toca alias ajenos (de un cliente).
     */
    public function testSupplierTabRejectsForeignAlias(): void
    {
        $code = $this->supplier->codproveedor;
        $this->runController('EditAliasProveedor', ['action' => 'delete', 'code' => $this->own->id], [], $code, 'EditProveedor');
        $this->assertTrue($this->own->exists(), 'la ficha del proveedor no debe borrar un alias de cliente');

        $controller = $this->runController('EditAliasProveedor', [
            'action' => 'edit',
            'code' => $this->own->id,
            'id' => $this->own->id,
            'aliastype' => Init::ALIAS_TYPE_PROVIDER,
            'cod' => $code,
            'alias' => self::PREFIX . 'HACK-' . mt_rand(1, 99999),
        ], [], $code, 'EditProveedor');
        $this->assertUnchanged($this->own);
        $this->assertRejected($controller);
    }

    /**
     * @description El caso normal: crear, editar y borrar un alias del cliente abierto.
     */
    public function testOwnAliasesStillWork(): void
    {
        $text = self::PREFIX . 'MINE-' . mt_rand(1, 99999);
        $this->runController(self::TAB, [
            'action' => 'insert',
            'aliastype' => Init::ALIAS_TYPE_CLIENT,
            'cod' => $this->clientA->codcliente,
            'alias' => $text,
        ]);
        $created = new Alias();
        $this->assertTrue($created->loadWhere([\FacturaScripts\Core\Where::eq('alias', $text)]), 'debe crearse el alias propio');

        $edited = $text . '-B';
        $this->runController(self::TAB, [
            'action' => 'edit',
            'code' => $created->id,
            'id' => $created->id,
            'aliastype' => Init::ALIAS_TYPE_CLIENT,
            'cod' => $this->clientA->codcliente,
            'alias' => $edited,
        ]);
        $created->load($created->id);
        $this->assertSame($edited, $created->alias, 'debe editarse el alias propio');

        $this->runController(self::TAB, ['action' => 'delete', 'code' => $created->id]);
        $this->assertFalse($created->exists(), 'debe borrarse el alias propio');
    }

    private function assertRejected($controller): void
    {
        $this->assertContains('alias-tab-out-of-scope', array_column(MiniLog::read('', ['warning']), 'original'));
        $this->assertSame('', (string)$controller->request->input('action', ''), 'la acción debe quedar vacía en el cuerpo');
        $this->assertSame('', (string)$controller->request->query('action', ''), 'la acción debe quedar vacía en la query');
        MiniLog::clear();
    }

    private function assertUnchanged(Alias $alias): void
    {
        $reload = new Alias();
        $this->assertTrue($reload->load($alias->id), 'el alias debe seguir existiendo');
        $this->assertSame($alias->alias, $reload->alias, 'el alias no debe cambiar');
        $this->assertSame($alias->aliastype, $reload->aliastype, 'el tipo no debe cambiar');
        $this->assertSame((string)$alias->cod, (string)$reload->cod, 'el código no debe cambiar');
    }

    private function makeAlias(string $type, string $cod, string $text): Alias
    {
        $alias = new Alias();
        $alias->aliastype = $type;
        $alias->cod = $cod;
        $alias->alias = self::PREFIX . $text . '-' . mt_rand(1, 99999);
        $this->assertTrue($alias->save());
        return $alias;
    }

    /**
     * Ejecuta EditCliente como lo haría el navegador: petición en $_GET/$_POST antes de construirlo,
     * token válido para la semilla que pondrá privateCore, y privateCore real.
     */
    private function runController(string $tab, array $post, array $query = [], ?string $code = null, string $page = self::PAGE)
    {
        $code = $code ?? $this->clientA->codcliente;
        $_GET = array_merge($code === '' ? [] : ['code' => $code], $query);

        $protection = new MultiRequestProtection();
        $protection->clearSeed();
        $protection->addSeed($this->admin->nick);
        $post['multireqtoken'] = $protection->newToken();
        $protection->clearSeed();

        $_POST = array_merge(['activetab' => $tab], $post);

        MiniLog::clear();
        $class = '\\FacturaScripts\\Dinamic\\Controller\\' . $page;
        $controller = new $class($page);
        $response = new Response();
        $controller->privateCore($response, $this->admin, new ControllerPermissions($this->admin, $page));
        return $controller;
    }

    protected function setUp(): void
    {
        $this->globals = [$_GET, $_POST];
        $this->sessionUser = Session::get('user');
        Alias::table()->whereLike('alias', self::PREFIX . '%')->delete();
        AliasType::ensure(Init::ALIAS_TYPE_CLIENT, 'Cliente', Init::PLUGIN_NAME);
        AliasType::ensure(Init::ALIAS_TYPE_PROVIDER, 'Proveedor', Init::PLUGIN_NAME);

        $this->admin = new User();
        $this->admin->nick = 'clitab' . mt_rand(1, 999999);
        $this->admin->email = $this->admin->nick . '@example.test';
        $this->admin->setPassword(Tools::password());
        $this->admin->admin = true;
        $this->assertTrue($this->admin->save());
        Session::set('user', $this->admin);

        $this->clientA = $this->getRandomCustomer();
        $this->assertTrue($this->clientA->save());
        $this->clientB = $this->getRandomCustomer();
        $this->assertTrue($this->clientB->save());
        $this->supplier = $this->getRandomSupplier();
        $this->assertTrue($this->supplier->save());

        $this->own = $this->makeAlias(Init::ALIAS_TYPE_CLIENT, $this->clientA->codcliente, 'OWN');
        $this->otherEntity = $this->makeAlias(Init::ALIAS_TYPE_CLIENT, $this->clientB->codcliente, 'OTHER');
        $this->otherType = $this->makeAlias(Init::ALIAS_TYPE_PROVIDER, $this->supplier->codproveedor, 'TYPE');
    }

    protected function tearDown(): void
    {
        [$_GET, $_POST] = $this->globals;
        foreach ([$this->clientA, $this->clientB, $this->supplier] as $entity) {
            $entity->delete();
        }
        Alias::table()->whereLike('alias', self::PREFIX . '%')->delete();
        Session::set('user', $this->sessionUser);
        $this->admin->delete();
        $this->logErrors();
    }
}
