<?php
namespace Collecting\Db\Filter;

use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\Mapping\ClassMetaData;
use Doctrine\ORM\Query\Filter\SQLFilter;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Check that the current user can view a collecting item.
 */
class VisibilityFilter extends SQLFilter
{
    protected $services;

    public function addFilterConstraint(ClassMetadata $targetEntity, $targetTableAlias) {

        if ('Collecting\Entity\CollectingItem' !== $targetEntity->getName()) {
            return '';
        }

        $services = $this->getServiceLocator();

        $acl = $services->get('Omeka\Acl');
        if ($acl->userIsAllowed('Omeka\Entity\Resource', 'view-all')) {
            return '';
        }

        // Users can view public items they do not own.
        $constraints = ['r.is_public = 1'];
        $identity = $services->get('Omeka\AuthenticationService')->getIdentity();
        if ($identity) {
            // Users can view all resources they own.
            $constraints[] = 'OR';
            $constraints[] = sprintf(
                'r.owner_id = %s',
                $this->getConnection()->quote($identity->getId(), Type::INTEGER)
            );
        }

        $constraint = sprintf(
            '%1$s.item_id = (SELECT r.id FROM resource r WHERE (%2$s) AND r.id = %1$s.item_id)',
            $targetTableAlias, implode(' ', $constraints)
        );

        return $constraint;
    }

    public function setServiceLocator(ServiceLocatorInterface $services)
    {
        $this->services = $services;
    }

    public function getServiceLocator()
    {
        return $this->services;
    }
}
