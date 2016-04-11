<?php

namespace \Fine\Entity\Extension;

use \Fine\Entity\Repository\RepositoryInterface;

interface NeedsRepositoryInterface
{

    public function setRepository(RepositoryInterface $repository);
    
}
    