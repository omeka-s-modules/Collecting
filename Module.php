<?php
namespace Collecting;

use Omeka\Module\AbstractModule;
use Zend\ServiceManager\ServiceLocatorInterface;

class Module extends AbstractModule
{
    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }

    public function install(ServiceLocatorInterface $serviceLocator)
    {
        $conn = $serviceLocator->get('Omeka\Connection');
        $conn->exec('
CREATE TABLE collecting_prompt (id INT AUTO_INCREMENT NOT NULL, collecting_form_id INT NOT NULL, property_id INT DEFAULT NULL, position INT NOT NULL, type VARCHAR(255) NOT NULL, text LONGTEXT DEFAULT NULL, media_type VARCHAR(255) DEFAULT NULL, input_type VARCHAR(255) DEFAULT NULL, select_options LONGTEXT DEFAULT NULL, INDEX IDX_98FE9BA648BB34C9 (collecting_form_id), INDEX IDX_98FE9BA6549213EC (property_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB;
CREATE TABLE collecting_form (id INT AUTO_INCREMENT NOT NULL, `label` VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB;
CREATE TABLE collecting_input (id INT AUTO_INCREMENT NOT NULL, collecting_prompt_id INT NOT NULL, text LONGTEXT NOT NULL, INDEX IDX_C6E2CFC9730468B0 (collecting_prompt_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB;
ALTER TABLE collecting_prompt ADD CONSTRAINT FK_98FE9BA648BB34C9 FOREIGN KEY (collecting_form_id) REFERENCES collecting_form (id);
ALTER TABLE collecting_prompt ADD CONSTRAINT FK_98FE9BA6549213EC FOREIGN KEY (property_id) REFERENCES property (id);
ALTER TABLE collecting_input ADD CONSTRAINT FK_C6E2CFC9730468B0 FOREIGN KEY (collecting_prompt_id) REFERENCES collecting_prompt (id);');
    }

    public function uninstall(ServiceLocatorInterface $serviceLocator)
    {
        $conn = $serviceLocator->get('Omeka\Connection');
        $conn->exec('
DROP TABLE IF EXISTS mapping;
DROP TABLE IF EXISTS mapping_marker');
    }
}
