<?php
/**
 * Copyright (C) 2026 Alexis Serafin <alexis@okodex.com>
 */

namespace FacturaScripts\Plugins\AliasClientes\Extension\Controller;

use Closure;
use FacturaScripts\Core\Where;
use FacturaScripts\Plugins\AliasClientes\Init;

/**
 * Extiende EditProveedor para mostrar y gestionar los alias del proveedor.
 *
 * @author Alexis Serafin <alexis@okodex.com>
 */
class EditProveedor
{
    public function createViews(): Closure
    {
        return function () {
            $this->addEditListView('EditAliasProveedor', 'Alias', 'aliases', 'fa-solid fa-tags');
        };
    }

    public function loadData(): Closure
    {
        return function ($viewName, $view) {
            if ($viewName !== 'EditAliasProveedor') {
                return;
            }

            // alias de tipo 'supplier' del proveedor actual; el where autorrellena
            // aliastype y cod en el formulario de alta (EditListView::loadData)
            $mvn = $this->getMainViewName();
            $where = [
                Where::eq('aliastype', Init::ALIAS_TYPE_PROVIDER),
                Where::eq('cod', $this->views[$mvn]->model->id()),
            ];
            $view->loadData('', $where);
        };
    }
}
