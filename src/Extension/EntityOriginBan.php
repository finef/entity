<?php

namespace \Fine\Entity\Extension;

use \Fine\Entity\Repository\RepositoryInterface;

class EntityOriginBan implements ModelEntityRemoveInterface, NeedsRepositoryInterface
{
    
    /**
     * @var RepositoryInterface 
     */
    protected $_repository;
    
    public function setRepository(RepositoryInterface $repository)
    {
        return $this->_repository = $repository;
    }

    public function removePre(array &$entity, Model $model, array &$context)
    {
        if (strlen($content->entity_origin) === 0) {
            return;
        }
        
        $this->_repository->getRepositoryContainer()->getDb()->entityOriginBan->insert([
            'entityOriginBan_origin' => $model->entity_origin, 
            'entityOriginBan_insert' => time()
        ]);
    }
    
    public function removePost(array $entity, Model $model, array $context)
    {
    }
    
}
