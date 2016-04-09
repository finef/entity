<?php

namespace Entity\Repository;

use \Fine\Container;

class RepositoryContainer extends RepositoryAbstract implements RepositoryInterface, ContainerInterface
{

    use ContainerTrait;

    protected $_db;
    
    public function setDb($db) 
    {
        $this->_db = $db;
        return $this;
    }

    public function getDb() 
    {
        return $this->_db;
    }

    public function save(array $entity)
    {
        if (!isset($entity['entity_type'])) {
            throw new \LogicException();
        }

        if (!isset($this->{$entity['entity_type']})) {
            throw new \LogicException();
        }

        /** @TODO*/
    }

}
