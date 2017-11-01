<?php

namespace Requestum\ApiBundle\Security\Authorization;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Class AbstractEntityVoter
 */
abstract class AbstractEntityVoter extends Voter
{
    /**
     * @var string
     */
    protected $attributes;

    /**
     * @var string
     */
    protected $entityClass;

    /**
     * @var bool
     */
    protected $userRequired;

    /**
     * AbstractEntityVoter constructor.
     * @param string $attributes
     * @param string $entityClass
     * @param bool $userRequired
     */
    public function __construct($attributes, $entityClass, $userRequired = true)
    {
        $this->attributes = (array) $attributes;
        $this->entityClass = $entityClass;
        $this->userRequired = $userRequired;
    }


    /**
     * {@inheritdoc}
     */
    protected function supports($attribute, $subject)
    {
        if (!$this->attributes || !$this->entityClass) {
            throw new \LogicException('$attribute and $class properties should be defined');
        }

        return is_object($subject) && in_array($attribute, $this->attributes) && is_a($subject, $this->entityClass);
    }

    /**
     * {@inheritdoc}
     */
    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        $user = $token->getUser();
        $user = is_object($user) ? $user : null;

        if (!$user && $this->userRequired) {
            return false;
        }

        return $this->voteOnEntity($attribute, $subject, $user);
    }

    /**
     * @param string $attribute
     * @param object $entity
     * @param UserInterface|null $user
     */
    abstract protected function voteOnEntity($attribute, $entity, UserInterface $user = null);
}