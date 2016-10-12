<?php

namespace spec\Welp\MailchimpBundle\Controller;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

class WebhookControllerSpec extends ObjectBehavior
{

    function it_is_initializable()
    {
        $this->shouldHaveType('Welp\MailchimpBundle\Controller\WebhookController');
        $this->shouldHaveType('Symfony\Bundle\FrameworkBundle\Controller\Controller');
    }

    function it_access_denied_if_secret_is_false()
    {
        $request = new Request();
        $request->setMethod('POST');

        $this->shouldThrow(new \Exception('incorrect data format!'))
            ->duringIndexAction($request);
    }

    /*function it_pass_when_secret_is_true(){
        //$this->getParameter('welp_mailchimp.lists')->willReturn(['listid01' => ['webhook_secret' => 'mysecretTest']]);

        $request = new Request();
        $request->setMethod('POST');
        var_dump($request); die();


    }*/
}
