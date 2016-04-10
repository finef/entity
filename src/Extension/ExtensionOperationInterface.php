<?php

namespace \Fine\Entity\Extension;

interface OperationInterface
{

    public function savePre($fetcher, &$param);
    
    public function tuple2entity(array &$tuple, $param);
    
    public function tuples2entities(array &$tuples, $parm);
    
    public function save(array &$entity);
    
    public function remove(array &$entity);
    
}
    