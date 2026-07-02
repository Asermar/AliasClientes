<?php
/**
 * Copyright (C) 2026 Oko Digital Experts, S.L.L. (Okodex)
 */

namespace FacturaScripts\Plugins\AliasClientes\Extension\Controller;

use Closure;
use FacturaScripts\Core\Where;
use FacturaScripts\Plugins\AliasClientes\Init;

/**
 * Extiende EditCliente para mostrar y gestionar los alias del cliente.
 *
 * @author Alexis Serafín <alexis@okodex.com>
 */
class EditCliente
{
    public function createViews(): Closure
    {
        return function () {
            $this->addEditListView('EditAliasCliente', 'Alias', 'aliases', 'fa-solid fa-tags');
        };
    }

    public function loadData(): Closure
    {
        return function ($viewName, $view) {
            if ($viewName !== 'EditAliasCliente') {
                return;
            }

            // filtramos los alias de tipo 'client' del cliente actual; este mismo where
            // autorrellena aliastype y cod en el formulario de alta (EditListView::loadData)
            $mvn = $this->getMainViewName();
            $where = [
                Where::eq('aliastype', Init::ALIAS_TYPE_CLIENT),
                Where::eq('cod', $this->views[$mvn]->model->id()),
            ];
            $view->loadData('', $where);
        };
    }
}
