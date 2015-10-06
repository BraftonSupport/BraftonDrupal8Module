<?php

/**
 * @file
 * Contains Drupal\brafton_importer\RCClientLibrary\AdferoArticles\Briefs\AdferoBrief
 */

namespace Drupal\brafton_importer\RCClientLibrary\AdferoArticles\Briefs;

use Drupal\brafton_importer\RCClientLibrary\AdferoArticles\AdferoEntityBase;

include_once dirname(__FILE__) . '/../AdferoEntityBase.php';

/**
 * Represents a brief.
 *
 *
 */
class AdferoBrief extends AdferoEntityBase {

    /**
     * @var string
     */
    public $name;

    /**
     * @var int
     */
    public $feedId;

}

?>
