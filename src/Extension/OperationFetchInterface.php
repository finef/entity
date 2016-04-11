<?php

namespace \Fine\Entity\Extension;

interface OperationFetchInterface
{

    public function fetchPre(&$param, &$context);
    
    public function fetchPost($param, &$entity, $context);
    
}
