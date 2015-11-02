<?php

/**
 * @file
 * Contains \Drupal\brafton_importer\Form\BraftonForm.
 */

namespace Drupal\brafton_importer\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class for Brafton admin form.
 */
class BraftonForm extends ConfigFormBase {

  /**
   * Manually imports articles
   *
   * @return void
   */
  static function manual_import_articles() {
    $article_loader = new \Drupal\brafton_importer\Model\BraftonArticleLoader();
    $article_loader->run_article_loop(null);
  }

  static function manual_import_archive(array &$form, FormStateInterface $form_state) {
    $file_value = $form_state->getValue('brafton_archive_file');
    $file_id = $file_value[0];
    $file = file_load($file_id);
    $file_uri = $file->getFileUri();
    $file_url = drupal_realpath($file_uri);

    $article_loader = new \Drupal\brafton_importer\Model\BraftonArticleLoader();
    $article_loader->run_article_loop($file_url);
  }

  static function manual_import_videos() {
    $video_loader = new \Drupal\brafton_importer\Model\BraftonVideoLoader();
    $video_loader->import_videos();

  }

  /**
   * {@inheritdoc}
   *
   * New method to Drupal 8. Returns machine name of form.
   *
   * @return string The machine name of form.
   */
  public function getFormId() {
    return 'brafton_form';
  }

