<?php

namespace Requestum\ApiBundle\Pagination;

use Doctrine\ORM\QueryBuilder;
use Doctrine\MongoDB\Query\Builder;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Pagerfanta\Pagerfanta;
use Pagerfanta\Exception\InvalidArgumentException;

use Requestum\ApiBundle\Action\ListAction;
use Requestum\ApiBundle\Filter\Exception\BadFilterException;

class CursorPaginationListAction extends ListAction
{
    /**
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function executeAction(Request $request)
    {
        $filters = $this->getFilters($request);

        $page = $this->extractParam($filters, 'page', 1);
        $perPage = $this->extractParam($filters, 'per-page', $this->options['default_per_page']);
        $cursor = $this->extractParam($filters, 'cursor', null);

        $cursorObject = null;
        if ($cursor) {
            $cursorObject = PaginationCursor::createCursorObjectFromBaseCode($cursor);
            $page = 1;
        }

        $expandExpression = $this->extractParam($filters, 'expand', null);

        try {
            $entitiesQueryBuilder = $this->createQueryBuilder($filters);
            $entitiesQueryBuilder->addOrderBy($entitiesQueryBuilder->getRootAlias() . '.' . $this->options['fetch_field']);
        } catch (BadFilterException $exception) {
            $value = $exception->getValue();

            if (is_scalar($value)) {
                $message = sprintf('Unprocessable filter "%s" with value "%s".', $exception->getName(), $value);
            } else {
                $value = is_object($value) ? get_class($value) : gettype($value);
                $message = sprintf('Unprocessable filter "%s" of type "%s".', $exception->getName(), $value);
            }

            if ($exception->getMessage()) {
                $message = sprintf("%s %s.", $message, $exception->getMessage());
            }

            throw new BadRequestHttpException($message);
        }

        try {
            $result = $this->getPager($entitiesQueryBuilder, $perPage, $page, $cursorObject);
        } catch (InvalidArgumentException $exception) {
            throw new BadRequestHttpException();
        }

        if ($request->attributes->get('count-only')) {
            $result = ['total' => $result->getNbResults()];
        }

        return $this->handleResponse($result, Response::HTTP_OK);
    }

    /**
     * {@inheritdoc}
     */
    protected function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setNormalizer('filters', function (Options $options, $value) {
            $reservedFilters = [
                'page' => null,
                'per-page' => null,
                'expand' => null,
                'cursor' => null,
            ];
            $reservedOverrides = [];
            $result = [];

            foreach ($value as $filter) {
                list($key, $processor) = is_array($filter) ? $filter : [$filter, null];

                if (isset($reservedFilters[$key])) {
                    $reservedOverrides[] = $key;
                }

                $result[$key] = $processor;
            }

            if (count($reservedOverrides)) {
                throw new \InvalidArgumentException(sprintf('next filters are reserved by ListAction: ["%s"]', implode('", "', $reservedOverrides)));
            }

            return $result + $reservedFilters;
        });
    }

    /**
     * @param QueryBuilder|Builder $entitiesQueryBuilder
     * @param integer $perPage
     * @param integer $page
     * @param PaginationCursor|null $cursor
     *
     * @return Pagerfanta
     * @throws \Exception
     */
    protected function getPager($entitiesQueryBuilder, $perPage, $page, $cursor = null)
    {
        if ($cursor && !$cursor->checkCursorByQueryBuilder($entitiesQueryBuilder)) {
            throw new BadRequestHttpException('Bad cursor!');
        }

        switch ($this->options['entity_manager']) {
            case 'doctrine':
                $adapter = new CursorDoctrineORMAdapter($entitiesQueryBuilder, $this->options['pagerfanta_fetch_join_collection'], $this->options['pagerfanta_use_output_walkers'], $cursor);
                break;

            case 'doctrine_mongodb':
                throw new \Exception('Entity manager is not supported');
                break;

            default:
                throw new \Exception('Entity manager not declared');
                break;
        }

        $pager = new ApiPagination($entitiesQueryBuilder, $adapter);

        $pager
            ->setMaxPerPage($perPage)
            ->setCurrentPage($page);

        return $pager;
    }
}
