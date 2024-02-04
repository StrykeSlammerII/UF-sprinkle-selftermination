<?php

/*
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2021 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

namespace SelfTermination\Sprinkle\Controller;

use Psr\Http\Message\ResponseInterface as Response;
use Slim\Views\Twig;

/**
 * CoreController Class.
 *
 * Implements some common sitewide routes.
 *
 * N.B.: THIS FILE IS SAFE TO EDIT OR DELETE.
 */
class AppController
{
    /**
     * Renders the default home page for UserFrosting.
     * By default, this is the page that non-authenticated users will first see when they navigate to your website's root.
     * Request type: GET.
     *
     * @param Response $response
     * @param Twig     $view
     */
    public function pageIndex(Response $response, Twig $view): Response
    {
        return $view->render($response, 'pages/index.html.twig');
    }

    /**
     * Renders a sample "about" page for UserFrosting.
     * Request type: GET.
     *
     * @param Response $response
     * @param Twig     $view
     */
    public function pageAbout(Response $response, Twig $view): Response
    {
        return $view->render($response, 'pages/about.html.twig');
    }

    /**
     * Renders terms of service page.
     * Request type: GET.
     *
     * @param Response $response
     * @param Twig     $view
     */
    public function pageLegal(Response $response, Twig $view): Response
    {
        return $view->render($response, 'pages/legal.html.twig');
    }

    /**
     * Renders privacy page.
     * Request type: GET.
     *
     * @param Response $response
     * @param Twig     $view
     */
    public function pagePrivacy(Response $response, Twig $view): Response
    {
        return $view->render($response, 'pages/privacy.html.twig');
    }
}
