<?php

namespace Sandstorm\NeosH5P\Domain\Service;

use Neos\Flow\Annotations as Flow;
use Neos\Flow\Mvc\Controller\ControllerContext;
use Neos\Flow\Package\PackageManagerInterface;
use Neos\Flow\Security\Account;
use Neos\Flow\Security\Context;
use Sandstorm\NeosH5P\Domain\Model\Content;
use Sandstorm\NeosH5P\Domain\Model\ContentUserData;
use Sandstorm\NeosH5P\Domain\Repository\ContentRepository;
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
     * @var FrontendUserServiceInterface
     */
    protected $frontendUserService;

    /**
     * @Flow\Inject
     * @var PackageManagerInterface
     */
    protected $packageManager;

    /**
     * @Flow\Inject
     * @var Context
     */
    protected $securityContext;

    /**
     * @Flow\Inject
     * @var ContentUserDataRepository
     */
    protected $contentUserDataRepository;

    /**
     * @Flow\Inject
     * @var ContentRepository
     */
    protected $contentRepository;

    /**
     * Returns an array with a set of core settings that the H5P JavaScript needs
     * to do its thing. Can also include editor settings.
     *
     * @param ControllerContext $controllerContext
     * @param array<int> $displayContentIds content IDs for which display settings should be generated
     * @return array
     */
    public function getSettings(ControllerContext $controllerContext, array $displayContentIds = []): array
    {
        $coreSettings = $this->generateCoreSettings($controllerContext);
        foreach ($displayContentIds as $contentId) {
            $coreSettings['contents']['cid-' . $contentId] = $this->generateContentSettings($controllerContext, $contentId);
        }
        return $coreSettings;
    }

    /**
     * @param ControllerContext $controllerContext
     * @param int $editorContentId content ID for which editor settings should be generated
     * @return array
     */
    public function getSettingsWithEditor(ControllerContext $controllerContext, int $editorContentId = -1)
    {
        $coreSettings = $this->generateCoreSettings($controllerContext);
        $coreSettings['editor'] = $this->generateEditorSettings($controllerContext, $editorContentId);
        return $coreSettings;
    }

    /**
     * Merges the core scripts with all content scripts, so that there is one list of scripts that
     * can be passed to a template for inclusion.
     *
     * @param array $h5pIntegrationSettings
     * @return array
     */
    public function getMergedScripts(array $h5pIntegrationSettings): array
    {
        $scripts = $h5pIntegrationSettings['core']['scripts'];
        foreach ($h5pIntegrationSettings['contents'] as $contentSettings) {
            if (isset($contentSettings['scripts'])) {
                foreach ($contentSettings['scripts'] as $script) {
                    $scripts[] = $script;
                }
            }
        }
        return $scripts;
    }

    /**
     * Merges the core styles with all content styles, so that there is one list of styles that
     * can be passed to a template for inclusion.
     *
     * @param array $h5pIntegrationSettings
     * @return array
     */
    public function getMergedStyles(array $h5pIntegrationSettings): array
    {
        $scripts = $h5pIntegrationSettings['core']['styles'];
        foreach ($h5pIntegrationSettings['contents'] as $contentSettings) {
            if (isset($contentSettings['styles'])) {
                foreach ($contentSettings['styles'] as $script) {
                    $scripts[] = $script;
                }
            }
        }
        return $scripts;
    }

    /**
     * Returns an array with a set of core settings that the H5P JavaScript needs
     * to do its thing.
     *
     * @param ControllerContext $controllerContext
     * @return array
     */
    private function generateCoreSettings(ControllerContext $controllerContext): array
    {
        $currentAccount = $this->securityContext->getAccount();

        $saveResultAction = $this->buildUri(
            $controllerContext,
            'save',
            [],
            'Frontend\ContentResults',
            'Sandstorm.NeosH5P'
        );

        $saveUserDataAction = $this->buildUri(
            $controllerContext,
            'save',
            [],
            'Frontend\ContentUserData',
            'Sandstorm.NeosH5P'
        );

        $settings = [
            'baseUrl' => $this->getBaseUri($controllerContext),
            'url' => $this->h5pPublicFolderUrl,
            'postUserStatistics' => $this->h5pFramework->getOption('track_user') && $currentAccount !== null,
            'ajax' => [
                'setFinished' => $saveResultAction,
                'contentUserData' => $saveUserDataAction
            ],
            'saveFreq' => $this->h5pFramework->getOption('save_content_state') ? $this->h5pFramework->getOption('save_content_frequency') : false,
            'siteUrl' => $this->getBaseUri($controllerContext),
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

        // If we have a current user, pass his data to the frontend too so xAPI statements can be sent
        if ($currentAccount !== null) {
            $userData = $this->frontendUserService->getXAPIUserSettings($currentAccount);
            if ($userData !== null) {
                $settings['user'] = $userData;
            }
        }

        return $settings;
    }

    /**
     * Returns an array with a set of editor settings that the H5P JavaScript needs
     * to do its thing.
     *
     * @param ControllerContext $controllerContext
     * @param int $contentId provide this to set the "nodeVersionId" - needed to edit contents.
     * @return array
     */
    private function generateEditorSettings(ControllerContext $controllerContext, int $contentId = -1): array
    {
        $editorAjaxAction = $this->buildUri(
            $controllerContext,
            'index',
            [],
            'Backend\EditorAjax',
            'Sandstorm.NeosH5P'
        );

        $editorSettings = [
            'filesPath' => $this->h5pPublicFolderUrl . 'editor', // TODO - from settings - IS NOT h5p-editor but the editortempfiles dir!
            'fileIcon' => [
                'path' => $this->h5pPublicFolderUrl . $this->h5pEditorPublicFolderName . '/images/binary-file.png',
                'width' => 50,
                'height' => 50,
            ],
            'ajaxPath' => $editorAjaxAction . '/',
            'libraryUrl' => $this->getBaseUri($controllerContext) . $this->h5pPublicFolderUrl . $this->h5pEditorPublicFolderName,
            'copyrightSemantics' => $this->h5pContentValidator->getCopyrightSemantics(),
            'assets' => [
                'css' => array_merge($this->getRelativeCoreStyleUrls(), $this->getRelativeEditorStyleUrls()),
                'js' => array_merge($this->getRelativeCoreScriptUrls(), $this->getRelativeEditorScriptUrls())
            ],
            'apiVersion' => \H5PCore::$coreApi
        ];

        if ($contentId !== -1) {
            $editorSettings['nodeVersionId'] = $contentId;
        }

        return $editorSettings;
    }

    /**
     * Get settings for given content
     *
     * @param ControllerContext $controllerContext
     * @param int $contentId
     * @return array
     */
    private function generateContentSettings(ControllerContext $controllerContext, int $contentId)
    {
        /** @var Content $content */
        $content = $this->contentRepository->findOneByContentId($contentId);
        if ($content === null) {
            return [];
        }
        $contentArray = $content->toAssocArray();

        $embedUrl = $this->buildUri(
            $controllerContext,
            'index',
            ['contentId' => $contentId],
            'Frontend\ContentEmbed',
            'Sandstorm.NeosH5P'
        );

        $h5pCorePublicUrl = $this->h5pPublicFolderUrl . $this->h5pCorePublicFolderName;

        // Add JavaScript settings for this content
        $contentSettings = [
            'library' => \H5PCore::libraryToString($contentArray['library']),
            'jsonContent' => $content->getFiltered(),
            'fullScreen' => $contentArray['library']['fullscreen'],
            // TODO: implement once export is enabled
            'exportUrl' => 'foo',
            'embedCode' => '<iframe src="' . $embedUrl . '" width=":w" height=":h" frameborder="0" allowfullscreen="allowfullscreen"></iframe>',
            'resizeCode' => '<script src="' . $h5pCorePublicUrl . '/js/h5p-resizer.js' . '" charset="UTF-8"></script>',
            'url' => $this->getBaseUri($controllerContext), // TODO needed? admin_url('admin-ajax.php?action=h5p_embed&id=' . $contentArray['id']),
            'title' => $contentArray['title'],
            // TODO: use actual account identifier instead of 0 - this is needed only for an auth check, which we default to true currently.
            'displayOptions' => $this->h5pCore->getDisplayOptionsForView($contentArray['disable'], 0)
        ];

        // Get assets for this content
        $preloadedDependencies = $this->h5pCore->loadContentDependencies($content->getContentId(), 'preloaded');
        $files = $this->h5pCore->getDependenciesFiles($preloadedDependencies, $this->h5pPublicFolderUrl);
        $buildUrl = function (\stdClass $asset) {
            return $asset->path . $asset->version;
        };
        $contentSettings['scripts'] = array_map($buildUrl, $files['scripts']);
        $contentSettings['styles'] = array_map($buildUrl, $files['styles']);

        // Get preloaded user data for the current user, if we have one.
        $currentAccount = $this->securityContext->getAccount();
        if ($this->h5pFramework->getOption('save_content_state', false) && $currentAccount !== null) {
            $contentSettings['contentUserData'] = $this->getContentUserData($content, $currentAccount);
        }

        return $contentSettings;
    }

    /**
     * Generates the relative script urls the H5P JS expects in window.H5PIntegration.scripts.
     * Is needed for the window.H5PIntegration object and also to actually load these scripts into
     * the window as head scripts.
     *
     * @return array
     */
    private function getRelativeCoreScriptUrls(): array
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
    private function getRelativeCoreStyleUrls(): array
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
    private function getRelativeEditorScriptUrls(): array
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
    private function getRelativeEditorStyleUrls(): array
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
     * @param Content $content
     * @param Account $account
     * @return array
     */
    private function getContentUserData(Content $content, Account $account): array
    {
        $contentUserDatas = $this->contentUserDataRepository->findByContentAndAccount($content, $account);

        $userDataArray = [];
        if (count($contentUserDatas) === 0) {
            $userDataArray[] = ['state' => '{}'];
        } else {
            /** @var ContentUserData $contentUserData */
            foreach ($contentUserDatas as $contentUserData) {
                $subContentId = $contentUserData->getSubContent() !== null ? $contentUserData->getSubContent()->getContentId() : 0;
                $userDataArray[$subContentId][$contentUserData->getDataId()] = $contentUserData->getData();
            }
        }

        return $userDataArray;
    }

    /**
     * Extracts the base URI from the provided controller context.
     *
     * @param ControllerContext $cc
     * @return string
     */
    private function getBaseUri(ControllerContext $cc): string
    {
        return $cc->getRequest()->getMainRequest()->getHttpRequest()->getBaseUri()->__toString();
    }

    /**
     * Extracts the UriBuilder from the provided controller context, retrieves the main request, sets it on the uribuilder
     * so uris are built without the backend interfering (in case we're in a subrequest) and re-sets the original request
     * on the uribuilder so building can continue elsewhere in Neos.
     *
     * @param ControllerContext $controllerContext
     * @param string $actionName Name of the action to be called
     * @param array $controllerArguments Additional query parameters. Will be merged with $this->arguments.
     * @param string $controllerName Name of the target controller. If not set, current ControllerName is used.
     * @param string $packageKey Name of the target package. If not set, current Package is used.
     * @param string $subPackageKey Name of the target SubPackage. If not set, current SubPackage is used.
     * @return string the rendered URI
     */
    private function buildUri(ControllerContext $controllerContext, $actionName, $controllerArguments = [], $controllerName = null, $packageKey = null, $subPackageKey = null): string
    {
        // Get the main request for URI building
        $mainRequest = $controllerContext->getRequest()->getMainRequest();
        $uriBuilder = $controllerContext->getUriBuilder();
        // Temporarily set the request to the main request so we get the correct URI
        $uriBuilder->setRequest($mainRequest);
        $uri = $uriBuilder->reset()->setCreateAbsoluteUri(true)->uriFor(
            $actionName,
            $controllerArguments,
            $controllerName,
            $packageKey,
            $subPackageKey
        );
        // Reset the URIBuilder to the subrequest to not mess with the backend module routing
        $uriBuilder->setRequest($controllerContext->getRequest());

        return $uri;
    }
}
