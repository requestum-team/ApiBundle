<?php

namespace Requestum\ApiBundle\Action;

use Requestum\ApiBundle\Exception\Controller\FormValidationException;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Workflow\TransitionBlockerList;
use Symfony\Component\Workflow\Exception\TransitionException;

/**
 * Class TransitAction
 */
class TransitAction extends UpdateAction
{
    /**
     * TransitionController constructor.
     *
     * @param string             $entityClass
     * @param FormInterface|null $formTypeClass
     */
    public function __construct($entityClass, $formTypeClass = FormType::class)
    {
        parent::__construct($entityClass, $formTypeClass);
    }

    /**
     * {@inheritdoc}
     */
    public function beforeSave(Request $request, $entity, Form $form)
    {
        $transitionName = $form->get('transition')->getData();
        $workflow = $this->get('workflow.registry')->get($entity);

        try {
            /** @var TransitionBlockerList $blockerList */
            $blockerList = $workflow->buildTransitionBlockerList($entity, $transitionName);

            if (null !== ($errors = $this->get('core.workflow.transition_blocker_error_transformer')->transform($blockerList))) {
                throw new FormValidationException($errors, '[transition]');
            }

            $this->denyAccessUnlessGranted('transition.'.$transitionName, $entity);
            $workflow->apply($entity, $transitionName);

            return UpdateAction::beforeSave($request, $entity, $form);
        } catch (TransitionException $exception) {
            throw new FormValidationException(
                new FormError(
                    $this->get('translator')->trans('Something went wrong during applying of the transition'),
                    null,
                    [],
                    null,
                    'error.workflow.transition_exception'
                ),
                '[transition]'
            );
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function buildForm($entity, $options)
    {
        $form = parent::buildForm($entity, $options);

        $form->add('transition', ChoiceType::class, [
            'choices' => $this->options['transitions'],
            'mapped' => false,
        ]);

        return $form;
    }

    /**
     * {@inheritdoc}
     */
    protected function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults([
            'transitions' => [],
        ]);
    }

}
