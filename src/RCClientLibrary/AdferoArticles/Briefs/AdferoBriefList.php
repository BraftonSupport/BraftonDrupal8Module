<?php

/**
 * @file
 * Contains Drupal\brafton_importer\RCClientLibrary\AdferoArticles\Briefs\AdferoBriefList
 */

namespace Drupal\brafton_importer\RCClientLibrary\AdferoArticles\Briefs;

use Drupal\brafton_importer\RCClientLibrary\AdferoArticles\AdferoListBase;

include_once dirname(__FILE__) . '/../AdferoListBase.php';

/**
 * Represents a list of briefs
 *
 */
class AdferoBriefList extends AdferoListBase {

    /**
     * @var array
     */
    public $items = array();

    public function getItems() {
        return $this->items;
    }

    public function setItems($items) {
        $this->items = $items;
    }

}

?>
