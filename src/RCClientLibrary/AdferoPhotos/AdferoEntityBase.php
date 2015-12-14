<?php

/**
 * @file
 * Contains Drupal\brafton_importer\RCClientLibrary\AdferoPhotos\AdferoEntityBase
 */

namespace Drupal\brafton_importer\RCClientLibrary\AdferoPhotos;

/**
 * Entity base class
 *
 *
 */
abstract class AdferoEntityBase {

    /**
     * @var int
     */
    public $id;

    public function getId() {
        return $this->id;
    }

    public function setId($id) {
        $this->id = $id;
    }

}

?>
