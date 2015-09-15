<?php

/**
 * @file
 * Contains \Drupal\brafton_importer\Form\BraftonForm.
 */

namespace Drupal\brafton_importer\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;



class BraftonForm extends ConfigFormBase {

  // For calling Service
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
  // End calling service

  /**
   * {@inheritdoc}
   *
   * New method to Drupal 8. Returns machine name of form.
   */
  public function getFormId() {
    return 'brafton_form';
  }

  /**
   * {@inheritdoc}
   *
   * Similar to Drupal 7. Builds up form.
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildForm($form, $form_state);

    $config = $this->config('brafton_importer.settings');

    $form['email'] = array(
      '#type' => 'email',
      '#title' => $this->t('Your .com email address.'),
      '#default_value' => $config->get('brafton_importer.email_address')
    );
    /*
    $form['manual_button'] = array(
      '#type' => 'submit',
      '#title' => 'Run article importer',
      '#value' => 'Run article importer',
      '#submit' => $this->braftonImporterService->createBraftonArticle()
    );
    */





    /*
    // Submit button. Redundant b/c of submitForm() function.
    $form['show'] = array(
      '#type' => 'submit',
      '#value' => $this->t('Submit')
    );
    */

  //  $output = \Drupal::entityManager()->getStorage('entity_view_display')->loadByProperties(array('targetEntityType' => 'node', 'bundle' => 'brafton_article4'));
  //  $output = \Drupal::entityManager()->getStorage('entity_view_display')->loadByProperties(array('field_name' => 'field_brafton_image'));
  //  $output = entity_get_form_display('node', 'brafton_article4', 'default');
  //  debug($output);

/*
  \Drupal::entityManager()->getStorage('entity_view_display')
  ->setComponent('field_brafton_image', array(
      'type' => 'image',
      'settings' => array(
      ),
      'weight' => 5,
*/




    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    if (strpos($form_state->getValue('email'), '.com') === FALSE) {
      $form_state->setErrorByName('email', $this->t('This is not a .com email address.'));
    }
  }




  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = $this->config('brafton_importer.settings');
    $config->set('brafton_importer.email_address', $form_state->getValue('email'));
    $config->save();

    drupal_set_message($this->t('Your email address is @email', array('@email' => $form_state->getValue('email'))));

    $this->braftonImporterService->createBraftonArticle();




    return parent::submitForm($form, $form_state);
  }


  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'brafton_importer.settings',
    ];
  }
}

?>
