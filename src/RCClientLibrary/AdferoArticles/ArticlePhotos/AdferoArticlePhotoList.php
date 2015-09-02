<?php

/**
 * @file
 * Contains Drupal\brafton_importer\RCClientLibrary\AdferoArticles\ArticlePhotos\AdferoArticlePhotoList
 */

namespace Drupal\brafton_importer\RCClientLibrary\AdferoArticles\ArticlePhotos;

include_once dirname(__FILE__) . '/../AdferoListBase.php';

/**
 * Represents a list of article photos
 *
 */
class AdferoArticlePhotoList extends AdferoListBase {

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