  /**
   * {@inheritdoc}
   *
   * Similar to Drupal 7. Builds up form.
   *
   * @param array $form The form object
   * @param FormStateInterface $form_state The FormStateInterface object
   *
   * @return array $form The build up form object
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
        1 => t('On'),
        0 => t('Off'),
      ),
      '#default_value' => $config->get('brafton_importer.brafton_general_switch'),
    );
    $form['brafton_general_options']['brafton_api_root'] = array(
      '#type' => 'select',
      '#title' => t( 'API Root' ),
      '#description' => t( 'The root domain of your Api key (i.e, api.brafton.com).' ),
      '#options' => array(
        'brafton.com' => 'Brafton',
        'contentlead.com' => 'ContentLEAD',
        'castleford.com.au' => 'Castleford',
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
      $form['brafton_general_options']['brafton_publish'] = array(
      '#type' => 'radios',
      '#title' => t( 'Publish Status.' ),
      '#options' => array(
        0 => 'Unpublished',
        1 => 'Published',
      ),
      '#default_value' => $config->get('brafton_importer.brafton_publish'),
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
    $form['brafton_article_options']['brafton_article_switch'] = array(
      '#type' => 'radios',
      '#title' => 'Article importer status',
      '#description' => 'Turn article importing on or off.',
      '#options' => array(
        1 => t('On'),
        0 => t('Off')
      ),
      '#default_value' => $config->get('brafton_importer.brafton_article_switch')
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
    $form['brafton_article_options']['brafton_publish_date'] = array(
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


    // Video Options
    $form['brafton_video_options'] = array(
      '#type' => 'details',
      '#title' => 'Video Options',
    );
    $form['brafton_video_options']['brafton_video_switch'] = array(
      '#type' => 'radios',
      '#title' => 'Video importer status',
      '#description' => 'Turn video importing on or off',
      '#options' => array(
        1 => t('On'),
        0 => t('Off')
      ),
      '#default_value' => $config->get('brafton_importer.brafton_video_switch')
    );
    $form['brafton_video_options']['brafton_video_public_key'] = array(
      '#type' => 'textfield',
      '#title' => 'Public Key',
      '#default_value' => $config->get('brafton_importer.brafton_video_public_key'),
    );
    $form['brafton_video_options']['brafton_video_private_key'] = array(
      '#type' => 'textfield',
      '#title' => 'Private Key',
      '#default_value' => $config->get('brafton_importer.brafton_video_private_key'),
    );
    $form['brafton_video_options']['brafton_video_feed_number'] = array(
      '#type' => 'textfield',
      '#title' => 'Feed Number',
      '#description' => t('Usually 0'),
      '#default_value' => $config->get('brafton_importer.brafton_video_feed_number'),
    );
    $form['brafton_video_options']['brafton_video_publish_date'] = array(
      '#type' => 'radios',
      '#title' => 'Publish date',
      '#description' => 'The date that the content is marked as having been published',
      '#options' => array(
        'published' => 'Published Date',
        'lastmodified' => 'Last Modified Date'
      ),
      '#default_value' => $config->get('brafton_importer.brafton_video_publish_date')
    );
    $form['brafton_video_options']['brafton_video_atlantis_switch'] = array(
      '#type' => 'radios',
      '#title' => t('Atlantis JS switch'),
      '#description' => t('Inject Atlantis JS into header or not. Needed for advanced video functionality like CTAs and sharing.'),
      '#options' => array(
        1 => t('On'),
        0 => t('Off'),
      ),
      '#default_value' => $config->get('brafton_importer.brafton_video_atlantis_switch'),
    );
    $form['brafton_video_options']['brafton_cta_switch'] = array(
      '#type' => 'radios',
      '#title' => t('CTA switch'),
      '#description' => t('Import video articles with CTAs or without.'),
      '#options' => array(
        1 => t('On'),
        0 => t('Off')
      ),
      '#default_value' => $config->get('brafton_importer.brafton_cta_switch'),
    );
    $form['brafton_video_options']['brafton_video_pause_cta_text'] = array(
      '#type' => 'textfield',
      '#title' => t( 'Atlantis Pause CTA Text' ),
      '#description' => t( 'Default video pause cta text every article imports' ),
      '#default_value' => $config->get( 'brafton_importer.brafton_video_pause_cta_text')
    );
    $form['brafton_video_options']['brafton_video_pause_cta_link'] = array(
        '#type' => 'textfield',
        '#title'    => t('Atlantis Pause Link'),
        '#description'  => t('Default video pause cta link'),
        '#default_value'   => $config->get('brafton_importer.brafton_video_pause_cta_link'),
    );
    $form['brafton_video_options']['brafton_video_pause_cta_asset_gateway_id'] = array(
        '#type' => 'textfield',
        '#title'    => t('Pause Asset Gateway ID'),
        '#description'  => t('Asset Gateay Form ID. disables pause link url'),
        '#default_value'   => $config->get('brafton_importer.brafton_video_pause_cta_asset_gateway_id'),
    );
    $form['brafton_video_options']['brafton_video_end_cta_title'] = array(
      '#type' => 'textfield',
      '#title' => t( 'Atlantis End CTA Title' ),
      '#description' => t( 'Default video end cta title every article imports' ),
      '#default_value' => $config->get('brafton_importer.brafton_video_end_cta_title'),
    );
    $form['brafton_video_options']['brafton_video_end_cta_subtitle'] = array(
      '#type' => 'textfield',
      '#title' => t( 'Atlantis End CTA Subtitle' ),
      '#description' => t( 'Default video end cta subtitle every article imports' ),
      '#default_value' => $config->get( 'brafton_importer.brafton_video_end_cta_subtitle'),
    );
    $form['brafton_video_options']['brafton_video_end_cta_link'] = array(
      '#type' => 'textfield',
      '#title' => t( 'Atlantis End CTA Link' ),
      '#description' => t( 'Default video end cta link every article imports. Requires http://' ),
      '#default_value' => $config->get( 'brafton_importer.brafton_video_end_cta_link'),
    );
    $form['brafton_video_options']['brafton_video_end_cta_asset_gateway_id'] = array(
        '#type' => 'textfield',
        '#title'    => t('End Asset Gateway ID'),
        '#description'  => t('Asset Gateay Form ID. disables end link url'),
        '#default_value'   => $config->get('brafton_importer.brafton_video_end_cta_asset_gateway_id'),
    );
    $form['brafton_video_options']['brafton_video_end_cta_text'] = array(
      '#type' => 'textfield',
      '#title' => t( 'Atlantis End CTA Text' ),
      '#description' => t( 'Default video end cta text every article imports' ),
      '#default_value' => $config->get( 'brafton_importer.brafton_video_end_cta_text'),
    );
    $form['brafton_video_options']['brafton_video_end_cta_button_image'] = array(
        '#type' => 'managed_file',
        '#title' => t( 'Ending CTA Button Image' ),
        '#description' => '<span class="actual_description">This is Optional and wil override the end cta text </span>',
        '#upload_location'  => 'public://',
        '#default_value'    => $config->get('brafton_importer.brafton_video_end_cta_button_image'),
    );
    $form['brafton_video_options']['brafton_video_end_cta_background'] = array(
        '#type' => 'managed_file',
        '#title' => t( 'Ending Background Image' ),
        '#description' => '<span class="actual_description">This is Optional</span>',
        '#upload_location'  => 'public://',
        '#default_value'    => $config->get('brafton_importer.brafton_video_end_cta_background'),
    );



    // Archive Controls
    $form['brafton_archive_options'] = array(
      '#type' => 'details',
      '#open' => TRUE,
      '#title' => 'Archive Uploads',
    );
    $form['brafton_archive_options']['brafton_archive_file'] = array(
      '#type' => 'managed_file',
      '#title' => t('Article Archive File'),
      '#description' => t('Upload an XML file'),
      '#upload_validators' => array(
        'file_validate_extensions' => array(0 => 'xml'),
      ),
    );
    $form['brafton_archive_options']['brafton_run_archive_importer'] = array(
      '#type' => 'submit',
      '#title' => 'Run Archive Importer',
      '#value' => 'Run Archive Importer',
      '#submit' => array('::manual_import_archive'),
    );

    // Manual Buttons
    $form['brafton_manual_options'] = array(
      '#type' => 'details',
      '#open' => TRUE,
      '#title' => 'Manual Control',
    );
    $form['brafton_manual_options']['brafton_run_importer'] = array(
      '#type' => 'submit',
      '#title' => 'Run Article Importer',
      '#value' => 'Run Article Importer',
      '#submit' => array('::manual_import_articles'),
    );
    $form['brafton_manual_options']['brafton_run_video_importer'] = array(
      '#type' => 'submit',
      '#title' => 'Run Video Importer',
      '#value' => 'Run Video Importer',
      '#submit' => array('::manual_import_videos'),
    );

    return $form;
  }

  /**
   * {@inheritdoc}
   *
   * Adds additional validation of .com email address.
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    if (strpos($form_state->getValue('email'), '.com') === FALSE) {
      $form_state->setErrorByName('email', $this->t('This is not a .com email address.'));
    }
  }





  /**
   * {@inheritdoc}
   *
   * Sets the admin configs for each field.
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = $this->config('brafton_importer.settings');

    // Permanently save the CTA images
    $file_value = $form_state->getValue('brafton_video_end_cta_button_image');
    if ($file_value) {
      $file = file_load($file_value[0]);
      $file_usage = \Drupal::service('file.usage');
      $file_usage->add($file, 'brafton_importer', 'node', $file->id());
      $config->set('brafton_importer.brafton_video_end_cta_button_image_url', $file->getFileUri());
    }

    $file_value = $form_state->getValue('brafton_video_end_cta_background');
    if ($file_value) {
      $file = file_load($file_value[0]);
      $file_usage = \Drupal::service('file.usage');
      $file_usage->add($file, 'brafton_importer', 'node', $file->id());
      $config->set('brafton_importer.brafton_video_end_cta_background_url', $file->getFileUri());
    }


    foreach( $form['brafton_general_options'] as $field => $field_value ) {
      $config->set('brafton_importer.' . $field, $form_state->getValue($field));
    }
    foreach( $form['brafton_article_options'] as $field => $field_value ) {
      $config->set('brafton_importer.' . $field, $form_state->getValue($field));
    }
    foreach( $form['brafton_video_options'] as $field => $field_value ) {
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
