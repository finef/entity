<?php

namespace \Fine\Entity\Extension;

use \Fine\Db\Model;

interface ModelEntitySaveInterface
{
    
    public function savePre(array &$entity, Model $model, array &$context);
    
    public function savePost(array $entity, Model $model, array $context);

}