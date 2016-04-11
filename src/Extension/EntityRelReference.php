<?php

namespace \Fine\Entity\Extension;

use \Fine\Entity\Repository\RepositoryInterface;

class EntityRelReference implements ModelEntitySaveInterface, NeedsRepositoryInterface
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
    
    public function addReference($name, $rel, $index = 0)
    {
        $this->_references[] = (object)['name' => $name, 'rel' => $rel, 'index' => $index];
        return $this;
    }

    public function savePre(array &$entity, Model $model, array &$context)
    {
        if (!array_key_exists('rel', $entity)) {
            return;
        }
        
        // @TODO
//            foreach ($this->_relationRef as $ref) { // relation ref
//
//                if (isset($entity['contentRelation'][$ref])) {
//                    reset($entity['contentRelation'][$ref]);
//                    $rel = current($entity['contentRelation'][$ref]);
//                    if (is_array($rel) && isset($rel['entity_id'])) {
//                        $model->{"entity_id_entity_{$ref}"} = $rel['entity_id'];
//                    }
//                    else if (is_array($rel) && isset($rel['entity_origin'])) {
//                        $model->{"entity_id_entity_{$ref}"} = m_content::_()->field('entity_id')->fetchVal(array('entity_origin' => $rel['entity_origin']));
//                    }
//                    else if (ctype_digit($rel)) {
//                        $model->{"entity_id_entity_{$ref}"} = $rel;
//                    }
//                    else if (is_string($rel)) {
//                        $model->{"entity_id_entity_{$ref}"} = m_content::_()->field('entity_id')->fetchVal(array('entity_origin' => $rel));
//                    }
//                    else {
//                        $model->{"entity_id_entity_{$ref}"} = '0';
//                    }
//                }
//            }
        
    }
    
    public function savePost(array $entity, Model $model, array $context)
    {
    }

}
