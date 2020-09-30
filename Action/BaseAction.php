<?php

namespace Requestum\ApiBundle\Action;

use Requestum\ApiBundle\Action\Extension\OptionsExtensionInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Serializer\Exception\CircularReferenceException;
use Symfony\Component\Serializer\Serializer;

/**
 * BaseController Class.
 */
abstract class BaseAction extends AbstractController implements ActionInterface, OptionsExtensionInterface
{
    /**
     * @var array
     */
    protected $options;

    /**
     * @var array
     */
    private $givenOptions = [];

    /**
     * @var OptionsResolver
     */
    private $optionResolver;

    /**
     * BaseAction constructor.
     */
    public function __construct()
    {
        $this->optionResolver = new OptionsResolver();
        $this->resolveOptions($this);
    }

    /**
     * @param OptionsExtensionInterface $extension
     */
    protected function resolveOptions(OptionsExtensionInterface $extension = null)
    {
        if (null !== $extension){
            $extension->setOptionDefaults($this->optionResolver);
        }
        $this->options = $this->optionResolver->resolve($this->givenOptions);
    }
    /**
     * @param $options
     */
    public function setOptions($options)
    {
        $this->givenOptions = array_replace_recursive($this->givenOptions, $options);
        $this->resolveOptions();
    }

    /**
     * @param mixed $data                 Data
     * @param array $serializationContext Context
     *
     * @return string
     */
    public function serialize($data, array $serializationContext = [])
    {
        /** @var Serializer $serializer */
        $serializer = $this->get('serializer');

        // no pass through context to decouple from concrete serializer implementation
        $context = [
            'expand' => $serializationContext['expand'],
            'groups' => isset($serializationContext['groups']) ? $serializationContext['groups'] : $this->options['serialization_groups'],
            'serialization_check_access' => isset($serializationContext['serialization_check_access']) ? $serializationContext['serialization_check_access'] : $this->options['serialization_check_access'],
        ];

        try {

            return $serializer->serialize($data, 'json', $context);
        } catch (CircularReferenceException $exception) {

            throw new BadRequestHttpException();
        }
    }

    public function setOptionDefaults(OptionsResolver $resolver)
    {
        $this->configureOptions($resolver);
    }

    /**
     * @param mixed $data
     * @param int   $status
     * @param array $serializationContext
     *
     * @return JsonResponse
     *
     * @throws \Exception
     */
    protected function handleResponse($data, $status = Response::HTTP_OK, array $serializationContext = [])
    {
        $request = $this->get('request_stack')->getCurrentRequest();
        $expandExpression = $request->query->get('expand') ? $request->query->get('expand') : null;
        $expand = $expandExpression ? explode(',', $expandExpression) : [];

        $serializationContext = $serializationContext + ['expand' => $expand];

        $body = null !== $data ? $this->serialize($data, (array) $serializationContext) : '';

        return new JsonResponse($body, $status, [], true);
    }

    /**
     * @param array  &$array
     * @param string $key
     * @param mixed  $default
     *
     * @return mixed
     */
    protected function extractParam(array &$array, $key, $default)
    {
        if (isset($array[$key])) {
            $result = $array[$key];
            unset($array[$key]);
        } else {
            $result = $default;
        }

        return $result;
    }

    /**
     * @param null   $subject
     * @param string $message
     */
    protected function checkAccess($subject = null,  $message = 'Access Denied.')
    {
        if ($accessAttr = $this->options['access_attribute']) {
            $this->denyAccessUnlessGranted($accessAttr, $subject, $message);
        }
    }

    /**
     * @param OptionsResolver $resolver
     */
    protected function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'serialization_groups' => ['default'],
            'access_attribute' => null,
            'serialization_check_access' => true,
        ]);
    }
}
