<?php

namespace Sandstorm\NeosH5P\Domain\Service;

use Neos\Flow\Annotations as Flow;
use Neos\Flow\Mvc\Routing\Exception\MissingActionNameException;
use Neos\Flow\Mvc\Routing\UriBuilder;
use Neos\Flow\Package\PackageManagerInterface;
use Neos\Flow\Security\Account;
use Neos\Neos\Domain\Service\UserService;
use Sandstorm\NeosH5P\Domain\Model\Content;
use Sandstorm\NeosH5P\Domain\Model\ContentUserData;
use Sandstorm\NeosH5P\Domain\Repository\ContentUserDataRepository;
use Sandstorm\NeosH5P\H5PAdapter\Core\H5PFramework;

/**
 * Responsible for pulling h5p settings and content/library values from the db
 * and generating the object that will be set as window.H5PIntegration on the client side.
 *
 * @Flow\Scope("singleton")
 */
class H5PIntegrationService
{
    /**
     * @Flow\InjectConfiguration(path="http.baseUri", package="Neos.Flow")
     * @var string
     */
    protected $baseUri;

    /**
     * @Flow\InjectConfiguration(path="h5pPublicFolder.url")
     * @var string
     */
    protected $h5pPublicFolderUrl;

    /**
     * @Flow\InjectConfiguration(path="h5pPublicFolder.subfolders.core")
     * @var string
     */
    protected $h5pCorePublicFolderName;

    /**
     * @Flow\InjectConfiguration(path="h5pPublicFolder.subfolders.editor")
     * @var string
     */
    protected $h5pEditorPublicFolderName;

    /**
     * @Flow\Inject
     * @var H5PFramework
     */
    protected $h5pFramework;

    /**
     * @Flow\Inject(lazy=false)
     * @var \H5PCore
     */
    protected $h5pCore;

    /**
     * @Flow\Inject
     * @var \H5PContentValidator
     */
    protected $h5pContentValidator;

    /**
     * @Flow\Inject
     * @var UserService
     */
    protected $userService;

    /**
     * @Flow\Inject
     * @var PackageManagerInterface
     */
    protected $packageManager;

    /**
     * @Flow\Inject
     * @var UriBuilder
     */
    protected $uriBuilder;

    /**
     * @Flow\Inject
     * @var ContentUserDataRepository
     */
    protected $contentUserDataRepository;

    /**
     * Returns an array with a set of core settings that the H5P JavaScript needs
     * to do its thing.
     *
     * @return array
     */
    public function getCoreSettings(): array
    {
        $currentUser = $this->userService->getCurrentUser();

        // TODO: $this->>baseUri is null here - probably because of the subrequest thing in the Neos BE. how to get it?
        $this->baseUri = 'http://127.0.0.1:8081/';

        $settings = [
            'baseUrl' => $this->baseUri,
            'url' => $this->h5pPublicFolderUrl,
            'postUserStatistics' => $this->h5pFramework->getOption('track_user') && $currentUser !== null,
            'ajax' => [
                // TODO: set this to the correct routes for the Frontend\ContentAjaxController
                // in wp looks like: http://127.0.0.1:8081/wp-admin/admin-ajax.php?token=bc3d523a30&action=h5p_setFinished
                'setFinished' => $this->baseUri,
                // in wp looks like: http://127.0.0.1:8081/wp-admin/admin-ajax.php?token=19c5088239&action=h5p_contents_user_data&content_id=:contentId&data_type=:dataType&sub_content_id=:subContentId"
                // !!!! mind the placeholders !!!
                'contentUserData' => $this->baseUri
            ],
            'saveFreq' => $this->h5pFramework->getOption('save_content_state') ? $this->h5pFramework->getOption('save_content_frequency') : false,
            'siteUrl' => $this->baseUri,
            'l10n' => [
                'H5P' => $this->h5pCore->getLocalization(),
            ],
            'hubIsEnabled' => $this->h5pFramework->getOption('hub_is_enabled') == 1,
            'reportingIsEnabled' => $this->h5pFramework->getOption('enable_lrs_content_types') == 1,
            'core' => [
                'scripts' => $this->getRelativeCoreScriptUrls(),
                'styles' => $this->getRelativeCoreStyleUrls()
            ]
        ];

        // If we have a current user, pass his data to the frontend too
        if ($currentUser !== null) {
            $settings['user'] = [
                // TODO: we will have to expose the way user settings are injected here, because packages using our
                // plugin might use different user models than the Neos model.
                'name' => $currentUser->getName()->getFullName(),
                'mail' => $currentUser->getElectronicAddresses()->first()
            ];
        }

        return $settings;
    }

