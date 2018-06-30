<?php

namespace Sandstorm\NeosH5P\Domain\Service;

use Neos\Flow\Annotations as Flow;
use Neos\Flow\Package\PackageManagerInterface;
use Neos\Neos\Domain\Service\UserService;
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
     * @Flow\InjectConfiguration(package="Neos.Flow", path="http.baseUri")
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
     * @Flow\Inject
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
     * Returns an array with a set of core settings that the H5P JavaScript needs
     * to do its thing.
     *
     * @return array
     */
    public function getCoreSettings(): array
    {
        $currentUser = $this->userService->getCurrentUser();

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
        $editorSettings = [
            'filesPath' => $this->h5pPublicFolderUrl . $this->h5pEditorPublicFolderName,
            'fileIcon' => [
                'path' => $this->h5pPublicFolderUrl . $this->h5pEditorPublicFolderName . '/images/binary-file.png',
                'width' => 50,
                'height' => 50,
            ],
            'ajaxPath' => '', // TODO admin_url('admin-ajax.php?token=' . wp_create_nonce('h5p_editor_ajax') . '&action=h5p_'),
            'libraryUrl' => '', // TODO - huh ? plugin_dir_url('h5p/h5p-editor-php-library/h5peditor.class.php'),
            'copyrightSemantics' => $this->h5pContentValidator->getCopyrightSemantics(),
            'assets' => [
                'css' => $this->getRelativeEditorStyleUrls(),
                'js' => $this->getRelativeEditorScriptUrls()
            ],
            'deleteMessage' => 'Are you sure you wish to delete this content?',
            'apiVersion' => \H5PCore::$coreApi
        ];

        if($contentId !== null) {
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
            $urls[] = $this->h5pPublicFolderUrl . $this->h5pEditorPublicFolderName . '/' . $script . $this->getCacheBuster();
            /**
             * TODO: The following is in WP, not sure if needed...
             */
            /**
             * // We do not want the creator of the iframe inside the iframe
             * if ($script !== 'scripts/h5peditor-editor.js') {
             * $assets['js'][] = $url . $script . $cache_buster;
             * }
             * */
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
}
