<?php
/**
 * Created by PhpStorm.
 * User: mathieu
 * Date: 01/06/14
 * Time: 15:44
 */
namespace ConcertoCms\Website\Command;

use ConcertoCms\CoreBundle\Document\Page;
use ConcertoCms\CoreBundle\Document\Route;
use ConcertoCms\CoreBundle\Service\Content;
use ConcertoCms\CoreBundle\Service\Navigation;
use Knp\Bundle\MarkdownBundle\MarkdownParserInterface;
use Symfony\Cmf\Bundle\MenuBundle\Doctrine\Phpcr\Menu;
use Symfony\Cmf\Bundle\MenuBundle\Doctrine\Phpcr\MenuNode;
use Symfony\Cmf\Bundle\RoutingBundle\Doctrine\Phpcr\RedirectRoute;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input;
use Symfony\Component\Console\Output;

class UpdateDocsCommand extends Command
{
    private $cm;
    private $nm;
    private $parser;
    public function __construct(Content $cm, Navigation $nav, MarkdownParserInterface $parser)
    {
        $this->parser = $parser;
        $this->cm = $cm;
        $this->nm = $nav;
         parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('website:updatedocs')
            ->setDescription('Render markdown code and update CMS pages')
        ;
    }

    private $output;
    private $folderRoutes = array();
    protected function execute(Input\InputInterface $input, Output\OutputInterface $output)
    {
        $this->output = $output;
        $rootRoute = $this->cm->getRoute("en/docs");

        if (!$rootRoute) {
            throw new \RuntimeException("Can't find documentation root route");
        }
        $rootPage = $rootRoute->getContent();
        $rootMenu = $this->nm->getMenu("main-menu/en/docs");
        if (!$rootMenu) {
            throw new \RuntimeException("Can't find documentation root menu");
        }

        $folder = str_replace("/", DIRECTORY_SEPARATOR, "vendor/concerto-cms/docs/docs");

        $dir_iterator = new \RecursiveDirectoryIterator($folder, \FilesystemIterator::SKIP_DOTS);
        $iterator = new \RecursiveIteratorIterator($dir_iterator, \RecursiveIteratorIterator::SELF_FIRST);

        /**
         * @var $file \SplFileInfo
         */
        foreach ($iterator as $file) {
            $output->writeln("");
            $slug = $file->getBasename("." . $file->getExtension());

            if ($file->isFile() && $file->getExtension() == "md") {
                $output->writeln("Found file: " . $file->getFilename());
                $parentSlug = substr($file->getPath(), strlen($folder)+1);

                $parentPage = $this->cm->getPage("en/docs/" . $parentSlug);
                $parentRoute = $this->cm->getRoute("en/docs/" . $parentSlug);
                $parentMenu = $this->nm->getMenu("main-menu/en/docs/" . $parentSlug);

                $page = $this->cm->getPage("en/docs/" . $parentSlug . "/" . $slug);
                if (!$page) {
                    $output->writeln("Creating page en/docs/" . $parentSlug . "/" . $slug);
                    $page = new Page();
                    $page->setParent($parentPage);
                    $page->setSlug($slug);

                    $this->cm->persist($page);
                    $this->cm->persist($route);

                }

                $route = $this->cm->getRoute("en/docs/" . $parentSlug . "/" . $slug);
                if (!$route) {
                    $route = new Route();
                    $route->setName($slug);
                    $route->setParentDocument($parentRoute);
                    $route->setContent($page);
                }

                $menu = $this->nm->getMenu("en/docs/" . $parentSlug . "/" . $slug);
                if (!$menu) {
                    $menu = new MenuNode();
                    $menu->setParentDocument($parentMenu);
                    $menu->setName($slug);
                    $menu->setLabel($slug); // todo: get title from .md content
                    $menu->setContent($route);
                    $this->cm->persist($menu);
                }

                $markdown = file_get_contents($file->getRealPath());
                $page->setTitle($slug);
                $page->setContent($this->parser->transformMarkdown($markdown));

            } elseif ($file->isDir()) {
                $output->writeln("Found folder " . $file->getFilename());

                $route = $this->cm->getRoute("en/docs/" . $slug);
                if (!$route) {
                    $route = new RedirectRoute();
                    $route->setName($slug);
                    $route->setRouteTarget($rootRoute);
                    $route->setParentDocument($rootRoute);
                    $route->setPermanent(true);
                    $this->cm->persist($route);
                }
                $page = $this->cm->getPage("en/docs/" . $slug);
                if (!$page) {
                    $page = new Page();
                    $page->setParent($rootPage);
                    $page->setSlug($slug);
                    $this->cm->persist($page);
                }

                $menu = $this->nm->getMenu("main-menu/en/docs/" . $slug);
                if (!$menu) {
                    $menu = new MenuNode();
                    $menu->setParentDocument($rootMenu);
                    $menu->setName($slug);
                    $menu->setLabel($slug);
                    $this->cm->persist($menu);
                }
                $this->cm->flush();
            }

        }
        $output->writeln("");
        $output->writeln("Flushing...");
        $this->cm->flush();
        $output->writeln("Done!");
    }
}