    /**
     * Returns an array with a set of editor settings that the H5P JavaScript needs
     * to do its thing.
     *
     * @param null $contentId provide this to set the "nodeVersionId" - needed to edit contents.
     * @return array
     */
    public function getEditorSettings($contentId = null): array
    {
        // TODO: $this->>baseUri is null here - probably because of the subrequest thing in the Neos BE. how to get it?
        $this->baseUri = 'http://127.0.0.1:8081/';

        // TODO: issues with plugin uri rewrite by PluginUriAspect
//        try {
//            $editorAjaxAction = $this->uriBuilder->reset()->setCreateAbsoluteUri(true)->uriFor(
//                'index',
//                [],
//                'Backend\EditorAjax',
//                'Sandstorm.NeosH5P'
//            );
//        } catch (MissingActionNameException $ex) {
//            // swallow, never happens
//        }

        $editorSettings = [
            'filesPath' => $this->h5pPublicFolderUrl . 'editor', // TODO - from settings
            'fileIcon' => [
                'path' => $this->h5pPublicFolderUrl . $this->h5pEditorPublicFolderName . '/images/binary-file.png',
                'width' => 50,
                'height' => 50,
            ],
            'ajaxPath' => 'http://127.0.0.1:8081/neosh5p/editor/',
            'libraryUrl' => $this->baseUri . $this->h5pPublicFolderUrl . $this->h5pEditorPublicFolderName,
            'copyrightSemantics' => $this->h5pContentValidator->getCopyrightSemantics(),
            'assets' => [
                'css' => array_merge($this->getRelativeCoreStyleUrls(), $this->getRelativeEditorStyleUrls()),
                'js' => array_merge($this->getRelativeCoreScriptUrls(), $this->getRelativeEditorScriptUrls())
            ],
            'apiVersion' => \H5PCore::$coreApi
        ];

        if ($contentId !== null) {
            $editorSettings['nodeVersionId'] = $contentId;
        }

        return $editorSettings;
    }

    /**
     * Generates the relative script urls the H5P JS expects in window.H5PIntegration.scripts.
     * Is needed for the window.H5PIntegration object and also to actually load these scripts into
     * the window as head scripts.
     *
     * @return array
     */
    public function getRelativeCoreScriptUrls(): array
    {
        $urls = [];
        foreach (\H5PCore::$scripts as $script) {
            $urls[] = $this->h5pPublicFolderUrl . $this->h5pCorePublicFolderName . '/' . $script . $this->getCacheBuster();
        }
        return $urls;
    }

    /**
     * Generates the relative style urls the H5P JS expects in window.H5PIntegration.styles.
     * Is needed for the window.H5PIntegration object and also to actually load these styles into
     * the window as head styles.
     *
     * @return array
     */
    public function getRelativeCoreStyleUrls(): array
    {
        $urls = [];
        foreach (\H5PCore::$styles as $style) {
            $urls[] = $this->h5pPublicFolderUrl . $this->h5pCorePublicFolderName . '/' . $style . $this->getCacheBuster();
        }
        return $urls;
    }

    /**
     * Generates the relative script urls the H5P JS expects in window.H5PIntegration.editor.assets.js.
     * Is needed for the window.H5PIntegration object and also to actually load these scripts into
     * the window as head scripts.
     *
     * @return array
     */
    public function getRelativeEditorScriptUrls(): array
    {
        $urls = [];
        foreach (\H5peditor::$scripts as $script) {
            /**
             * We do not want the creator of the iframe inside the iframe.
             * If we loaded this, the iframe would continually try to load more iframes inside itself.
             * This is a bug in the H5P integration (or rather a weird way of declaring the libraries)
             */
            if (strpos($script, 'scripts/h5peditor-editor.js') !== false) {
                continue;
            }
            $urls[] = $this->h5pPublicFolderUrl . $this->h5pEditorPublicFolderName . '/' . $script . $this->getCacheBuster();
        }

        // Add language script - english only for now
        $urls[] = $this->h5pPublicFolderUrl . $this->h5pEditorPublicFolderName . '/language/en.js';

        return $urls;
    }

