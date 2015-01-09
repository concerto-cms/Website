<?php
/**
 * Created by PhpStorm.
 * User: mathieu
 * Date: 31/05/14
 * Time: 15:34
 */
namespace ConcertoCms\Website\Controller;

use ConcertoCms\CoreBundle\Routes\Service\RoutesManager;
use ConcertoCms\CoreBundle\Util\PublishableInterface;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;

class PageController
{
    private $twig;
    private $rm;
    private $router;

    public function __construct(
        EngineInterface $twig,
        RoutesManager $rm,
        RouterInterface $router
    ) {
        $this->twig = $twig;
        $this->rm = $rm;
        $this->router = $router;
    }
    public function splashAction()
    {
        $route = $this->rm->getByUrl("/en");
        $uri = $this->router->generate($route);
        return new RedirectResponse($uri, 301);
    }
    public function pageAction(Request $request, PublishableInterface $contentDocument)
    {
        return $this->twig->renderResponse(":Page:page.html.twig", array("document" => $contentDocument));
    }
}
