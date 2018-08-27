<?php

namespace Requestum\ApiBundle\Action;

use Requestum\ApiBundle\Action\Extension\ValidationExtensionInterface;
use Requestum\ApiBundle\Exception\Controller\FormValidationException;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Requestum\ApiBundle\Event\FormActionEvent;

/**
 * Class EntityController.
 */
abstract class AbstractFormAction extends EntityAction
{
    /**
     * @var string
     */
    protected $formTypeClass;

    /**
     * @var ValidationExtensionInterface[]
     */
    protected $validationExtensions;

    /**
     * @param string $entityClass     Entity class
     * @param string $formTypeClass   From class
     * @param string $beforeSaveEvent The name of event triggered before save
     * @param string $afterSaveEvent  The name of event triggered after save
     */
    public function __construct($entityClass, $formTypeClass)
    {
        parent::__construct($entityClass);

        $this->formTypeClass = $formTypeClass;
        $this->validationExtensions = [];
    }

    public function addValidationExtension(ValidationExtensionInterface $extension)
    {
        $this->validationExtensions[] = $extension;
    }

    /**
     * @param Request $request
     * @return object
     */
    abstract protected function provideEntity(Request $request);

    /**
     * {@inheritdoc}
     */
    public function executeAction(Request $request)
    {
        if ($this->options['use_lock']) {
            $this->get('doctrine.orm.default_entity_manager')->beginTransaction();
            $entity = $this->provideEntity($request);
            try {
                $response = $this->processEntity($request, $entity);
                $this->get('doctrine.orm.default_entity_manager')->commit();
            } catch (\Exception $exception) {
                $this->get('doctrine.orm.default_entity_manager')->rollback();

                throw $exception;
            }
        } else {
            $entity = $this->provideEntity($request);
            $response = $this->processEntity($request, $entity);
        }

        return $response;
    }

    /**
     * @param Request $request
     * @param object  $entity
     *
     * @return JsonResponse
     */
    public function processEntity(Request $request, $entity)
    {
        $form = $this->buildForm($entity, $this->getFormOptions($request));
        $form->handleRequest($request);

        if (!$form->isSubmitted()) {
            throw new BadRequestHttpException('Wrong request');
        }

        if ($form->isValid()) {

            $this->checkAccess($entity);

            try {
                $this->beforeSave($request, $entity, $form);
                $this->processSubmit($request, $entity, $form);
                $this->afterSave($request, $entity, $form);

                return $this->handleResponse($this->options['return_entity'] ? $entity : null, $this->options['success_status_code']);
            } catch (FormValidationException $exception) {
                foreach ($exception->getErrors() as $path => $errors) {
                    $targetForm = is_string($path) ? $this->get('property_accessor')->getValue($form, $path) : $form;
                    foreach ((array) $errors as $error) {
                        $targetForm->addError($error);
                    }
                }
            }
        }

        return $this->handleResponse($form, Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    /**
     * @param Request $request
     * @param FormInterface $form
     *
     * @return bool
     */
    protected function isValid(Request $request, FormInterface $form)
    {
        foreach ($this->validationExtensions as $extension) {
            $extension->validate($request, $this->get('validator'), $form);
        }

        return $form->isValid();
    }

    /**
     * @param mixed $entity
     *
     * @return Form
     */
    protected function buildForm($entity, $options)
    {
        return $this->get('form.factory')
            ->createNamedBuilder('', $this->formTypeClass, $entity, $options)
            ->getForm()
        ;
    }

    protected function getFormOptions(Request $request)
    {
        $options = [
            'method' => $this->options['http_method'],
        ];

        $options = $options + $this->options['form_options'];

        return $options;
    }

    /**
     * @param Request $request
     * @param mixed   $entity
     * @param Form    $form
     */
    protected function beforeSave(Request $request, $entity, Form $form)
    {
        foreach ($this->options['before_save_events'] + ['action.before_save'] as $event) {
            $this->get('event_dispatcher')->dispatch($event, new FormActionEvent($request, $entity, $form, $this->getDoctrine()));
        }
    }

    /**
     * @param Request $request
     * @param mixed   $entity
     * @param Form    $form
     */
    protected function afterSave(Request $request, $entity, Form $form)
    {
        foreach ($this->options['after_save_events'] + ['action.after_save'] as $event) {
            $this->get('event_dispatcher')->dispatch($event, new FormActionEvent($request, $entity, $form, $this->getDoctrine()));
        }
    }

    /**
     * @param Request $request
     * @param object  $entity
     * @param Form    $form
     *
     * @return void|mixed
     *
     * @throws \Exception
     */
    protected function processSubmit(Request $request, $entity, Form $form)
    {
        $em = $this->getDoctrine()->getManager();
        $em->persist($entity);
        $em->flush();
    }

    protected function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults([
            'use_lock' => false,
            'http_method' => Request::METHOD_POST,
            'success_status_code' => Response::HTTP_OK,
            'return_entity' => true,
            'form_options' => [],
            'before_save_events' => [],
            'after_save_events' => [],
        ]);
    }


}
