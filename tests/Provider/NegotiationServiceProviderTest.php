<?php

use KPhoen\Provider\NegotiationServiceProvider;
use Silex\Application;
use Symfony\Component\HttpKernel\KernelEvents;

class NegotiationServiceProviderTest extends \PHPUnit_Framework_TestCase
{
    protected $serviceProvider, $app;

    public function setUp()
    {
        $this->serviceProvider = new NegotiationServiceProvider();
        $this->app = new Application();
    }

    public function testRegisterCreatesServices()
    {
        $this->app->register($this->serviceProvider);

        $this->assertInstanceOf('\Negotiation\Negotiator', $this->app['negotiator']);
        $this->assertInstanceOf('\Negotiation\FormatNegotiator', $this->app['format.negotiator']);
        $this->assertInstanceOf('\Negotiation\LanguageNegotiator', $this->app['language.negotiator']);
    }

    public function testRegisterHandlesCustomFormats()
    {
        $customFormats = array(
            'wkt' => array('text/plain+wkt'),
        );
        $this->serviceProvider = new NegotiationServiceProvider($customFormats);
        $this->app->register($this->serviceProvider);

        $this->assertEquals('wkt', $this->app['format.negotiator']->getFormat('text/plain+wkt'));
    }

    public function testBootSetupTheRequestListener()
    {
        $this->serviceProvider->boot($this->app);

        $providerListener = null;
        $listeners = $this->app['dispatcher']->getListeners(KernelEvents::REQUEST);
        foreach ($listeners as $listener) {
            if ($listener[0] === $this->serviceProvider) {
                $providerListener = $listener;
                break;
            }
        }

        $this->assertSame($this->serviceProvider, $providerListener[0], 'The listener is registered');
    }

    public function testRequestListener()
    {
        $customFormats = array(
            'wkt' => array('text/plain+wkt'),
        );
        $this->serviceProvider = new NegotiationServiceProvider($customFormats);

        $request = $this->getMockBuilder('\Symfony\Component\HttpFoundation\Request')
            ->disableOriginalConstructor()
            ->getMock();
        $request->expects($this->once())
            ->method('setFormat')
            ->with(
                $this->equalTo('wkt'),
                $this->equalTo($customFormats['wkt'])
            );

        $event = $this->getMockBuilder('\Symfony\Component\HttpKernel\Event\GetResponseEvent')
            ->disableOriginalConstructor()
            ->getMock();
        $event->expects($this->once())
            ->method('getRequest')
            ->will($this->returnValue($request));

        $this->serviceProvider->onEarlyKernelRequest($event);
    }
}
