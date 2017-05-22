<?php

namespace Oro\BugTrackerBundle\Security;

use Oro\BugTrackerBundle\Entity\Issue;
use Oro\BugTrackerBundle\Entity\Customer;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManager;


class IssueVoter extends Voter
{
    // these strings are just invented: you can use anything
    const VIEW = 'view_issue';
    const EDIT = 'edit_issue';
    const DELETE = 'delete_issue';

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
        if (!in_array($attribute, array(self::VIEW, self::EDIT, self::DELETE))) {
            return false;
        }

        // only vote on Post objects inside this voter
        if (!$subject instanceof Issue) {
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

        if (in_array($attribute, [self::VIEW, self::EDIT])) {
            if ($this->decisionManager->decide($token, array(Customer::ROLE_MANAGER))) {
                return true;
            }
        }

        // you know $subject is a Post object, thanks to supports
        /** @var Post $post */
        $issue = $subject;

        switch ($attribute) {
            case self::VIEW:
                return $this->canView($issue, $customer);
            case self::EDIT:
                return $this->canEdit();
            case self::DELETE:
                return $this->canDelete($token);
        }

        throw new \LogicException('This code should not be reached!');
    }

    /**
     * @param Issue $issue
     * @param Customer $customer
     * @return bool
     */
    private function canView(Issue $issue, Customer $customer)
    {
        $isCollaboration = $issue->getCollaboration()->contains($customer);

        return $isCollaboration;
    }

    /**
     * @param Issue $issue
     * @param Customer $customer
     * @return bool
     */
    private function canEdit(Issue $issue, Customer $customer)
    {
        return false;
    }

    /**
     * @param TokenInterface $token
     * @return bool
     */
    private function canDelete(TokenInterface $token)
    {
        return $this->decisionManager->decide($token, array(Customer::ROLE_ADMIN));
    }
}