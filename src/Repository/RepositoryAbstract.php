<?php

/** @todo dvc, search */

namespace Entity\Repository;


abstract class RepositoryAbstract
{

    protected $_subentity = array();

    protected $_subentityListing = array();

    /**
     * Construct
     *
     * @param array $config
     */
    public function __construct(array $config = array())
    {
        foreach ($config as $k => $v) {
            $this->{$k}($v);
        }
    }

    public function fetcher(array $param = array())
    {
        $fetcher = app()->mod->app->db->entity;

        // subcontent
        $this->_subentityCall('fetcher', array($fetcher, $param));

        // concrete repository logic
        $this->_fetcher($fetcher, $param);

        // set params
        $fetcher->param($param);

        return $fetcher;
    }

    public function is(array $param = array())
    {
        return (boolean) $this->count($param);
    }

    public function count(array $param = array())
    {
        return $this->fetcher($param)->fetchCount();
    }

    public function fetch(array $param = array())
    {
        $param['limit'] = '1';

        // operation pre
        $operation = $this->_isOperation($param);
        if ($operation) {
            $this->_subentityCall('operationFetchPre', $param);
        }

        // fetch
        $tuple = (array)$this->fetcher($param)->fetch();
        if (!$tuple) {
            return null;
        }
        $entity = $this->tuple2entity($tuple);

        // operation post
        if ($operation) {
            $this->_subentityCall('operationFetchPre', $param, $entity);
        }

        return $entity;
    }

    public function fetchAll(array $param = array())
    {
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
            $this->paging($fetcher, $param, $paging);
        }

