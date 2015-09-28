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


  static function manual_import_articles(array $form, FormStateInterface $form_state) {

    $article_loader = new \Drupal\brafton_importer\Model\BraftonArticleLoader();
    $article_loader->import_articles();

  }




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



    $connection = \Drupal\Core\Database\Database::getConnection();
    $results = $connection->query("SELECT uid, name FROM {users_field_data} WHERE status=1");
    $user_array = $results->fetchAllKeyed();

    // db_query is deprecated.
  //  $results = db_query( "SELECT uid, name FROM {users_field_data} WHERE status=1" );
  //  $user_array = $results->fetchAllKeyed();

    //Add option for getting dynamic author.
    //0 is also the id for anonymous author as a fall back if no author is set in the feed
    $user_array[0] = 'Get Author from Article';

    // General Options
    $form['brafton_general_options'] = array(
      '#type' => 'details',
      '#title' => 'General Options',
      '#description' => t('Configure the Brafton Importer here.'),
    );

    $form['brafton_general_options']['brafton_general_switch'] = array(
      '#type' => 'radios',
      '#title' => t('Master Importer Status'),
      '#description' => t('Turn the importer on or off globally.'),
      '#options' => array(
        'on' => t('On'),
        'off' => t('Off'),
      ),
      '#default_value' => $config->get('brafton_importer.brafton_general_switch'),
    );

    $form['brafton_general_options']['brafton_feed_type'] = array(
      '#type' => 'select',
      '#title' => t( 'Type of Content' ),
      '#description' => t( 'The type(s) of content you are importing.' ),
      '#options' => array(
        'articles' => 'Articles',
        'videos' => 'Videos',
        'both' => 'Both',
      ),
      '#prefix' => '<h2>Choose Content Types</h2>',
      '#default_value' => $config->get('brafton_importer.brafton_feed_type')
    );
    $form['brafton_general_options']['brafton_api_root'] = array(
      '#type' => 'select',
      '#title' => t( 'API Root' ),
      '#description' => t( 'The root domain of your Api key (i.e, api.brafton.com).' ),
      '#options' => array(
        'http://api.brafton.com' => 'Brafton',
        'http://api.contentlead.com' => 'ContentLEAD',
        'http://api.castleford.com.au' => 'Castleford',
      ),
      '#default_value' => $config->get('brafton_importer.brafton_api_root'),
    );
    $form['brafton_general_options']['brafton_author'] = array(
      '#type' => 'select',
      '#title' => t( 'Content Author' ),
      '#description' => t( 'The author of the content.' ),
      '#options' => $user_array,
      '#default_value' =>$config->get('brafton_importer.brafton_author'),
      '#prefix' => '<h2>Import Options</h2>',
    );
    $form['brafton_general_options']['brafton_publish_date'] = array(
      '#type' => 'radios',
      '#title' => t( 'Publish Date' ),
      '#description' => t( 'The date that the content is marked as having been published.' ),
      '#options' => array(
        'published' => 'Published Date',
        'created' => 'Created Date',
        'lastmodified' => 'Last Modified Date',
      ),
      '#default_value' => $config->get('brafton_importer.brafton_publish_date'),
    );
    $form['brafton_general_options']['brafton_category_switch'] = array(
      '#type' => 'radios',
      '#title' => t('Brafton Categories'),
      '#description' => t('Use Brafton categories or not.'),
      '#options' => array(
        'on' => t('On'),
        'off' => t('Off'),
      ),
      '#default_value' => $config->get('brafton_importer.brafton_category_switch'),
    );
    $form['brafton_general_options']['brafton_overwrite'] = array(
      '#type' => 'checkbox',
      '#title' => t( 'Overwrite any changes made to existing content.' ),
      '#default_value' => $config->get('brafton_importer.brafton_overwrite'),
    );
      $form['brafton_general_options']['brafton_published'] = array(
      '#type' => 'checkbox',
      '#title' => t( 'Import Content as unpublished.' ),
      '#default_value' => $config->get('brafton_importer.brafton_published'),
    );
    $form['brafton_general_options']['email'] = array(
      '#type' => 'email',
      '#title' => $this->t('Your .com email address.'),
      '#default_value' => $config->get('brafton_importer.email')
    );

    // Article Options

    $form['brafton_article_options'] = array(
      '#type' => 'details',
      '#title' => 'Article Options',
    );
    $form['brafton_article_options']['brafton_api_key'] = array(
      '#type' => 'textfield',
      '#title' => t( 'Api Key' ),
      '#description' => t( 'Your API key (of the format xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx).' ),
      '#default_value' => $config->get('brafton_importer.brafton_api_key'),
      '#size' => 36,
      '#maxlength' => 36,
          '#prefix'   => 'Options in this section apply to Articles ONLY.  Videos have seperate options'
    );

    // Manual Buttons

    $form['brafton_manual_options'] = array(
      '#type' => 'details',
      '#open' => TRUE,
      '#title' => 'Manual Control & Archive Uploads',
    );
    $form['brafton_manual_options']['brafton_run_importer'] = array(
      '#type' => 'submit',
      '#title' => 'Run Article Importer',
      '#value' => 'Run Article Importer',
      '#submit' => array('\Drupal\brafton_importer\BraftonImporterService::createBraftonArticle'),
   //   \Drupal::service('brafton_importer.brafton_importer_service')->createBraftonArticle();
     // '#submit' => array('::createBraftonArticle'),
    );
    $form['brafton_manual_options']['brafton_run_importer2'] = array(
      '#type' => 'submit',
      '#title' => 'Run Article Importer 2',
      '#value' => 'Run Article Importer 2',
      '#submit' => array('::manual_import_articles'),
   //   '#submit' => array('::createBraftonArticle2'),

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

    foreach( $form['brafton_general_options'] as $field => $field_value ) {
      $config->set('brafton_importer.' . $field, $form_state->getValue($field));
    }
    foreach( $form['brafton_article_options'] as $field => $field_value ) {
      $config->set('brafton_importer.' . $field, $form_state->getValue($field));
    }

  //  $config->set('brafton_importer.' . 'email', $form_state->getValue('email'));
  //  $config->set('brafton_importer.brafton_feed_type', $form_state->getValue('brafton_feed_type'));
  //  $config->set('brafton_importer.brafton_api_root', $form_state->getValue('brafton_api_root'));
    $config->save();

  //  drupal_set_message($this->t('Your email address is @email', array('@email' => $form_state->getValue('email'))));

   // $this->braftonImporterService->createBraftonArticle();




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
