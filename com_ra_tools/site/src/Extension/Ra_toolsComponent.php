<?php

namespace Ramblers\Component\Ra_tools\Administrator\Extension;

defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Categories\CategoryServiceInterface;
use Joomla\CMS\Categories\CategoryServiceTrait;
use Joomla\CMS\Extension\BootableExtensionInterface;
use Joomla\CMS\Extension\MVCComponent;
use Joomla\CMS\HTML\HTMLRegistryAwareTrait;
use Ramblers\Component\Ra_tools\Administrator\Service\HTML\AdministratorService;
use Psr\Container\ContainerInterface;

class Ra_toolsComponent extends MVCComponent implements BootableExtensionInterface, CategoryServiceInterface {

    use CategoryServiceTrait;
    use HTMLRegistryAwareTrait;

    public function boot(ContainerInterface $container) {
        $this->getRegistry()->register('ra_toolsadministrator', new AdministratorService);
    }

}

