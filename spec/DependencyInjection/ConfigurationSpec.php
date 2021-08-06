<?php

namespace spec\Welp\MailchimpBundle\DependencyInjection;

use PhpSpec\ObjectBehavior;

class ConfigurationSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('Welp\MailchimpBundle\DependencyInjection\Configuration');
    }

    function it_is_symfony_configuration()
    {
        $this->shouldImplement('Symfony\Component\Config\Definition\ConfigurationInterface');
    }

    function it_gets_config_tree_builder()
    {
        $this
            ->getConfigTreeBuilder()
            ->shouldHaveType('Symfony\Component\Config\Definition\Builder\TreeBuilder');
    }
}
