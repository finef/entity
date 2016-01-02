<?php

namespace Entity\Repository;

use \Fine\Container;

class RepositoryContainer extends RepositoryAbstract implements RepositoryInterface, ContainerInterface
{

    use ContainerTrait;

    // public function save(array $entity)
    // {
    //     if (!isset($entity['entity_type'])) {
    //         throw new \LogicException();
    //     }
    //
    //     if (!isset($this->{$entity['entity_type']})) {
    //         throw new \LogicException();
    //     }
    //
    //     return parent::save($entity);
    // }

}
