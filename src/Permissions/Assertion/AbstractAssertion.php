<?php
namespace Collecting\Permissions\Assertion;

use Omeka\Permissions\Acl;
use Zend\Permissions\Acl\Assertion\AssertionInterface;
use Zend\Permissions\Acl\Role\RoleInterface;

abstract class AbstractAssertion implements AssertionInterface
{
    const PERMITTED_ROLES = [
        Acl::ROLE_REVIEWER,
        Acl::ROLE_EDITOR,
        Acl::ROLE_SITE_ADMIN,
        Acl::ROLE_GLOBAL_ADMIN,
    ];

    protected function roleHasPermission(RoleInterface $role = null)
    {
        if (null === $role) {
            return false;
        }
        return in_array($role->getRoleId(), self::PERMITTED_ROLES);
    }
}
