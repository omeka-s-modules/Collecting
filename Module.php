<?php
namespace Collecting;

use Omeka\Module\AbstractModule;
use Zend\Mvc\MvcEvent;
use Zend\ServiceManager\ServiceLocatorInterface;

class Module extends AbstractModule
{
    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }

    public function onBootstrap(MvcEvent $event)
    {
        parent::onBootstrap($event);

        $acl = $this->getServiceLocator()->get('Omeka\Acl');
        $acl->allow(null, 'Collecting\Controller\Admin\Index');
    }

    public function install(ServiceLocatorInterface $services)
    {
        $services->get('Omeka\Connection')->exec('
CREATE TABLE collecting_item (id INT AUTO_INCREMENT NOT NULL, item_id INT DEFAULT NULL, form_id INT NOT NULL, UNIQUE INDEX UNIQ_D414538C126F525E (item_id), INDEX IDX_D414538C5FF69B7D (form_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB;
CREATE TABLE collecting_prompt (id INT AUTO_INCREMENT NOT NULL, form_id INT NOT NULL, property_id INT DEFAULT NULL, position INT NOT NULL, type VARCHAR(255) NOT NULL, text LONGTEXT DEFAULT NULL, input_type VARCHAR(255) DEFAULT NULL, select_options LONGTEXT DEFAULT NULL, media_type VARCHAR(255) DEFAULT NULL, INDEX IDX_98FE9BA65FF69B7D (form_id), INDEX IDX_98FE9BA6549213EC (property_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB;
CREATE TABLE collecting_form (id INT AUTO_INCREMENT NOT NULL, item_set_id INT DEFAULT NULL, site_id INT DEFAULT NULL, owner_id INT DEFAULT NULL, `label` VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, INDEX IDX_99878BDD960278D7 (item_set_id), INDEX IDX_99878BDDF6BD1646 (site_id), INDEX IDX_99878BDD7E3C61F9 (owner_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB;
CREATE TABLE collecting_input (id INT AUTO_INCREMENT NOT NULL, prompt_id INT NOT NULL, item_id INT NOT NULL, text LONGTEXT NOT NULL, INDEX IDX_C6E2CFC9B5C4AA38 (prompt_id), INDEX IDX_C6E2CFC9126F525E (item_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB;
ALTER TABLE collecting_item ADD CONSTRAINT FK_D414538C126F525E FOREIGN KEY (item_id) REFERENCES item (id) ON DELETE SET NULL;
ALTER TABLE collecting_item ADD CONSTRAINT FK_D414538C5FF69B7D FOREIGN KEY (form_id) REFERENCES collecting_form (id) ON DELETE CASCADE;
ALTER TABLE collecting_prompt ADD CONSTRAINT FK_98FE9BA65FF69B7D FOREIGN KEY (form_id) REFERENCES collecting_form (id) ON DELETE CASCADE;
ALTER TABLE collecting_prompt ADD CONSTRAINT FK_98FE9BA6549213EC FOREIGN KEY (property_id) REFERENCES property (id);
ALTER TABLE collecting_form ADD CONSTRAINT FK_99878BDD960278D7 FOREIGN KEY (item_set_id) REFERENCES item_set (id) ON DELETE SET NULL;
ALTER TABLE collecting_form ADD CONSTRAINT FK_99878BDDF6BD1646 FOREIGN KEY (site_id) REFERENCES site (id) ON DELETE SET NULL;
ALTER TABLE collecting_form ADD CONSTRAINT FK_99878BDD7E3C61F9 FOREIGN KEY (owner_id) REFERENCES user (id) ON DELETE SET NULL;
ALTER TABLE collecting_input ADD CONSTRAINT FK_C6E2CFC9B5C4AA38 FOREIGN KEY (prompt_id) REFERENCES collecting_prompt (id) ON DELETE CASCADE;
ALTER TABLE collecting_input ADD CONSTRAINT FK_C6E2CFC9126F525E FOREIGN KEY (item_id) REFERENCES collecting_item (id) ON DELETE CASCADE;
');
    }

    public function uninstall(ServiceLocatorInterface $services)
    {
        $services->get('Omeka\Connection')->exec('
SET FOREIGN_KEY_CHECKS=0;
DROP TABLE IF EXISTS collecting_prompt;
DROP TABLE IF EXISTS collecting_form;
DROP TABLE IF EXISTS collecting_input;
DROP TABLE IF EXISTS collecting_item;
SET FOREIGN_KEY_CHECKS=1;
DELETE FROM site_page_block WHERE layout = "collecting";
');
    }
}
