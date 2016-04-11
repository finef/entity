<?php

namespace \Fine\Entity\Extension;

interface OperationRemoveInterface
{

    public function removePre(&$entity, $entityPre, &$context);
    
    public function removePost($entity, $entityPre, $context);
    
}
