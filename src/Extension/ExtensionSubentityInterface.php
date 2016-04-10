<?php

namespace \Fine\Entity\Extension;

interface SubentityInterface
{

    public function fetcher($fetcher, &$param);
    
    public function tuple2entity(array &$tuple, $param);
    
    public function tuples2entities(array &$tuples, $parm);
    
    public function save(array &$entity);
    
    public function remove(array &$entity);
    
}
    