    /**
     * Generates the relative style urls the H5P JS expects in window.H5PIntegration.editor.assets.css.
     * Is needed for the window.H5PIntegration object and also to actually load these styles into
     * the window as head styles.
     *
     * @return array
     */
    public function getRelativeEditorStyleUrls(): array
    {
        $urls = [];
        foreach (\H5peditor::$styles as $style) {
            $urls[] = $this->h5pPublicFolderUrl . $this->h5pEditorPublicFolderName . '/' . $style . $this->getCacheBuster();
        }
        return $urls;
    }

    protected function getCacheBuster(): string
    {
        $neosH5PPackage = $this->packageManager->getPackage('Sandstorm.NeosH5P');
        return "?v=" . $neosH5PPackage->getInstalledVersion();
    }

    /**
     * Get settings for given content
     *
     * @param Content $content
     * @return array
     */
    public function getContentSettings(Content $content)
    {
        $contentArray = $content->toAssocArray();

        // TODO: $this->>baseUri is null here - probably because of the subrequest thing in the Neos BE. how to get it?
        $this->baseUri = 'http://127.0.0.1:8081/';

        $h5pCorePublicUrl = $this->h5pPublicFolderUrl . $this->h5pCorePublicFolderName;

        // Add JavaScript settings for this content
        $contentSettings = [
            'library' => \H5PCore::libraryToString($contentArray['library']),
            // TODO: filterParameters if filtered is empty
            'jsonContent' => $content->getFiltered(), //$this->h5pCore->filterParameters($contentArray),
            'fullScreen' => $contentArray['library']['fullscreen'],
            // TODO: implement once export is enabled
            'exportUrl' => 'foo',
            // TODO: implement once iframe embedding is enabled
            'embedCode' => '<iframe src="embed-url-for-content-here-once-implemented" width=":w" height=":h" frameborder="0" allowfullscreen="allowfullscreen"></iframe>',
            'resizeCode' => '<script src="' . $h5pCorePublicUrl . '/js/h5p-resizer.js' . '" charset="UTF-8"></script>',
            'url' => $this->baseUri, // TODO needed? admin_url('admin-ajax.php?action=h5p_embed&id=' . $contentArray['id']),
            'title' => $contentArray['title'],
            // TODO: use actual account identifier instead of 0 - this is needed only for an auth check, which we default to true currently.
            'displayOptions' => $this->h5pCore->getDisplayOptionsForView($contentArray['disable'], 0),
            'contentUserData' => [
                0 => ['state' => '{}']
            ]
        ];

        // Get preloaded user data for the current user, if we have one.
        // TODO - we will have to expose this to packages integrating us, as they might be using a
        // frontend user model which doesnt get picked up by the Neos UserService.
        $currentUser = $this->userService->getCurrentUser();
        if ($this->h5pFramework->getOption('save_content_state', false) && $currentUser !== null) {
            $contentSettings['contentUserData'] = $this->getContentUserData($content, $currentUser->getAccounts()->first());
        }

        return $contentSettings;
    }

    /**
     * @param Content $content
     * @param Account $account
     * @return array
     */
    protected function getContentUserData(Content $content, Account $account): array
    {
        $contentUserDatas = $this->contentUserDataRepository->findBy([
            'content' => $content,
            'account' => $account
        ]);

        $userDataArray = [
            0 => ['state' => '{}']
        ];

        /** @var ContentUserData $contentUserData */
        foreach ($contentUserDatas as $contentUserData) {
            $settings['contentUserData'][$contentUserData->getSubContent()->getContentId()][$contentUserData->getDataId()] = $contentUserData->getData();
        }
        return $userDataArray;
    }

}
