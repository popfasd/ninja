<?php

namespace Popfasd\Ninja;

use MattFerris\HttpRouting\RequestInterface;

class Form
{
    /**
     * @var string
     */
    protected $id;

    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var array
     */
    protected $fields;

    /**
     * @var string
     */
    protected $cacheDir;

    /**
     * @var string
     */
    protected $nextUrl;

    /**
     * @param RequestInterface $request
     * @param $string $cacheDir
     */
    public function __construct(RequestInterface $request, $cacheDir)
    {
        $this->id = sha1($request->getHeader('Referer'));
        $this->request = $request;
        $this->cacheDir = $cacheDir.'/'.$this->id;

        $fields = $nexturl = null;
        if (file_exists($this->cacheDir)) {
            $settingsCache = $this->cacheDir.'/settings.php';
            if (file_exists($settingsCache)) {
                require($settingsCache);
            }
        } else {
            mkdir($this->cacheDir);
            $settings = "<?php\n\n// url: ".$this->request->getHeader('Referer')."\n";
            file_put_contents($this->cacheDir.'/settings.php', $settings);
        }

        $this->fields = $fields;
        $this->nextUrl = $nexturl;
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->request->getHeader('Referer');
    }

    /**
     * @return array
     */
    public function getFields()
    {
        return $this->fields;
    }

    /**
     * @param string $key
     * @return string
     */
    public function getFieldTitle($key)
    {
        $title = null;
        if (isset($this->fields[$key]) || array_key_exists($key, $this->fields)) {
            $title = $this->fields[$key];
        }
        return $title;
    }

    /**
     * @param string $key
     * @return bool
     */
    public function hasField($key)
    {
        $hasField = false;
        if (isset($this->fields[$key]) || array_key_exists($key, $this->fields)) {
            $hasField = true;
        }
        return $hasField;
    }

    /**
     * @return string
     */
    public function getCacheDir()
    {
        return $this->cacheDir;
    }

    /**
     * @return string
     */
    public function getNextUrl()
    {
        return $this->nextUrl;
    }

    /**
     * @return string The URL to forward to
     */
    public function process()
    {
        $post = $this->request->post();

        // populate default field names
        $fields = $this->fields;
        if (is_null($fields)) {
            $fields = ['__id' => 'Submission ID', '__ts' => 'Submission Timestamp'];
        }
        foreach (array_keys($post) as $k) {
            $fields[$k] = $k;
        }
        $this->fields = $fields;

        // pull wanted data from post values
        $data = [];
        foreach (array_keys($this->fields) as $k) {
            // ignore internal fields
            if (strpos($k, '__') === 0) {
                continue;
            }
            $data[$k] = $post[$k];
        }

        // generate submission ID and add field/data
        $submissionId = sha1($this->id.serialize($data));
 
        $submission = new Submission($submissionId, $this, $data);

        // raise submission processed event
        DomainEvents::dispatch(new SubmissionProcessedEvent($submission));
    }
}

