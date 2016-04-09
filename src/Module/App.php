<?php

namespace Entity\Module;

use \Fine\Db\ClientInterface;
use \Fine\Db\Table;

class App
{

    public function db(ClientInterface $db)
    {
        $db(array(
            'attr' => function () use ($db) {
                return (new Table())->setDb($db)->setTable('attr')
                        ->setFields(['attr_id_entity', 'attr_key', 'attr_val']);
            },
            'dispatch' => function () {
                return new Table([
                    'setTable'  => 'dispatch',
                    'setFields' => ['dispatch_id', 'dispatch_controller', 'dispatch_attr'],
                ]);
            },
            'entity'   => '\Entity\Module\App\Db\Table\Entity',
            'order'    => '\Entity\Module\App\Db\Table\Order',
            'route'    => '\Entity\Module\App\Db\Table\Route', // path, pairs
            'rel'      => '\Entity\Module\App\Db\Table\Rel',
            'tag'      => '\Entity\Module\App\Db\Table\Tag',
            'tree' => function () {
                return new Table([
                    'setTable'  => 'tree',
                    'setFields' => ['tree_id', 'tree_id_entity', 'tree_order'],
                ]);
            },

        ));
    }

}
