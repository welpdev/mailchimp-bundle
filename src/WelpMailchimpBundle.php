<?php

namespace Welp\MailchimpBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Welp\MailchimpBundle\DependencyInjection\WelpMailchimpExtension;

class WelpMailchimpBundle extends Bundle
{
    public function getContainerExtension()
    {
        return new WelpMailchimpExtension();
    }
}
