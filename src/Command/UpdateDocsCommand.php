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
use Symfony\Cmf\Bundle\RoutingBundle\Doctrine\Phpcr\RedirectRoute;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input;
use Symfony\Component\Console\Output;

class UpdateDocsCommand extends Command {
    private $cm;
    public function __construct(Content $cm)
    {
        $this->cm = $cm;
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
        $docroot = $this->cm->getRoute("en/docs");
        if (!$docroot) {
            throw new \RuntimeException("Can't find documentation root");
        }

        $parentPage = $docroot->getContent();
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
                $parentUrl = substr($file->getPath(), strlen($folder)+1);

                if ($parentUrl == "") {
                    $parentUrl = "en/docs";
                } else {
                    $parentUrl = "en/docs/" . $parentUrl;
                }

                $page = $this->cm->getPage($parentUrl . "/" . $slug);
                if (!$page) {
                    $page = new Page();
                    $page->setParent($parentPage);
                    $page->setSlug($slug);

                    $route = new Route();
                    $route->setName($slug);
                    $route->setParentDocument($this->cm->getRoute($parentUrl));
                    $route->setContent($page);

                    $this->cm->persist($page);
                    $this->cm->persist($route);
                }

                $output->writeln("parentUrl: " . $parentUrl);
                $output->writeln("slug: " . $slug);

            } elseif ($file->isDir()) {
                $output->writeln("Creating folder " . $file->getFilename());

                $checkRoute = $this->cm->getRoute("en/docs/" . $slug);
                if ($checkRoute) {
                    $output->writeln("Folder already exists, skipping...");
                    continue;
                }
                $page = new Page();
                $page->setParent($parentPage);
                $page->setSlug($slug);

                $route = new RedirectRoute();
                $route->setName($slug);
                $route->setRouteTarget($docroot);
                $route->setParentDocument($docroot);
                $route->setPermanent(true);
                $route->setContent($page);

                $this->cm->persist($page);
                $this->cm->persist($route);
            }

        }
        $output->writeln("");
        $output->writeln("Flushing...");
        $this->cm->flush();
        $output->writeln("Done!");
    }
} 