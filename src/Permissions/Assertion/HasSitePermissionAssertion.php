<?php
namespace Collecting\Permissions\Assertion;

use Collecting\Entity\CollectingInput;
use Omeka\Permissions\Assertion\HasSitePermissionAssertion as OmekaHasSitePermissionAssertion;
use Zend\Permissions\Acl\Acl;
use Zend\Permissions\Acl\Resource\ResourceInterface;
use Zend\Permissions\Acl\Role\RoleInterface;

class HasSitePermissionAssertion extends OmekaHasSitePermissionAssertion
{
    public function assert(Acl $acl, RoleInterface $role = null,
        ResourceInterface $resource = null, $privilege = null
    ) {
        if ($resource instanceof CollectingInput) {
            // A collecting input inherits the site from its collecting item.
            $resource = $resource->getCollectingItem();
        }
        return parent::assert($acl, $role, $resource, $privilege);
    }
}
