<?php

namespace Requestum\ApiBundle\Action;

use Doctrine\DBAL\Exception\ForeignKeyConstraintViolationException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class DeleteAction.
 */
class DeleteAction extends EntityAction
{
    /**
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function executeAction(Request $request)
    {
        $entity = $this->getEntity($request);
        $this->checkAccess($entity);

        try {
            $this->processDeletion($entity);
        } catch (ForeignKeyConstraintViolationException $e) {
            throw new BadRequestHttpException('Cannot delete entity due to constraint reference');
        }

        return $this->handleResponse(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * @param $entity
     */
    protected function processDeletion($entity)
    {
        $em = $this->getDoctrine()->getManager();
        $em->remove($entity);
        $em->flush();
    }

    /**
     * {@inheritdoc}
     */
    protected function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver
            ->setDefaults([
                'access_attribute' => 'delete',
            ])
        ;
    }
}
