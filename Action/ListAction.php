<?php

namespace Requestum\ApiBundle\Action;

use Doctrine\ORM\QueryBuilder;
use Requestum\ApiBundle\Filter\Exception\BadFilterException;
use Requestum\ApiBundle\Filter\Processor\FilterProcessorInterface;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Exception\InvalidArgumentException;
use Pagerfanta\Pagerfanta;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class ListAction.
 */
class ListAction extends EntityAction
{
    /**
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function executeAction(Request $request)
    {
        $filters = $this->getFilters($request);

        $page = $request->query->get( 'page', 1);
        $perPage = $request->query->get( 'per-page', $this->options['default_per_page']);

        try {
            $entitiesQueryBuilder = $this->createQueryBuilder($filters);
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
            $result = $this->getPager($entitiesQueryBuilder, $perPage, $page);
        } catch (InvalidArgumentException $exception) {
            throw new BadRequestHttpException($exception->getMessage());
        }

        if ($request->attributes->get('count-only')) {
            $result = ['total' => $result->getNbResults()];
        }

        return $this->handleResponse($result, Response::HTTP_OK);
    }

    /**
     * @param Request $request
     *
     * @return array
     */
    protected function getFilters(Request $request)
    {
        $filters = [];

        foreach ($request->query->all() as $key => $value) {
            if (in_array($key, $this->options['reserved_filters']) || !array_key_exists($key, $this->options['filters'])) {
                continue;
            }

            $filters[$key] = $this->processFilter($key, $value);
        }

        return $filters;
    }

    /**
     * @param $key
     * @param $value
     *
     * @return mixed
     */
    protected function processFilter($key, $value)
    {
        /** @var FilterProcessorInterface $processor */
        $processor = $this->options['filters'][$key];

        if ($processor) {
            return $processor->processFilter($value);
        }

        return $value;
    }

    /**
     * {@inheritdoc}
     */
    protected function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults([
            'default_per_page' => 20,
            'filters' => [],
            'pagerfanta_fetch_join_collection' => false,
            'pagerfanta_use_output_walkers' => null,
            'reserved_filters' => [
                'page',
                'per-page',
                'expand'
            ],
        ]);

        $resolver->setNormalizer('filters', function (Options $options, $value) {
            $result = [];

            $reservedFilters = $options->offsetGet('reserved_filters');
            $reservedOverrides = [];

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

            return $result;
        });
    }

    /**
     * @param QueryBuilder $entitiesQueryBuilder
     * @param integer      $perPage
     * @param integer      $page
     *
     * @return Pagerfanta
     */
    protected function getPager($entitiesQueryBuilder, $perPage, $page)
    {
        $adapter = new DoctrineORMAdapter($entitiesQueryBuilder, $this->options['pagerfanta_fetch_join_collection'], $this->options['pagerfanta_use_output_walkers']);

        $pager = new Pagerfanta($adapter);

        $pager
            ->setMaxPerPage($perPage)
            ->setCurrentPage($page);

        return $pager;
    }
}
