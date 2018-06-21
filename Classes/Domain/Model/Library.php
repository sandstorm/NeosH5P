<?php
namespace Sandstorm\NeosH5P\Domain\Model;

use Doctrine\Common\Collections\Collection;
use Neos\Flow\Annotations as Flow;
use Doctrine\ORM\Mapping as ORM;

/**
 *
 *
 * id INT UNSIGNED NOT NULL AUTO_INCREMENT,
created_at TIMESTAMP NOT NULL,
updated_at TIMESTAMP NOT NULL,
name VARCHAR(127) NOT NULL,
title VARCHAR(255) NOT NULL,
major_version INT UNSIGNED NOT NULL,
minor_version INT UNSIGNED NOT NULL,
patch_version INT UNSIGNED NOT NULL,
runnable INT UNSIGNED NOT NULL,
restricted INT UNSIGNED NOT NULL DEFAULT 0,
fullscreen INT UNSIGNED NOT NULL,
embed_types VARCHAR(255) NOT NULL,
preloaded_js TEXT NULL,
preloaded_css TEXT NULL,
drop_library_css TEXT NULL,
semantics TEXT NOT NULL,
tutorial_url VARCHAR(1023) NOT NULL,
has_icon INT UNSIGNED NOT NULL DEFAULT 0,
PRIMARY KEY  (id),
KEY name_version (name,major_version,minor_version,patch_version),
KEY runnable (runnable)
 *
 *
 *
 */

/**
 * Class H5PLibrary
 * @package Sandstorm\NeosH5P\Domain\Model
 * @Flow\Entity
 */
class Library {

    /**
     * @Flow\Identity
     */
    protected $id;

    /**
     * @var string
     * @ORM\Column(nullable=false)
     */
    protected $createdAt;

    // -------------------- RELATIONS ----------------

    /**
     * @var Collection<H5PLibraryDependency>
     * @ORM\OneToMany(mappedBy="library", cascade={"persist", "remove"})
     */
    protected $libraryDependencies;

    /**
     * @var Collection<H5PLibraryTranslation>
     * @ORM\OneToMany(mappedBy="library", cascade={"persist", "remove"})
     */
    protected $libraryTranslations;

}
