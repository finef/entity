<?php

namespace \Fine\Entity\Repository;

interface RepositoryInterface
{
    
    public function setRepositoryContainer($container);

    public function getRepositoryContainer();

    public function setName($name);

    public function getName();

    public function fetcher(array $param = array());
    
    public function is(array $param = array());
    
    public function count(array $param = array());
    
    public function fetch(array $param);
    
    public function fetchAll(array $param = array()); 
    
    public function save(array $entity);
    
    public function remove(array $entity); 
    
}
