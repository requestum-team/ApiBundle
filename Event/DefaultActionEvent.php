<?php

namespace Requestum\ApiBundle\Event;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\HttpFoundation\Request;

class DefaultActionEvent extends Event
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
     * @var Registry
     */
    protected $doctrineRegistry;

    /**
     * DefaultActionEvent constructor.
     * @param Request $request
     * @param $subject
     * @param Registry $doctrineRegistry
     */
    public function __construct(Request $request, $subject, Registry $doctrineRegistry)
    {
        $this->subject = $subject;
        $this->request = $request;
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