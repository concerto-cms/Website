<?php
/**
 * Created by PhpStorm.
 * User: mathieu
 * Date: 31/05/14
 * Time: 15:34
 */
namespace ConcertoCms\Website\Controller;

use ConcertoCms\CoreBundle\Document\ContentInterface;
use ConcertoCms\CoreBundle\Service\Content;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;

class PageController {
    private $twig;
    private $cm;
    private $router;

    public function __construct(
        EngineInterface $twig,
        Content $contentManager,
        RouterInterface $router
    ) {
        $this->twig = $twig;
        $this->cm = $contentManager;
        $this->router = $router;
    }
    public function splashAction()
    {
        $route = $this->cm->getRoute("/en");
        $uri = $this->router->generate($route);
        return new RedirectResponse($uri, 301);
    }
    public function pageAction(Request $request, ContentInterface $contentDocument)
    {
        return $this->twig->renderResponse(":Page:page.html.twig", array("document" => $contentDocument));
    }
} 