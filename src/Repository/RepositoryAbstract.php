<?php

/** @todo dvc, search */

namespace Entity\Repository;

use \Fine\Entity\Extension\NeedsRepositoryInterface;
use \Fine\Entity\Extension\ModelEntityRemoveInterface;
use \Fine\Entity\Extension\ModelEntitySaveInterface;
use \Fine\Entity\Extension\OperationFetchInterface;
use \Fine\Entity\Extension\OperationSaveInterface;
use \Fine\Entity\Extension\OperationRemoveInterface;
use \Fine\Entity\Extension\SubentityInterface;

abstract class RepositoryAbstract
{

    protected $_type;
    protected $_extensions = [];
    protected $_repositoryContainer;

    public function setType($type) 
    {
        $this->_type = $type;
        return $this;
    }

    public function getType() 
    {
        return $this->_type;
    }
    
    public function addExtension($extension)
    {
        if ($extension instanceof NeedsRepositoryInterface) {
            $extension->setRepository($this);
        }
        return $this;
    }
    
    public function addExtensions(array $extensions)
    {
        foreach ($extensions as $extensions) {
            $this->addExtension($extension);
        }
        return $this;
    }
    
    public function setExtensions($extensions) 
    {
        $this->removeExtensions();
        $this->addExtensions($extensions);
        return $this;
    }

    public function getExtensions() 
    {
        return $this->_extensions;
    }
    
    public function removeExtensions()
    {
        $this->_extensions = [];
        return $this;
    }

    public function setRepositoryContainer($repositoryContainer) 
    {
        $this->_repositoryContainer = $repositoryContainer;
        return $this;
    }

    public function getRepositoryContainer() 
    {
        return $this->_repositoryContainer;
    }

    public function fetcher(array $param = [])
    {
        $fetcher = $this->getRepositoryContainer()->getDb()->entity;
        $this->_extension(SubentityInterface, 'fetcher', array($fetcher, $param));
        $this->_fetcher($fetcher, $param);
        $fetcher->param($param);
        return $fetcher;
    }

    public function is(array $param = [])
    {
        return (boolean) $this->count($param);
    }

    public function count(array $param = [])
    {
        return $this->fetcher($param)->fetchCount();
    }

    public function fetch(array $param = [])
    {
        $context = $param;
        $param['limit'] = '1';

        // operation pre
        $operation = $this->_isOperation($param);
        if ($operation) {
            $this->_extension(OperationFetchInterface, 'fetchPre', [$param, $context]);
        }

        // fetch
        $tuple = (array)$this->fetcher($param)->fetch();
        if (!$tuple) {
            return null;
        }
        $entity = $this->tuple2entity($tuple, $context);

        // operation post
        if ($operation) {
            $this->_extension(OperationFetchInterface, 'fetchPost', [$entity, $context]);
        }

        return $entity;
    }

    public function fetchAll(array $param = [])
    {
        $context = $param;
        
        // is paging?
        $paging = null;
        if (isset($param['paging'])) {
            $paging = $param['paging'];
            unset($param['paging']);
        }

        // fetcher
        $fetcher = $this->fetcher($param);

        // paginate
        if ($paging) {
            $this->paging($fetcher, $paging);
        }

        // fetch
        return $this->tuples2entities($fetcher->fetchAll(), $pa);
    }

    public function save(array $entity)
    {
        $context = $entity;
        
        // operation pre
        $operation = $this->_isOperation($entity);
        $entityPre = null;
        if ($operation) {
            if (array_key_exists('entity_id', $entity)) {
                $entityPre = $this->fetch(['entity_id' => $entity['entity_id'], 'operation' => true]);
            }
            else if (array_key_exists('entity_origin', $entity)) {
                $entityPre = $this->fetch(['entity_origin' => $entity['entity_origin'], 'operation' => true]);
            }
            $this->_extension(OperationSaveInterface, 'savePre', [$entity, $entityPre, $context]);
        }

        // save main record
        if (!$this->_saveEntity($entity)) {
            return false;
        }

        // save subentities
        $this->_extension(SubentityInterface, 'save', [$entity]);

        // concrete repository logic
        $this->_save($entity);

        // operation post
        if ($operation) {
            $entityPost = $this->fetch(['entity_id' => $entity['entity_id'], 'operation' => true]);
            $this->_extension(OperationSaveInterface, 'savePost', [$entity, $entityPre, $entityPost, $context]);
        }

        return $entity['entity_id'];
    }

