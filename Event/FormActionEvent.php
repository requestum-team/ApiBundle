<?php

namespace Requestum\ApiBundle\Event;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;

class FormActionEvent extends Event
{
    /**
     * @var Request
     */
    protected $request;

    /**
     * @var
     */
    protected $subject;

    /**
     * @var Form
     */
    protected $form;

    /**
     * @var Registry
     */
    protected $doctrineRegistry;

    /**
     * FormActionEvent constructor.
     * @param $subject
     * @param $request
     * @param $form
     * @param $doctrineRegistry
     */
    public function __construct(Request $request, $subject, Form $form, Registry $doctrineRegistry)
    {
        $this->subject = $subject;
        $this->request = $request;
        $this->form = $form;
        $this->doctrineRegistry = $doctrineRegistry;
    }

    /**
     * @param Request $request
     * @return $this
     */
    public function setRequest($request)
    {
        $this->request = $request;
        
        return $this;
    }

    /**
     * @return Request
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * @param $subject
     * @return $this
     */
    public function setSubject($subject)
    {
        $this->subject = $subject;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getSubject()
    {
        return $this->subject;
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

    /**
     * @param Registry $doctrineRegistry
     * @return $this
     */
    public function setDoctrineRegistry($doctrineRegistry)
    {
        $this->doctrineRegistry = $doctrineRegistry;

        return $this;
    }

    /**
     * @return Registry
     */
    public function getDoctrineRegistry()
    {
        return $this->doctrineRegistry;
    }
}