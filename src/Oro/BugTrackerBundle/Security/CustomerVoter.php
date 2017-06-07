<?php

namespace Oro\BugTrackerBundle\Security;

use Oro\BugTrackerBundle\Entity\Customer;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManager;

class CustomerVoter extends Voter
{
    // these strings are just invented: you can use anything
    const EDIT = 'edit_customer';
    const DELETE = 'delete_customer';

    /**
     * @var EntityManager
     */
    private $em;
    /**
     * @var AccessDecisionManager
     */
    private $decisionManager;

    public function __construct(EntityManager $entityManager, AccessDecisionManager $decisionManager)
    {
        $this->em = $entityManager;
        $this->decisionManager = $decisionManager;
    }

    protected function supports($attribute, $subject)
    {
        // if the attribute isn't one we support, return false
        if (!in_array($attribute, array(self::EDIT, self::DELETE))) {
            return false;
        }

        // only vote on Post objects inside this voter
        if (!$subject instanceof Customer) {
            return false;
        }

        return true;
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        $customer = $token->getUser();

        if (!$customer instanceof Customer) {
            // the user must be logged in; if not, deny access
            return false;
        }

        if (in_array($attribute, [self::EDIT, self::DELETE])) {
            if ($this->decisionManager->decide($token, array(Customer::ROLE_ADMIN))) {
                return true;
            }
        }

        // you know $subject is a Csutomer object, thanks to supports
        $currentCustomer = $subject;

        switch ($attribute) {
            case self::EDIT:
                return $this->canEdit($currentCustomer, $customer);
        }

        throw new \LogicException('This code should not be reached!');
    }

    /**
     * @param Customer $currentCustomer
     * @param Customer $customer
     * @return bool
     */
    private function canEdit(Customer $currentCustomer, Customer $customer)
    {
        return ($currentCustomer->getId() == $customer->getId());
    }
}