    public function remove(array $entity)
    {
        if (
            !array_key_exists('entity_id', $entity) || !array_key_exists('entity_origin', $entity)
            || ctype_digit($entity['entity_id']) || is_string($entity['entity_origin'])
        ) {
            throw new \LogicException();
        }

        $context = $entity;
        
        // operation pre
        $operation = $this->_isOperation($entity);
        $entityPre = null;
        if ($operation) {
            if (array_key_exists('entity_id', $entity)) {
                $entityPre = $this->fetch(['entity_id' => $entity['entity_id'], 'operation' => true]);
            }
            else if (array_key_exists('entity_origin', $entity)) {
                $entityPre = $this->fetch(['entity_origin' => $entity['entity_origin'], 'operation' => true]);
            }
            $this->_extension(OperationRemoveInterface, 'removePre', [$entity, $entityPre, $context]);
        }

        // remove main record
        if (!$this->_removeEntity($entity)) {
            return false;
        }

        // remove subentities
        $this->_extension(SubentityInterface, 'remove', [$entity, $entityPre]);

        // concrete repository logic
        $this->_remove($entity);

        // operation post
        if ($operation) {
            $this->_extension(OperationRemoveInterface, 'removePost', [$entity, $entityPre, $context]);
        }
    }

    public function tuple2entity(array $tuple, array $context)
    {
        $this->_extension(SubentityInterface, 'tuple2entity', [$tuple, $context]);
        $this->_tuple2entity($tuple, $context);
        return $tuple;
    }

    public function tuples2entities(array $tuples, array $context)
    {
        $this->_extension(SubentityInterface, 'tuples2entities', [$tuples, $context]);
        $this->_tuples2entities($tuples, $context);
        return $tuples;
    }

    public function paging($fetcher, $paging)
    {
        // detach group and order
        if (($group = $entity->getParam(':group'))) {
            $fetcher->removeParam(':group');
        }
        if (($order = $entity->getParam(':order'))) {
            $fetcher->removeParam(':order');
        }

        // paginate
        $paging
            ->all($fetcher->fetchCount(null,  'DISTINCT ' . ($group ? $group : $fetcher->getKey())))
            ->paging();

        // attach group and order
        if ($group) {
            $fetcher->setParam(':group', $group);
        }
        if ($order) {
            $fetcher->setParam(':order', $order);
        }

        // set paging
        $fetcher->setParam(':paging', $paging);
    }

    protected function _saveEntity(array &$entity)
    {
        $context = $entity;
        $model = $this->getRepositoryContainer()->getDb()->entity;

        if (array_key_exists('entity_id', $entity)) { // select by entity_id
            $model->select($entity['entity_id']);
            if (!$model->getId()) {
                return false;
            }
            $entity['entity_id'] = $model->getId();
        }
        else if (array_key_exists('entity_origin', $entity)) { // select by entity_origin
            $model->select(['entity_origin' => $entity['entity_origin']]);
            $model->entity_origin = $entity['entity_origin'];
            if ($model->getId()) {
                $entity['entity_id'] = $model->getId();
            }
        }
        
        $this->_extension(ModelEntitySaveInterface, 'savePre', [$entity, $model, $context]);

        if (!$model->getId()) { // insert
            $model->entity_type = $this->module()->getType();
            $model->save();
            $model->selectInserted();
            $entity['entity_id'] = $model->getId();
        }
        else { // update
            $model->save();
        }
        
        $this->_extension(ModelEntitySaveInterface, 'savePost', [$entity, $model, $context]);
        
        return true;
    }

    protected function _removeEntity(array &$entity)
    {
        $context = $entity;
        $model = $this->getRepositoryContainer()->getDb()->entity;

        if (array_key_exists('entity_id', $entity)) { // select by entity_id
            $model->select($entity['entity_id']);
        }
        else if (array_key_exists('entity_origin', $entity)) { // select by entity_origin
            $model->select(array('entity_origin' => $entity['entity_origin']));
        }

        if (!$model->getId()) {
            return false;
        }

        $this->_extension(ModelEntityRemoveInterface, 'removePre', [$entity, $model, $context]);

        $model->delete();
        
        $this->_extension(ModelEntityRemoveInterface, 'removePost', [$entity, $model, $context]);
        
        return true;
    }

    protected function _save(array &$entity)
    {
        $model = $this->getRepositoryContainer()->getDb()->{$this->_type}->select($entity['entity_id'])->setVal($entity);

        $model->getId()
            ? $model->save()
            : $model->insert(["{$this->_type}_id" => $entity['entity_id']] + $model->val());
    }

    protected function _remove(array &$entity)
    {
        $model = $this->getRepositoryContainer()->getDb()->{$this->_type}->delete(["{$this->_type}_id" => $entity['entity_id']] );
    }

    protected function _fetcher(Model $fetcher, &$param)
    {
        $fetcher->join($this->_type);
    }

    protected function _isOperation(&$param)
    {
        $operation = true;
        if (array_key_exists('operation', $param)) {
            $operation = $param['operation'] === false ? false : true;
            unset($param['operation']);
        }
        return $operation;
    }

    protected function _extension($interface, $method, $args)
    {
        foreach ($this->_extensions as $extension) {
            if (!$extension instanceof $interface) {
                continue;
            }
            call_user_func_array([$subentity, $method], $args);
        }
    }

}
