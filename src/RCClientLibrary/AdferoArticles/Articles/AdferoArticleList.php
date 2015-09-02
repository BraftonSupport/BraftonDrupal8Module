<?php

/**
 * @file
 * Contains Drupal\brafton_importer\RCClientLibrary\AdferoArticles\Articles\AdferoArticleList
 */

namespace Drupal\brafton_importer\RCClientLibrary\AdferoArticles\Articles;

include_once dirname(__FILE__) . '/../AdferoListBase.php';

/**
 * Represents a list of articles
 *
 */
class AdferoArticleList extends AdferoListBase {

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
