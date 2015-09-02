<?php

/**
 * @file
 * Contains Drupal\brafton_importer\APIClientLibrary\XMLLoadException
 */

namespace Drupal\brafton_importer\APIClientLibrary;
/**
 * Custom Exception XMLLoadException thrown if an XML source file is not found
 * @package SamplePHPApi
 */
class XMLLoadException extends XMLException{
  function __construct($message, $code=""){
    $this->message = "Could not load URL: " . $message;
  }
}

?>
