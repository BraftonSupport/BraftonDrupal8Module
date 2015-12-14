<?php

/**
 * @file
 * Contains Drupal\brafton_importer\RCClientLibrary\AdferoArticlesVideoExtensions\VideoOutputs\AdferoVideoOutput
 */

namespace Drupal\brafton_importer\RCClientLibrary\AdferoArticlesVideoExtensions\VideoOutputs;

use Drupal\brafton_importer\RCClientLibrary\AdferoArticles\AdferoEntityBase;

include_once dirname(__FILE__) . '/../../AdferoArticles/AdferoEntityBase.php';

/**
 * Represents a video output.
 *
 *
 */
class AdferoVideoOutput extends AdferoEntityBase {

    /**
     * @var string
     */
    public $type;

    /**
     * @var int
     */
    public $width;

    /**
     * @var int
     */
    public $height;

    /**
     * @var string
     */
    public $path;
}

?>
