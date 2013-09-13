<?php

namespace KPhoen\Provider;

use Silex\Application;
use Silex\ServiceProviderInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Negotiation services provider for Silex.
 *
 * Provides the following services:
 *  * `negotiator`: a Negotiator instance ;
 *  * `format.negotiator`: a FormatNegotiator instance ;
 *  * `language.negotiator`: a LanguageNegotiator instance.
 *
 * @author Kévin Gomez <contact@kevingomez.fr>
 */
class NegotiationServiceProvider implements ServiceProviderInterface
{
    protected $app;
    protected $customFormats = array();

    /**
     * Constructor.
     *
     * @param array $customFormats  A list of custorm formats to add in the
     *                              format negotiator. The formats must be
     *                              given as a 'formatName' => mimeTypes map.
     */
    public function __construct(array $customFormats = array())
    {
        $this->customFormats = $customFormats;
    }

    /**
     * Register the services in the application.
     *
     * @param Application $app The application.
     */
    public function register(Application $app)
    {
        $this->app = $app;

        $app['negotiator'] = $app->share(function($app) {
            return new \Negotiation\Negotiator();
        });

        $app['language.negotiator'] = $app->share(function($app) {
            return new \Negotiation\LanguageNegotiator();
        });

        $app['format.negotiator'] = $app->share(function($app) {
            $negotiator = new \Negotiation\FormatNegotiator();

            // add new formats
            foreach ($this->customFormats as $name => $mimeTypes) {
                $negotiator->registerFormat($name, $mimeTypes);
            }

            return $negotiator;
        });
    }

    /**
     * Listen to the REQUEST event emitted by the kernel in order to update the
     * request with the given custom formats.
     *
     * @param Application $app The current application.
     */
    public function boot(Application $app)
    {
        $app['dispatcher']->addListener(KernelEvents::REQUEST, array($this, 'onEarlyKernelRequest'), 128);
    }

    /**
     * Update the request with the given custom formats.
     *
     * @param GetResponseEvent $event The event.
     */
    public function onEarlyKernelRequest(GetResponseEvent $event)
    {
        $request = $event->getRequest();
        foreach ($this->customFormats as $name => $mimeTypes) {
            $request->setFormat($name, $mimeTypes);
        }
    }
}
