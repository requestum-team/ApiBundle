<?php

namespace Requestum\ApiBundle\Action;

use Requestum\ApiBundle\Exception\Controller\FormValidationException;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Workflow\Transition;
use Symfony\Component\OptionsResolver\OptionsResolver;

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

        if (!$workflow->can($entity, $transitionName)) {
            $possibleTransitionNames = array_map(function (Transition $value) {
                return $value->getName();
            }, $workflow->getEnabledTransitions($entity));

            throw new FormValidationException(new FormError(
                null,
                'error.workflow.wrong_transition',
                [
                    '%impossible_transition%' => $transitionName,
                    '%possible_transitions%' => implode(' ,', $possibleTransitionNames),
                ]
            ), '[transition]');
        }

        $workflow->apply($entity, $transitionName);

        parent::beforeSave($request, $entity, $form);
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
