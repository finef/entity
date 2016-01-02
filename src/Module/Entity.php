<?php

namespace Entity\Module;

class Entity
{

    public function subentity($container)
    {
        $container(array(
            'attr'     => '\Entity\Subentity\Attr',
            'dispatch' => '\Entity\Subentity\Dispatch',
            'link'     => '\Entity\Subentity\Link',
            'order'    => '\Entity\Subentity\Order',
            'route'    => '\Entity\Subentity\Route',
            'rel'      => '\Entity\Subentity\Rel',
            'tag'      => '\Entity\Subentity\Tag',
            'tree'     => '\Entity\Subentity\Tree',
        ));
    }

}
