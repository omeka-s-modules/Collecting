<?php
namespace Collecting;

use Omeka\Module\AbstractModule;
use Zend\EventManager\Event;
use Zend\EventManager\SharedEventManagerInterface;
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
        $acl->allow(null, 'Collecting\Controller\SiteAdmin\Form');
        $acl->allow(null, 'Collecting\Controller\Site\Index');
        $acl->allow(null, 'Collecting\Api\Adapter\CollectingFormAdapter', ['search', 'read']);
        $acl->allow(null, 'Collecting\Entity\CollectingForm', ['read']);
    }

    public function install(ServiceLocatorInterface $services)
    {
        $conn = $services->get('Omeka\Connection');
        // Reduce installation time by toggling off foreign key checks.
        $conn->exec('SET FOREIGN_KEY_CHECKS = 0');
        $conn->exec('
CREATE TABLE collecting_item (id INT AUTO_INCREMENT NOT NULL, item_id INT DEFAULT NULL, form_id INT NOT NULL, collecting_user_id INT NOT NULL, created DATETIME NOT NULL, modified DATETIME DEFAULT NULL, UNIQUE INDEX UNIQ_D414538C126F525E (item_id), INDEX IDX_D414538C5FF69B7D (form_id), INDEX IDX_D414538CB0237C21 (collecting_user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB;
CREATE TABLE collecting_prompt (id INT AUTO_INCREMENT NOT NULL, form_id INT NOT NULL, property_id INT DEFAULT NULL, position INT NOT NULL, type VARCHAR(255) NOT NULL, text LONGTEXT DEFAULT NULL, input_type VARCHAR(255) DEFAULT NULL, select_options LONGTEXT DEFAULT NULL, media_type VARCHAR(255) DEFAULT NULL, required TINYINT(1) NOT NULL, INDEX IDX_98FE9BA65FF69B7D (form_id), INDEX IDX_98FE9BA6549213EC (property_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB;
CREATE TABLE collecting_form (id INT AUTO_INCREMENT NOT NULL, item_set_id INT DEFAULT NULL, site_id INT DEFAULT NULL, owner_id INT DEFAULT NULL, label VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, INDEX IDX_99878BDD960278D7 (item_set_id), INDEX IDX_99878BDDF6BD1646 (site_id), INDEX IDX_99878BDD7E3C61F9 (owner_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB;
CREATE TABLE collecting_input (id INT AUTO_INCREMENT NOT NULL, prompt_id INT NOT NULL, collecting_item_id INT NOT NULL, text LONGTEXT NOT NULL, INDEX IDX_C6E2CFC9B5C4AA38 (prompt_id), INDEX IDX_C6E2CFC9522FDEA (collecting_item_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB;
CREATE TABLE collecting_user (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, UNIQUE INDEX UNIQ_469CA0DBA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB;
ALTER TABLE collecting_item ADD CONSTRAINT FK_D414538C126F525E FOREIGN KEY (item_id) REFERENCES item (id) ON DELETE SET NULL;
ALTER TABLE collecting_item ADD CONSTRAINT FK_D414538C5FF69B7D FOREIGN KEY (form_id) REFERENCES collecting_form (id) ON DELETE CASCADE;
ALTER TABLE collecting_item ADD CONSTRAINT FK_D414538CB0237C21 FOREIGN KEY (collecting_user_id) REFERENCES collecting_user (id) ON DELETE CASCADE;
ALTER TABLE collecting_prompt ADD CONSTRAINT FK_98FE9BA65FF69B7D FOREIGN KEY (form_id) REFERENCES collecting_form (id) ON DELETE CASCADE;
ALTER TABLE collecting_prompt ADD CONSTRAINT FK_98FE9BA6549213EC FOREIGN KEY (property_id) REFERENCES property (id) ON DELETE SET NULL;
ALTER TABLE collecting_form ADD CONSTRAINT FK_99878BDD960278D7 FOREIGN KEY (item_set_id) REFERENCES item_set (id) ON DELETE SET NULL;
ALTER TABLE collecting_form ADD CONSTRAINT FK_99878BDDF6BD1646 FOREIGN KEY (site_id) REFERENCES site (id) ON DELETE CASCADE;
ALTER TABLE collecting_form ADD CONSTRAINT FK_99878BDD7E3C61F9 FOREIGN KEY (owner_id) REFERENCES user (id) ON DELETE SET NULL;
ALTER TABLE collecting_input ADD CONSTRAINT FK_C6E2CFC9B5C4AA38 FOREIGN KEY (prompt_id) REFERENCES collecting_prompt (id) ON DELETE CASCADE;
ALTER TABLE collecting_input ADD CONSTRAINT FK_C6E2CFC9522FDEA FOREIGN KEY (collecting_item_id) REFERENCES collecting_item (id) ON DELETE CASCADE;
ALTER TABLE collecting_user ADD CONSTRAINT FK_469CA0DBA76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE SET NULL;
');
        $conn->exec('SET FOREIGN_KEY_CHECKS = 1');
    }

    public function uninstall(ServiceLocatorInterface $services)
    {
        $services->get('Omeka\Connection')->exec('
SET FOREIGN_KEY_CHECKS=0;
DROP TABLE IF EXISTS collecting_item;
DROP TABLE IF EXISTS collecting_prompt;
DROP TABLE IF EXISTS collecting_form;
DROP TABLE IF EXISTS collecting_input;
DROP TABLE IF EXISTS collecting_user;
SET FOREIGN_KEY_CHECKS=1;
DELETE FROM site_page_block WHERE layout = "collecting";
DELETE FROM site_setting WHERE id = "collecting_recaptcha_secret_key";
DELETE FROM site_setting WHERE id = "collecting_recaptcha_site_key";
');
    }

    public function attachListeners(SharedEventManagerInterface $sharedEventManager)
    {
        $services = $this->getServiceLocator();
        $siteSettings = $services->get('Omeka\SiteSettings');
        $connection = $services->get('Omeka\Connection');

        // When a property is deleted convert all "property" prompts that are
        // assigned to that property to "input" prompts. When the prompt has no
        // text use the property label.
        $sharedEventManager->attach(
            'Omeka\Entity\Property',
            'entity.remove.pre',
            function (Event $event) use ($connection) {
                $sql = '
                UPDATE collecting_prompt
                SET type = "input",
                text = CASE WHEN COALESCE(text, "") = "" THEN ? ELSE text END
                WHERE property_id = ?';
                $property = $event->getTarget();
                $connection->executeUpdate($sql, [$property->getLabel(), $property->getId()]);
            }
        );

        // Add the reCAPTCHA site and secret keys to the site settings form.
        $sharedEventManager->attach(
            '*',
            'site_settings.form',
            function (Event $event) use ($siteSettings) {
                $form = $event->getParam('form');
                $form->add([
                    'type' => 'text',
                    'name' => 'collecting_recaptcha_site_key',
                    'options' => [
                        'label' => 'Collecting reCAPTCHA site key',
                    ],
                    'attributes' => [
                        'value' => $siteSettings->get('collecting_recaptcha_site_key'),
                    ],
                ]);
                $form->add([
                    'type' => 'text',
                    'name' => 'collecting_recaptcha_secret_key',
                    'options' => [
                        'label' => 'Collecting reCAPTCHA secret key',
                    ],
                    'attributes' => [
                        'value' => $siteSettings->get('collecting_recaptcha_secret_key'),
                    ],
                ]);
            }
        );
    }
}
