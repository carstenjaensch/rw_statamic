<?php

namespace Statamic\Addons\Store;

use Statamic\API\Nav;
use Statamic\Extend\Listener;

class StoreListener extends Listener
{
    public $events = [
        'cp.nav.created' => 'addNavItems'
    ];

    public function addNavItems($nav)
    {
        // Create the first level navigation item
        // Note: by using route('store'), it assumes you've set up a route named 'store'.
        $store = Nav::item('Amazon')->route('amazon.index')->icon('shopping-cart');
        $nav->addTo('tools', $store);
        $store = Nav::item('Export Saugroboter CSV')->route('amazon.export')->icon('export');

        // Add second level navigation items to it
        $store->add(function ($item) {
           $item->add(Nav::item('Import Saugroboter')->route('amazon.saugroboter') );
           $item->add(Nav::item('Import Mähroboter')->route('amazon.maehroboter'));
           //$item->add(Nav::item('Export Saugroboter CSV')->route('amazon.export'));

        });

        // Finally, add our first level navigation item
        // to the navigation under the 'tools' section.
        $nav->addTo('tools', $store);
    }
}
