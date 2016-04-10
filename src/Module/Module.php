<?php

namespace Entity\Module;

use \Fine\Di;
use \Fine\Container;

class Module extends Container
{

    protected $_app;

    public function register($app)
    {
        $this->_app = $app;

        $app['repository'] = function() use ($app) {
            return $app->repository = $app->mod->entity->repository;
        };
    }

    protected function _mod()
    {
        return $this->mod = new Container(array('__invoke' => array(
            'app'    => '\Fine\Entity\Module\App',
            'entity' => '\Fine\Entity\Module\Entity',
        )));
    }

    protected function _repository()
    {
        $this->repository = new \Entity\Repository\RepositoryContainer();
        $this->reposiotry->setExtensionContainer($this->extension);
        $this->_app->mod->each()->entity->repository($this->repository);
        return $this->repository;
    }

    protected function _extension()
    {
        $this->extension = new \Fine\Container\Container();
        $this->_app->mod->each()->entity->extension($this->subentity);
        return $this->extension;
    }

}
