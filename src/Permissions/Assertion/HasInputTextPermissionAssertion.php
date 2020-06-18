<?php
namespace Collecting\Permissions\Assertion;

use Zend\Permissions\Acl\Acl;
use Zend\Permissions\Acl\Assertion\AssertionInterface;
use Zend\Permissions\Acl\Resource\ResourceInterface;
use Zend\Permissions\Acl\Role\RoleInterface;

/**
 * Does the user have permission to view an input's text?
 */
class HasInputTextPermissionAssertion implements AssertionInterface
{
    public function assert(Acl $acl, RoleInterface $role = null,
        ResourceInterface $resource = null, $privilege = null
    ) {
        $promptType = $resource->getPrompt()->getType();

        // "User Private" inputs are always restricted.
        if ('user_private' === $promptType) {
            return false;
        }

        // "User Public" inputs are conditionally restricted.
        if ('user_public' === $promptType) {

            $cItem = $resource->getCollectingItem();
            $cForm = $cItem->getForm();

            if ('private' === $cForm->getAnonType()) {
                // The collecting form restricts all user inputs.
                return false;
            }

            if ($cItem->getAnon()) {
                // This item was submitted anonymously.
                return false;
            }
        }

        return true;
    }
}
