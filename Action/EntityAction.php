<?php

namespace Requestum\ApiBundle\Action;

use Doctrine\Common\Persistence\ObjectRepository;
use Doctrine\DBAL\LockMode;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\QueryBuilder;
use Requestum\ApiBundle\Action\Extension\FiltersExtensionInterface;
use Requestum\ApiBundle\Action\Extension\OptionExtensionInterface;
use Requestum\ApiBundle\Repository\FilterableRepositoryInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * Class EntityAction
 */
abstract class EntityAction extends BaseAction
{
    /**
     * @var string
     */
    protected $entityClass;

    /**
     * @var FiltersExtensionInterface[]
     */
    protected $filtersExtensions;

    /**
     * EntityAction constructor.
     *
     * @param string $entityClass
     */
    public function __construct($entityClass)
    {
        parent::__construct();

        $this->entityClass = $entityClass;
        $this->filtersExtensions = [];
    }

    /**
     * @param FiltersExtensionInterface $extension
     */
    public function addFiltersExtension(FiltersExtensionInterface $extension)
    {
        $this->filtersExtensions[] = $extension;
        if ($extension instanceof OptionExtensionInterface) {
            $this->resolveOptions($extension);
        }
    }

    /**
     * @param array $queryExtensions
     *
     * @param bool $append
     */
    public function setFiltersExtensions(array $queryExtensions, $append = false)
    {
        if (!$append) {
            $this->filtersExtensions = [];
        }

        foreach ($queryExtensions as $extension) {
            $this->addFiltersExtension($extension);
        }
    }

    /**
     * @param Request $request
     * @param string  $param
     * @param bool    $useLock
     *
     * @return mixed
     *
     * @throws NotFoundHttpException|BadRequestHttpException
     */
    protected function getEntity(Request $request, $param = null, $useLock = false)
    {
        $param = $param ?: $this->options['fetch_field'];

        if (!($value = $request->get($param))) {
            throw $this->createNotFoundException();
        }

        $query = $this->createQueryBuilder([$param => $value])->getQuery();

        if ($useLock) {
            $query->setLockMode(LockMode::PESSIMISTIC_WRITE);
        }

        try {
            return $query->getSingleResult();
        } catch (NoResultException $exception) {
            throw $this->createNotFoundException();
        }
    }

    /**
     * @param array $filters
     *
     * @return QueryBuilder
     *
     * @throws \InvalidArgumentException
     */
    protected function createQueryBuilder(array $filters = [])
    {
        $repository = $this->getDoctrine()->getRepository($this->entityClass);

        if  ($repository instanceof ContainerAwareInterface) {
            $repository->setContainer($this->container);
        }

        if (!$repository instanceof FilterableRepositoryInterface) {
            throw new \InvalidArgumentException(sprintf('Repository should implement %s', FilterableRepositoryInterface::class));
        }

        $this->processFilters($filters);

        return $repository->filter($filters, $this->createCustomFilterBuilder($repository));
    }

    /**
     * @param ObjectRepository $repository
     * @return null
     */
    protected function createCustomFilterBuilder(ObjectRepository $repository)
    {
        return null;
    }

    /**
     * @param $filters
     */
    protected function processFilters(&$filters)
    {
        $filters = $filters + $this->options['preset_filters'];

        $this->processPlaceholders($filters);

        foreach ($this->filtersExtensions as $extension) {
            $extension->extend($filters,$this->entityClass, $this->options);
        }
    }

    protected function processPlaceholders(&$filters)
    {
        foreach ($filters as $key => &$val) {
            if (is_array($val)) {
                $this->processFilters($val);
            } elseif ($val == '__USER__') {
                if (!is_object($user = $this->getUser())) {
                    throw new AccessDeniedException();
                }
                $filters[$key] = $user->getId();
            }

            continue;
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults([
            'fetch_field' => 'id',
            'preset_filters' => [],
        ]);
    }
}