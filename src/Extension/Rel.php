<?php

namespace \Fine\Entity\Extension;


class Rel implements NeedsRepositoryInterface, SubentityInterface
{
    
    /**
     * @var RepositoryInterface 
     */
    protected $_repository = false;
    protected $_listing = false;
    
    public function setRepository(RepositoryInterface $repository) 
    {
        $this->_repository = $repository;
        return $this;
    }
    

    public function setListing($listing) 
    {
        $this->_listing = $listing;
        return $this;
    }

    public function fetcher($fetcher, &$param);
    
    public function tuple2entity(array &$tuple, $param);
    
    public function tuples2entities(array &$tuples, $parm)
    {
        if ($this->_listing === false) {
            return;
        }
    }
    
    public function save(array &$entity)
    {
        /** @TODO */
    }
    
    public function remove(array &$entity)
    {
        $this->_repository->getRepositoryContainer()->getDb()->rel->delete(['rel_id_entity_master' => $entity['entity_id']]);
    }
    
}
