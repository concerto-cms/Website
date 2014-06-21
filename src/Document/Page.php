<?php
/**
 * Created by PhpStorm.
 * User: mathieu
 * Date: 17/06/14
 * Time: 10:07
 */

namespace ConcertoCms\Website\Document;

use Doctrine\ODM\PHPCR\Mapping\Annotations as PHPCR;

/**
 * @PHPCR\Document(referenceable=true)
 */
class Page extends \ConcertoCms\CoreBundle\Document\Page
{
    /**
     * @PHPCR\String(nullable=true)
     */
    protected $header;

    /**
     * @param mixed $header
     */
    public function setHeader($header)
    {
        $this->header = $header;
    }

    /**
     * @return mixed
     */
    public function getHeader()
    {
        return $this->header;
    }

    public function getClassname()
    {
        return "ConcertoCmsWebsite:Page";
    }

    public function jsonSerialize()
    {
        $data = parent::jsonSerialize();
        $data["header"] = $this->getHeader();
        return $data;
    }
}
