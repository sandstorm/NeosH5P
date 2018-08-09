<?php

namespace Sandstorm\NeosH5P\Controller\Backend;

use Neos\Flow\Annotations as Flow;
use Neos\Error\Messages\Message;
use Neos\Flow\Mvc\View\ViewInterface;
use Neos\Neos\Controller\Module\AbstractModuleController;
use Sandstorm\NeosH5P\H5PAdapter\Core\H5PFramework;

class AdministrationController extends AbstractModuleController
{
    /**
     * @var \H5PCore
     * @Flow\Inject(lazy=false)
     */
    protected $h5pCore;

    /**
     * @var H5PFramework
     * @Flow\Inject
     */
    protected $h5pFramework;

    public function indexAction()
    {
        // convert the timestamp into a datetime object so we can format it nicely in the frontend
        $lastContentTypeCacheUpdate = $this->h5pFramework->getOption('content_type_cache_updated_at');
        if ($lastContentTypeCacheUpdate) {
            $datetime = new \DateTime();
            $datetime->setTimestamp($lastContentTypeCacheUpdate);
            $lastContentTypeCacheUpdate = $datetime;
        }

        $this->view->assign('settings', [
            'site_uuid' => $this->h5pFramework->getOption('site_uuid'),
            'content_type_cache_updated_at' => $lastContentTypeCacheUpdate,
            'send_usage_statistics' => $this->h5pFramework->getOption('send_usage_statistics'),
            'track_user' => $this->h5pFramework->getOption('track_user'),
            'save_content_state' => $this->h5pFramework->getOption('save_content_state'),
            'save_content_frequency' => $this->h5pFramework->getOption('save_content_frequency'),
            'hub_is_enabled' => $this->h5pFramework->getOption('hub_is_enabled'),
            'enable_lrs_content_types' => $this->h5pFramework->getOption('enable_lrs_content_types'),
            'frame' => $this->h5pFramework->getOption('frame'),
            'export' => $this->h5pFramework->getOption('export'),
            'embed' => $this->h5pFramework->getOption('embed'),
            'copyright' => $this->h5pFramework->getOption('copyright'),
            'icon' => $this->h5pFramework->getOption('icon')
        ]);
    }

}
