<?php
/**
 * CakePHP(tm) : Rapid Development Framework (https://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 * @link      https://cakephp.org CakePHP(tm) Project
 * @since     3.3.0
 * @license   https://opensource.org/licenses/mit-license.php MIT License
 */
namespace App;

use Cake\Core\Configure;
use Cake\Core\Plugin;
use Cake\Error\Middleware\ErrorHandlerMiddleware;
use Cake\Http\BaseApplication;
use Cake\Http\Middleware\SecurityHeadersMiddleware;
use Cake\Routing\Middleware\AssetMiddleware;
use Cake\Routing\Middleware\RoutingMiddleware;

/**
 * Application setup class.
 *
 * This defines the bootstrapping logic and middleware layers you
 * want to use in your application.
 */
class Application extends BaseApplication
{
    /**
     * {@inheritDoc}
     */
    public function bootstrap()
    {
        Stopwatch::start('Application::bootstrap');

        parent::bootstrap();

        // @td 3.0
        $this->addPlugin(\Api\Plugin::class, ['bootstrap' => true, 'routes' => true]);
        $this->addPlugin(\Bookmarks\Plugin::class, ['bootstrap' => true, 'routes' => true]);
        $this->addPlugin(\Feeds\Plugin::class, ['bootstrap' => true, 'routes' => true]);
        // @td 3.0
        // $this->addPlugin('M')->enable('bootstrap')->enable('routes');
        $this->addPlugin(\SaitoHelp\Plugin::class, ['bootstrap' => true]);
        $this->addPlugin(\Sitemap\Plugin::class, ['bootstrap' => true, 'routes' => true]);

        Plugin::load('Cron');
        Plugin::load('BbcodeParser');
        Plugin::load('Commonmark');
        Plugin::load('Cron');
        Plugin::load('Detectors');
        Plugin::load('Embedly');
        Plugin::load('MailObfuscator');
        Plugin::load('Markitup');
        Plugin::load('Paz');
        Plugin::load('SpectrumColorpicker');
        Plugin::load('Stopwatch');

        Plugin::load('Proffer');
    }

    /**
     * Setup the middleware queue your application will use.
     *
     * @param \Cake\Http\MiddlewareQueue $middlewareQueue The middleware queue to setup.
     * @return \Cake\Http\MiddlewareQueue The updated middleware queue.
     */
    public function middleware($middlewareQueue)
    {
        $middlewareQueue
            // Catch any exceptions in the lower layers,
            // and make an error page/response
            ->add(ErrorHandlerMiddleware::class)

            // Handle plugin/theme assets like CakePHP normally does.
            ->add(AssetMiddleware::class)

            // Add routing middleware.
            // Routes collection cache enabled by default, to disable route caching
            // pass null as cacheConfig, example: `new RoutingMiddleware($this)`
            // you might want to disable this cache in case your routing is extremely simple
            ->add(new RoutingMiddleware($this, '_cake_routes_'));

        $security = (new SecurityHeadersMiddleware())
            ->setXFrameOptions(strtolower(Configure::read('Saito.X-Frame-Options')));
        $middlewareQueue->add($security);

        return $middlewareQueue;
    }
}
