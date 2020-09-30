<?php

namespace Requestum\ApiBundle\Tests\Util;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\FormError;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\ConstraintViolation;

use Requestum\ApiBundle\Util\ErrorFactory;

class ErrorFactoryTest extends TestCase
{
    /**
     * @var ErrorFactory
     */
    protected $errorFactory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->errorFactory = new ErrorFactory();
    }

    public function testFormatFormError()
    {
        $constraint = new NotBlank();
        $violation = new ConstraintViolation('', '', [], null, '', null, null, NotBlank::IS_BLANK_ERROR, $constraint);
        $error = new FormError('translated', 'template', [], null, $violation);

        $expected = [
            'error' => 'error.constraint.is_blank_error',
            'description' => 'translated',
        ];

        static::assertEquals($expected, $this->errorFactory->formatError($error));
    }

    public function testFormatFormErrorNoCode()
    {
        $constraint = new NotBlank();
        $violation = new ConstraintViolation('', '', [], null, '', null, null, null, $constraint);
        $error = new FormError('translated', 'template', [], null, $violation);

        $expected = [
            'error' => 'error.constraint.not_blank',
            'description' => 'translated',
        ];

        static::assertEquals($expected, $this->errorFactory->formatError($error));
    }

    public function testFormatFormConstraintViolation()
    {
        $constraint = new NotBlank();
        $error = new ConstraintViolation('message', '', [], null, '', null, null, NotBlank::IS_BLANK_ERROR, $constraint);

        $expected = [
            'error' => 'error.constraint.is_blank_error',
            'description' => 'message',
        ];

        static::assertEquals($expected, $this->errorFactory->formatError($error));
    }

    public function testFormatFormErrorStringParameters()
    {
        $expected = [
            'error' => 'error.constraint.is_blank_error',
            'description' => 'translated',
        ];

        static::assertEquals($expected, $this->errorFactory->formatError('error.constraint.is_blank_error', 'translated'));
    }
}
