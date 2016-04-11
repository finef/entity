<?php

namespace \Fine\Entity\Extension;

interface OperationSaveInterface
{

    public function savePre(&$entity, $entityPre, &$context);
    
    public function savePost($entity, $entityPre, $entityPost, $context);
    
}
