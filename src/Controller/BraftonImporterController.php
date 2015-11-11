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

  protected $braftonImporterService;

  /**
   * Class constructor
   */
  public function __construct($braftonImporterService) {
    $this->braftonImporterService = $braftonImporterService;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('brafton_importer.brafton_importer_service')
    );
  }

  public function content() {

    $form = \Drupal::formBuilder()->getForm('Drupal\brafton_importer\Form\BraftonForm');

  //  $form['#attached']['library'][] = 'brafton_importer/brafton_css';
  //  debug($form);

    return $form;


/*
    $test = \Drupal::entityManager()->getStorage('field_storage_config')->load('field_brafton_image');
    $test2 = 'yeah buddy';
    debug($test);
    return array(
      '#markup' => t('Hello @value', array(
        '@value' => $test
      ))
    );
*/

    /*
    return array(
      '#type' => 'markup',
      '#markup' => t('Hello world'),
    );
    */
/*
    return array(
      '#markup' => t('Hello @value', array(
        '@value' => $this->braftonImporterService->getDemoValue()
      ))
    );
*/

  }
}

?>
