<?php

namespace \Fine\Entity\Extension;

use \Fine\Entity\Repository\RepositoryInterface;

class EntityRelCount implements ModelEntitySaveInterface, NeedsRepositoryInterface
{

    /**
     * @var RepositoryInterface 
     */
    protected $_repository;
    
    protected $_references;

    public function setRepository(RepositoryInterface $repository)
    {
        return $this->_repository = $repository;
    }
    
    public function addReference($name, $rel)
    {
        $this->_references[] = (object)['name' => $name, 'rel' => $rel];
        return $this;
    }

    public function savePre(array &$entity, Model $model, array &$context)
    {
        if (!array_key_exists('rel', $entity)) {
            return;
        }
        
        // @TODO
//            foreach ($this->_relationCount as $ref) { // relation count
//                $model->{"entity_count_entity_{$ref}"} = 0;
//
//                if (isset($entity['contentRelation'][$ref])) {
//                    $model->{"entity_count_entity_{$ref}"} = count($entity['contentRelation'][$ref]);
//                }
//            }
        
    }
    
    public function savePost(array $entity, Model $model, array $context)
    {
    }
}
