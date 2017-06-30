<?php

namespace Zeeshan\Instagrab;

use Exception;
use DOMDocument;

/**
 * Download photos and videos directly from Instagram.
 *
 * @author    Zeeshan Ahmed <ziishaned@gmail.com>
 * @copyright 2017 Zeeshan Ahmed
 * @license   http://www.opensource.org/licenses/mit-license.html MIT License
 */
class Grabber
{
    
    /**
     * A link from where user wants to download the image or video.
     *
     * @var string
     */
    protected $url;

    /**
     * Link to download the media file.
     *
     * @var string
     */
    protected $fileUrl;

    /**
     * Meta tags that are avaliable in Instagram link.
     *
     * @var array
     */
    protected $metaTags = [];

    /**
     * @param string $url
     */
    public function __construct(string $url)
    {
        if (!$this->validateUrl($url)) {
            throw new Exception('Url is not valid.');
        }

        $this->url = $url;
        
        $this->getFile();

        $this->setFileUrl();
    }

    /**
     * Read the url value.
     *
     * @return string
     */
    public function getUrl() : string
    {
        return $this->url;
    }

    /**
     * Get the html from the Instagram link.
     *
     * @return void
     */
    public function getFile()
    {
        $response = file_get_contents($this->url);

        $this->setMetaTags($response);
    }

    /**
     * Set the url from where media file will be downloaded.
     */
    public function setFileUrl() : Grabber
    {
        if (array_key_exists('og:image', $this->metaTags) && !array_key_exists('og:video', $this->metaTags)) {
            $this->fileUrl = $this->metaTags['og:image'];
            return $this;
        }

        $this->fileUrl = $this->metaTags['og:video'];
        
        return $this;
    }

    /**
     * Get the download media file url.
     *
     * @return string
     */
    public function getFileUrl() : string
    {
        return $this->fileUrl;
    }

    /**
     * @param string $html
     */
    public function setMetaTags($html) : Grabber
    {
        $dom = new DOMDocument();
        @$dom->loadHTML($html);

        foreach ($dom->getElementsByTagName('meta') as $meta) {
            $this->metaTags[$meta->getAttribute('property')] = $meta->getAttribute('content');
        }

        return $this;
    }

    /**
     * @return array
     */
    public function getMetaTags() : array
    {
        return $this->metaTags;
    }

    /**
     * @return string
     */
    public function getDownloadUrl() : string
    {
        return $this->fileUrl;
    }

    /**
     * Directly download the media file.
     */
    public function download()
    {
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename=' . basename($this->fileUrl));
        header('Content-Transfer-Encoding: binary');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        readfile($this->fileUrl);
    }

    /**
     * Validate the url provided by the user.
     *
     * @param  string $url
     * @return bool
     */
    private function validateUrl(string $url) : bool
    {
        return filter_var($url, FILTER_VALIDATE_URL);
    }
}
