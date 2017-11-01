<?php

namespace Requestum\ApiBundle\Filter\Exception;

/**
 * Class BadFilterException
 */
class BadFilterException extends \RuntimeException
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $value;

    /**
     * BadFilterException constructor.
     *
     * @param string $name
     * @param int    $value
     * @param string $message
     */
    public function __construct($name, $value, $message = '')
    {
        $this->name = $name;
        $this->value = $value;

        parent::__construct($message);
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }
}
