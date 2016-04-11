<?php

namespace \Fine\Entity\Extension;

use \Fine\Db\Model;

interface ModelEntityRemoveInterface
{
    
    public function removePre(array &$entity, Model $model, array &$context);
    
    public function removePost(array $entity, Model $model, array $context);
        
}