        // fetch
        return $this->tuples2entities($fetcher->fetchAll());
    }

    public function save(array $entity)
    {
        // operation pre
        $operation = $this->_isOperation($entity);
        $entityPre = null;
        if ($operation) {
            if (array_key_exists('entity_id', $entity)) {
                $entityPre = $this->fetch(array('entity_id' => $entity['entity_id'], 'operation' => false));
            }
            else if (array_key_exists('entity_origin', $entity)) {
                $entityPre = $this->fetch(array('entity_origin' => $entity['entity_origin'], 'operation' => false));
            }
            $this->_subentityCall('operationSavePre', array($entity, $entityPre));
        }

        // save main record
        $this->_saveEntity($entity);

        if (!$entity['entity_id']) {
            return false;
        }

        // save subentities
        $this->_subentityCall('save', array($entity));

        // concrete repository logic
        $this->_save($entity);

        // operation post
        if ($operation) {
            $entityPost = $this->fetch(array('entity_id' => $entity['entity_id'], 'operation' => false));
            $this->_subentityCall('operationSavePost', [$entity, $entityPre, $entityPost]);
        }

        return $entity['entity_id'];
    }

    public function remove(array $entity)
    {
        if (!array_key_exists('entity_id', $entity) || !array_key_exists('entity_origin', $entity)) {
            throw new \LogicException();
        }

        // operation pre
        $operation = $this->_isOperation($entity);
        $entityPre = null;
        if ($operation) {
            if (array_key_exists('entity_id', $entity)) {
                $entityPre = $this->fetch(array('entity_id' => $entity['entity_id'], 'operation' => false));
            }
            else if (array_key_exists('entity_origin', $entity)) {
                $entityPre = $this->fetch(array('entity_origin' => $entity['entity_origin'], 'operation' => false));
            }
            $this->_subentityCall('operationRemovePre', array($entity, $entityPre));
        }

        // remove main record
        $this->_removeEntity($entity);

        // remove subentities
        $this->_subentityCall('remove', array($entity, $entityPre));

        // concrete repository logic
        $this->_remove($entity);

        // operation post
        if ($operation) {
            $this->_subentityCall('operationRemovePost', [$entity, $entityPre]);
        }
    }

    public function getSubentities()
    {
        return $this->_subentity;
    }

    public function hasSubentity($subcontent)
    {
        return in_array($subcontent, $this->_subcontent);
    }

    public function tuple2entity(array $tuple)
    {
        // subentity
        $this->_subentityCall('tuple2entity', array($tuple));

        // concrete repository logic
        $this->_tuple2entity($tuple);

        return $tuple;
    }

    public function tuples2entities(array $tuples)
    {
        // subentity
        $this->_subentityCall('tuples2entities', array($tuples));

        // concrete repository logic
        $this->_tuple2entity($tuples);

        return $tuples;
    }

    public function paging($fetcher, array $param, $paging)
    {

        // detach group and order
        if (($group = $content->getParam('group'))) {
            $fetcher->removeParam('group');
        }
        if (($order = $content->getParam('order'))) {
            $fetcher->removeParam('order');
        }

        // paginate
        $paging
            ->all($fetcher->fetchCount(null,  'DISTINCT ' . ($group ? $group : $fetcher->getKey())))
            ->paging();

        // attach group and order
        if ($group) {
            $fetcher->setParam('group', $group);
        }
        if ($order) {
            $fetcher->setParam('groorderup', $order);
        }

        // set paging
        $fetcher->setParam('paging', $paging);

    }

    protected function _saveContent(array &$entity)
    {
        $content = new m_content();

        if (isset($entity['content_id'])) { // select by content_id
            $content->select($entity['content_id']);
            if (!$content->id()) {
                return false;
            }
            $entity['content_id'] = $content->id();
        }
        else if (isset($entity['content_origin'])) { // select by content_origin
            $content->select(array('content_origin' => $entity['content_origin']));
            $content->content_origin = $entity['content_origin'];
            if ($content->id()) {
                $entity['content_id'] = $content->id();
            }
        }

        // time update
        $content->content_update = time();

        // flags, meta info

        if (isset($entity['content_context'])) {
            $content->content_context = $entity['content_context'];
        }

        if (isset($entity['content_inbox'])) {
            $content->content_inbox = $entity['content_inbox'];
        }

        if (isset($entity['content_public'])) {
            $content->content_public = $entity['content_public'];
        }

        if (isset($entity['content_order'])) {
            $content->content_order = $entity['content_order'];
        }

        if (isset($entity['content_list'])) {
            $content->content_list = $entity['content_list'];
        }

        if (isset($entity['content_published'])) {
            $content->content_published = $entity['content_published'];
        }

        if (isset($entity['content_modified'])) {
            $content->content_modified = $entity['content_modified'];
        }

        if (isset($entity['content_importchecksum'])) {
            $content->content_importchecksum = $entity['content_importchecksum'];
        }

        if (isset($entity['content_canonicaluri'])) {
            $content->content_canonicaluri = $entity['content_canonicaluri'];
        }

        // foreign ids

        if (isset($entity['content_id_content_user'])) {
            $content->content_id_content_user = $entity['content_id_content_user'];
        }

        // index words
        if (isset($entity['content_search_checksum'])) {
            $content->content_search_update = $content->content_update;
            $content->content_search_checksum = $entity['content_search_checksum'];
        }

        // relation ref & count
        if (isset($entity['contentRelation'])) {

            foreach ($this->_relationRef as $ref) { // relation ref

                if (isset($entity['contentRelation'][$ref])) {
                    reset($entity['contentRelation'][$ref]);
                    $rel = current($entity['contentRelation'][$ref]);
                    if (is_array($rel) && isset($rel['content_id'])) {
                        $content->{"content_id_content_{$ref}"} = $rel['content_id'];
                    }
                    else if (is_array($rel) && isset($rel['content_origin'])) {
                        $content->{"content_id_content_{$ref}"} = m_content::_()->field('content_id')->fetchVal(array('content_origin' => $rel['content_origin']));
                    }
                    else if (ctype_digit($rel)) {
                        $content->{"content_id_content_{$ref}"} = $rel;
                    }
                    else if (is_string($rel)) {
                        $content->{"content_id_content_{$ref}"} = m_content::_()->field('content_id')->fetchVal(array('content_origin' => $rel));
                    }
                    else {
                        $content->{"content_id_content_{$ref}"} = '0';
                    }
                }
            }

            foreach ($this->_relationCount as $ref) { // relation count
                $content->{"content_count_content_{$ref}"} = 0;

                if (isset($entity['contentRelation'][$ref])) {
                    $content->{"content_count_content_{$ref}"} = count($entity['contentRelation'][$ref]);
                }
            }
        }

        if (!$content->id()) { // insert

            $content->content_type   = $this->module()->getType();
            $content->content_insert = $content->content_update;

            // default values

            if (!isset($entity['content_order'])) {
                $content->content_order = $content->content_update;
            }

            if (!isset($entity['content_public'])) {
                $content->content_public = 'yes';
            }

            if (!isset($entity['content_list'])) {
                $content->content_list = 'yes';
            }

            $content->save();
            $content->selectInserted();

            $entity['content_id'] = $content->id();
            $entity['_new']       = true;
        }
        else { // update

            $content->save();
        }
    }

    protected function _removeContent(array &$entity)
    {
        $content = new m_content();

        if (isset($entity['content_id'])) { // select by content_id
            $content->select($entity['content_id']);
        }
        else if (isset($entity['content_origin'])) { // select by content_origin
            $content->select(array('content_origin' => $entity['content_origin']));
        }

        if (!$content->id()) {
            return false;
        }

        if (strlen($content->content_origin)) {
            m_contentOriginBan::_()->insert(array('contentOriginBan_origin' => $content->content_origin, 'contentOriginBan_insert' => time()));
        }

        $content->delete();
    }

    protected function _save(array &$entity)
    {
        $model = app()->mod->app->db->{$this->_type};
        $model->select($entity['content_id']);
        $model->setVal($entity);

        $model->id()
            ? $model->save()
            : $model->insert(array("{$this->_type}_id" => $entity['content_id']) + $model->val());
    }

    protected function _remove(array &$entity)
    {
        app()->mod->app->db->{$this->_type}->delete($entity['content_id']);
    }

    protected function _fetcher(m_content $fetcher, &$param)
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

    protected function _subentityCall($method, $args)
    {
        foreach ($this->_subentity as $subentity) {
            if (!method_exists($subentity, $method)) {
                continue;
            }
            $subentity->{$method}($args);
        }
    }

}
