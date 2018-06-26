<?php
namespace Sandstorm\NeosH5P\H5PAdapter\Core;

use Sandstorm\NeosH5P\H5PAdapter\Core\Traits\AuthorizationTrait;
use Sandstorm\NeosH5P\H5PAdapter\Core\Traits\ContentModelTrait;
use Sandstorm\NeosH5P\H5PAdapter\Core\Traits\ExtensionModelTrait;
use Sandstorm\NeosH5P\H5PAdapter\Core\Traits\HttpRequestTrait;
use Sandstorm\NeosH5P\H5PAdapter\Core\Traits\FileTrait;
use Sandstorm\NeosH5P\H5PAdapter\Core\Traits\LibraryModelTrait;
use Sandstorm\NeosH5P\H5PAdapter\Core\Traits\SystemTrait;
use Neos\Flow\Annotations as Flow;

/**
 * @Flow\Scope("singleton")
 */
class H5PFramework implements \H5PFrameworkInterface {
    use SystemTrait;
    use FileTrait;
    use AuthorizationTrait;
    use HttpRequestTrait;
    use ContentModelTrait;
    use ExtensionModelTrait;
    use LibraryModelTrait;
}
