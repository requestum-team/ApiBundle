<?php

namespace Requestum\ApiBundle\Event;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;

class FormActionEvent extends EntityActionEvent
{

    /**
     * @var Form
     */
    protected $form;

    /**
     * FormActionEvent constructor.
     * @param $subject
     * @param $request
     * @param $form
     * @param $doctrineRegistry
     */
    public function __construct(Request $request, $subject, Form $form, Registry $doctrineRegistry)
    {
        parent::__construct($request, $subject, $doctrineRegistry);

        $this->form = $form;
    }

    /**
     * @param Form $form
     * @return $this
     */
    public function setForm($form)
    {
        $this->form = $form;

        return $this;
    }

    /**
     * @return Form
     */
    public function getForm()
    {
        return $this->form;
    }
}