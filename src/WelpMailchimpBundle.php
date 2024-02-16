<?php

declare(strict_types=1);

namespace Welp\MailchimpBundle;

use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;
use Welp\MailchimpBundle\DependencyInjection\WelpMailchimpExtension;

class WelpMailchimpBundle extends AbstractBundle
{
    public function getPath(): string
    {
        return dirname(__DIR__);
    }

    public function getContainerExtension(): ExtensionInterface
    {
        return new WelpMailchimpExtension();
    }
}
