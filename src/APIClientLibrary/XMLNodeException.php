<?php

/**
 * @file
 * Contains Drupal\brafton_importer\APIClientLibrary\XMLNodeException
 */

namespace Drupal\brafton_importer\APIClientLibrary;
/**
 * Custom Exception XMLNodeException thrown if a required XML element is not found
 * @package SamplePHPApi
 */
class XMLNodeException extends XMLException{
  function __construct($message, $code=""){
    $this->message = "Could not find XMLNode: " . $message;
  }

?>
