<?php

declare(strict_types=1);

use Mezzio\Application;
use Mezzio\MiddlewareFactory;
use Psr\Container\ContainerInterface;

/**
 * FastRoute route configuration
 *
 * @see https://github.com/nikic/FastRoute
 *
 * Setup routes with a single request method:
 *
 * $app->get('/', App\Handler\HomePageHandler::class, 'home');
 * $app->post('/album', App\Handler\AlbumCreateHandler::class, 'album.create');
 * $app->put('/album/{id:\d+}', App\Handler\AlbumUpdateHandler::class, 'album.put');
 * $app->patch('/album/{id:\d+}', App\Handler\AlbumUpdateHandler::class, 'album.patch');
 * $app->delete('/album/{id:\d+}', App\Handler\AlbumDeleteHandler::class, 'album.delete');
 *
 * Or with multiple request methods:
 *
 * $app->route('/contact', App\Handler\ContactHandler::class, ['GET', 'POST', ...], 'contact');
 *
 * Or handling all request methods:
 *
 * $app->route('/contact', App\Handler\ContactHandler::class)->setName('contact');
 *
 * or:
 *
 * $app->route(
 *     '/contact',
 *     App\Handler\ContactHandler::class,
 *     Mezzio\Router\Route::HTTP_METHOD_ANY,
 *     'contact'
 * );
 */

return static function (Application $app, MiddlewareFactory $factory, ContainerInterface $container): void {

    /** This is a kind of test endpoint for pinging API. */
    $app->get('/sum', Sync\Handlers\SumHandler::class, 'test');
    
    /** Start of authorisation process. */
    $app->get('/auth', Sync\Handlers\AuthHandler::class, 'auth');
    /** Getting callback with authorisation code for getting access token. */
    $app->get('/authcallback', Sync\Handlers\AuthCallbackHandler::class, 'authcallback');

    /** Returning all contacts from Kommo account. */
    $app->get('/kommo/contacts', Sync\Handlers\ContactsHandler::class, 'contacts'); // KommoContactsHandler
    /** Returning all contacts from Unisender account. */
    $app->get('/unisender/contacts', Sync\Handlers\GetContactFromUnisenderHandler::class, 'getcontact'); // UnisenderContactsHandler
    /** The way to add new contacts to unisender via email. */
    $app->post('/unisender/contacts', Sync\Handlers\PingUnisenderHandler::class, 'unisping'); // UnisenderContactsHandler
    /** Synchronising kommo <-- unisender accounts.  */
    $app->post('/sync/contacts', Sync\Handlers\SendContactsFromKommoHandler::class, 'synccontacts'); //
    
    /** Getting all accounts from Kommo. */
    $app->get('/accounts', Sync\Handlers\AccountsHandler::class, 'accounts'); // AccountsHandler

    /** Adding new integration to database. */
    $app->post('/integration', Sync\Handlers\CreateIntegrationHandler::class, 'addintegration'); // IntegrationsHandler

    /** Processing request from widget JS code, subcribing to Kommo webhooks. */
    $app->post('/widget', Sync\Handlers\WidgetHandler::class, 'widgetpost'); // WidgetHandler

    /** Processing requests from kommo webhooks. */
    $app->post('/webhook', Sync\Handlers\KommoWebHookHandler::class, 'kommowebhook'); // WebHookHandler
    
};
