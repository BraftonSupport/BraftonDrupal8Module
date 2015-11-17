<?php
/**
 * @file
 * Contains \Drupal\brafton_importer\Controller\BraftonImporterController
 */

namespace Drupal\brafton_importer\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * BraftonImporterController
 */
class BraftonImporterController extends ControllerBase {

  /**
   * Provides the Brafton config form.
   *
   * @return object $form The Brafton config form.
   */
  public function content() {
    $form = \Drupal::formBuilder()->getForm('Drupal\brafton_importer\Form\BraftonForm');
    return $form;
  }

}

?>
