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
use Symfony\Component\Workflow\Workflow;
use Symfony\Component\Workflow\Transition;
use Symfony\Component\Workflow\TransitionBlockerList;
use Symfony\Component\Workflow\TransitionBlocker;
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

            if ($blockerList->count()) {
                $errors = [];
                foreach ($blockerList as $transitionBlocker) {
                    $errors[] = $this->getTransitionBlockerError(
                        $workflow,
                        $entity,
                        $transitionName,
                        $transitionBlocker
                    );
                }

                throw new FormValidationException($errors, '[transition]');
            }

            $this->denyAccessUnlessGranted('transition.'.$transitionName, $entity);
            $workflow->apply($entity, $transitionName);

            return UpdateAction::beforeSave($request, $entity, $form);
        } catch (TransitionException $exception) {
            throw new FormValidationException(
                $this->getAbstractError($workflow, $entity, $transitionName),
                '[transition]'
            );
        }
    }

    /**
     * @param Workflow $workflow
     * @param $entity
     * @param $transitionName
     *
     * @return FormError
     */
    protected function getAbstractError(Workflow $workflow, $entity, $transitionName)
    {
        $possibleTransitionNames = array_map(function (Transition $value) {
            return $value->getName();
        }, $workflow->getEnabledTransitions($entity));

        $messageTemplate = 'Wrong transition "{{transition}}". Possible transitions: ["{{possible_transitions}}"]';
        $messageParams = [
            '{{transition}}' => $transitionName,
            '{{possible_transitions}}' => implode('" ,"', $possibleTransitionNames),
        ];

        return new FormError(
            $this->get('translator')->trans($messageTemplate, $messageParams),
            $messageTemplate,
            $messageParams,
            null,
            'error.workflow.wrong_transition'
        );
    }

    /**
     * @param Workflow $workflow
     * @param $entity
     * @param $transitionName
     * @param TransitionBlocker $blocker
     *
     * @return FormError
     */
    protected function getTransitionBlockerError(
        Workflow $workflow,
        $entity,
        $transitionName,
        TransitionBlocker $blocker
    ) {
        switch ($code = $blocker->getCode()) {
            case TransitionBlocker::BLOCKED_BY_EXPRESSION_GUARD_LISTENER:
            case TransitionBlocker::BLOCKED_BY_MARKING:
            case TransitionBlocker::UNKNOWN:
                return $this->getAbstractError($workflow, $entity, $transitionName);
            default:
                return new FormError(
                    $blocker->getMessage(),
                    null,
                    [],
                    null,
                    $blocker
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
