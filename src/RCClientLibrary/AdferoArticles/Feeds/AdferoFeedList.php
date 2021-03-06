<?php

/**
 * @file
 * Contains Drupal\brafton_importer\RCClientLibrary\AdferoArticles\Feeds\AdferoFeedList
 */

namespace Drupal\brafton_importer\RCClientLibrary\AdferoArticles\Feeds;

use Drupal\brafton_importer\RCClientLibrary\AdferoArticles\AdferoListBase;

include_once dirname(__FILE__) . '/../AdferoListBase.php';

/**
 * Represents a list of feeds
 *
 */
class AdferoFeedList extends AdferoListBase {

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
