<?php

namespace Requestum\ApiBundle\Workflow;

use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\Workflow\TransitionBlocker;
use Symfony\Component\Workflow\TransitionBlockerList;

use Symfony\Component\Form\FormError;

/**
 * Class TransitionBlockerErrorTransformer
 *
 * @package Requestum\ApiBundle\Workflow
 */
class TransitionBlockerErrorTransformer
{
    /**
     * @var TranslatorInterface
     */
    protected $translator;

    /**
     * TransitionBlockerBuilder constructor
     * .
     * @param TranslatorInterface $translator
     */
    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    /**
     * @param TransitionBlockerList $blockerList
     *
     * @return null|FormError[]
     */
    public function transform(TransitionBlockerList $blockerList)
    {
        if ($blockerList->count() === 0) {
            return null;
        }

        $errors = [];
        foreach ($blockerList as $transitionBlocker) {
            $errors[] = $this->getTransitionBlockerError(
                $transitionBlocker
            );
        }

        return $errors;
    }

    /**
     * @param TransitionBlocker $blocker
     *
     * @return FormError
     */
    protected function getTransitionBlockerError(TransitionBlocker $blocker)
    {
        switch ($code = $blocker->getCode()) {
            case TransitionBlocker::BLOCKED_BY_EXPRESSION_GUARD_LISTENER:
            case TransitionBlocker::BLOCKED_BY_MARKING:
            case TransitionBlocker::UNKNOWN:
                return new FormError(
                    $this->translator->trans('The transition was blocked by some reason'),
                    null,
                    [],
                    null,
                    'error.workflow.blocked_transition'
                );
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
}
