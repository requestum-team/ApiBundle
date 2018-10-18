<?php

namespace Requestum\ApiBundle\Action;

use Requestum\ApiBundle\Filter\EntityContextData;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class ActionContextDecorator
 *
 * @package Requestum\ApiBundle\Action
 */
class ActionContextDecorator extends BaseAction
{
    private $decoratedAction;

    private $entityClass;

    /**
     * ActionContextDecorator constructor.
     *
     * @param EntityAction $decoratedAction
     */
    public function __construct(EntityAction $decoratedAction)
    {
        parent::__construct();

        $this->decoratedAction = $decoratedAction;

        $this->entityClass = $decoratedAction->getEntityClass();
    }

    /**
     * @param Request $request
     *
     * @return Response
     */
    public function executeAction(Request $request)
    {
        if (isset($this->options['context'])) {
            $contexts = $request->attributes->has('_contexts') ? $request->attributes->get('_contexts') : [];
            $currentContext = $this->getEntityContextData($request);
            if ($currentContext) {
                $contexts[$this->options['context']] = $currentContext;
            }
            $request->attributes->set('_contexts', $contexts);
        }

        return $this->decoratedAction->executeAction($request);
    }

    public function getRequestAttrs(Request $request)
    {
        $requestAttrs = [];
        if (empty($this->options['fetch_field_map'])) {
            if ($request->get($this->options['context'])) {
                return [$this->options['context'] => $request->get($this->options['context'])];
            }
        } else {
            foreach ($this->options['fetch_field_map'] as $request_attr => $entityField) {
                if ($request->get($request_attr)) {
                    $requestAttrs[$request_attr] = $request->get($request_attr);
                }
            }
        }

        return $requestAttrs;
    }

    public function getContextValuesFromRequest($requestAttrs, $contextKeys)
    {
        if (count($requestAttrs) === 1 && count($contextKeys) === 1) {
            return [$contextKeys[key($contextKeys)] => $requestAttrs[key($requestAttrs)]];
        }
        $contextValues = [];
        if (!empty($this->options['fetch_field_map'])) {
            foreach ($requestAttrs as $key => $value) {
                if (isset($this->options['fetch_field_map'][$key])) {
                    $contextValues[$this->options['fetch_field_map'][$key]] = $value;
                }
            }
        }

        foreach ($contextKeys as $key) {
            if (isset($contextValues[$key])) {
                continue;
            }
            if (!isset($requestAttrs[$key])) {
                throw new \Exception('Request attribute not set: ' . $key);
            }
            $contextValues[$key] = $requestAttrs[$key];
        }

        return $contextValues;
    }

    public function getEntityContextData(Request $request)
    {
        $requestAttrs = $this->getRequestAttrs($request);
        if (count($requestAttrs) > 0) {
            $meta = $this->getMetaContextByField($this->options['context']);

            $contextValues = $this->getContextValuesFromRequest($requestAttrs, $meta['fields']);

            $entity = $this->getDoctrine()->getRepository($meta['class'])->findOneBy($contextValues);

            if (!$entity) {
                throw new NotFoundHttpException();
            }

            $this->denyAccessUnlessGranted($this->options['access_attribute'], $entity);

            $entityContextData = new EntityContextData($this->options['context'], $entity, $contextValues);

            return $entityContextData;
        }

        return null;
    }

    /**
     * @return \Doctrine\ORM\EntityManager|object
     */
    protected function getEntityManager()
    {
        return $this->get('doctrine.orm.entity_manager');
    }

    /**
     * @param $field
     *
     * @return array
     */
    protected function getMetaContextByField($field)
    {
        $metaDataClass = $this->getEntityManager()->getClassMetadata($this->entityClass);

        if (!isset($metaDataClass->associationMappings[$field])) {
            throw new NotFoundHttpException('Context "' . $field . '" not found');
        }

        $meta = $metaDataClass->associationMappings[$field];

        return ['class' => $meta['targetEntity'], 'fields' => array_keys($meta['targetToSourceKeyColumns'])]; //todo: check whether keys are entity property names
    }

    protected function getFieldNameByColumnName($entityClass, $columnName)
    {
        $metaDataClass = $this->getEntityManager()->getClassMetadata($this->entityClass);

        if (!isset($metaDataClass->fieldNames[$columnName])) {
            throw new \ErrorException('Field "' . $columnName . '" is missing in the entity ' . $entityClass);
        }

        return $metaDataClass['fieldNames'][$columnName];
    }

    protected function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults([
            'context' => 'user',
            'fetch_field_map' => [],
            'access_attribute' => 'fetch', //todo: Maybe take access_attribute from $decoratedAction ?
        ]);
    }

    public function getEntityClass()
    {
        return $this->entityClass;
    }
}
