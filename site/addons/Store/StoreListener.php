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

        // Add second level navigation items to it
        $store->add(function ($item) {
           $item->add(Nav::item('Saugroboter')->route('amazon.saugroboter') );
           $item->add(Nav::item('Mähroboter')->route('amazon.maehroboter'));
        });

        // Finally, add our first level navigation item
        // to the navigation under the 'tools' section.
        $nav->addTo('tools', $store);
    }
}
