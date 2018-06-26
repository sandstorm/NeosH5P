<?php

namespace Sandstorm\NeosH5P\H5PAdapter\Editor;

use Neos\Flow\Annotations as Flow;

/**
 * @Flow\Scope("singleton")
 */
class H5PEditorFactory
{
    /**
     * @var \H5PCore
     * @Flow\Inject(lazy=false)
     */
    protected $h5pCore;

    public function getEditor() : \H5peditor
    {
        return new \H5peditor(
            $this->h5pCore,
            new EditorFileAdapter(),
            new EditorAjax()
        );
    }
}
