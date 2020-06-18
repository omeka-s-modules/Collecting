<?php
namespace Collecting\Permissions\Assertion;

use Zend\Permissions\Acl\Acl;
use Zend\Permissions\Acl\Assertion\AssertionInterface;
use Zend\Permissions\Acl\Resource\ResourceInterface;
use Zend\Permissions\Acl\Role\RoleInterface;

/**
 * Does the user have permission to view a collecting item's user name?
 */
class HasUserNamePermissionAssertion implements AssertionInterface
{
    public function assert(Acl $acl, RoleInterface $role = null,
        ResourceInterface $resource = null, $privilege = null
    ) {
        if ('private' === $resource->getForm()->getAnonType()) {
            // The collecting form restricts user name.
            return false;
        }

        if ($resource->getAnon()) {
            // This item was submitted anonymously.
            return false;
        }

        return true;
    }
}
