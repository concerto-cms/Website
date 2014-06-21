<?php
/**
 * Created by PhpStorm.
 * User: mathieu
 * Date: 17/06/14
 * Time: 10:11
 */
namespace ConcertoCms\Website\Service;

use ConcertoCms\CoreBundle\Extension\PageManagerInterface;
use ConcertoCms\CoreBundle\Extension\PageType;
use ConcertoCms\CoreBundle\Event\PagePopulateEvent;
use ConcertoCms\CoreBundle\Event\PageCreateEvent;
use ConcertoCms\CoreBundle\Document\ContentInterface;
use ConcertoCms\Website\Document\Page;

class PageManager implements PageManagerInterface
{
    /**
     * @return \ConcertoCms\CoreBundle\Extension\PageType[]
     */
    public function getPageTypes()
    {
        $page = new PageType(
            "ConcertoCmsWebsite:Page",
            "Basic page with header",
            "View.PageContent_PageWithHeader"
        );
        return array($page);
    }

    /**
     * @param PagePopulateEvent $event
     * @return void
     */
    public function onPopulate(PagePopulateEvent $event)
    {
        if ($event->getDocument() instanceof ContentInterface) {
            $this->populate($event->getDocument(), $event->getData());
        }
    }

    /**
     * @param PageCreateEvent $event
     * @return void
     */
    public function onCreate(PageCreateEvent $event)
    {
        if ($event->getType() == "ConcertoCmsWebsite:Page") {
            $event->setDocument(new Page());
        }
    }

    /**
     * @param ContentInterface $document
     * @param array $params
     */
    private function populate($document, $params)
    {
        if ($document instanceof Page) {
            if (isset($params["header"])) {
                $document->setHeader($params["header"]);

            }
        }
    }
}
