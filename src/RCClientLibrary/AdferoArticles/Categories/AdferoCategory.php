<?php

/**
 * @file
 * Contains Drupal\brafton_importer\RCClientLibrary\AdferoArticles\Categories\AdferoCategory
 */

namespace Drupal\brafton_importer\RCClientLibrary\AdferoArticles\Categories;

use Drupal\brafton_importer\RCClientLibrary\AdferoArticles\AdferoEntityBase;

include_once dirname(__FILE__) . '/../AdferoEntityBase.php';

/**
 * Represents a category.
 *
 *
 */
class AdferoCategory extends AdferoEntityBase {

    /**
     * @var string
     */
    public $name;

    /**
     * @var int
     */
    public $parentId;

}

?>
