<?php

namespace Drupal\brafton_importer\Plugin\Block;

//use Drupal\Core\Access\AccessResult;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Block\BlockPluginInterface;
use Drupal\Core\Form\FormStateInterface;
//use Drupal\Core\Session\AccountInterface;

/**
 * Provides a 'Demo' block.
 *
 * @Block(
 *  id = "brafton_block",
 *  admin_label = @Translation("Brafton block"),
 * )
 */
class BraftonBlock extends BlockBase implements BlockPluginInterface {
  /**
   * {@inheritdoc}
   */
  public function build() {
    $config = $this->getConfiguration();

    if (!empty($config['name'])) {
      $name = $config['name'];
    }
    else {
      $name = $this->t('to no one');
    }

    return array(
      '#markup' => $this->t('Hello @name', array(
        '@name' => $name,
        )
      ),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $form = parent::blockForm($form, $form_state);

    $config = $this->getConfiguration();

    $form['brafton_block_name'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Who'),
      '#description' => $this->t('Who do you want to say hello to?'),
      '#default_value' => isset($config['name']) ? $config['name'] : ''
    );

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    $this->setConfigurationValue('name', $form_state->getValue('brafton_block_name'));
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    $default_config = \Drupal::config('brafton_importer.settings');
    return array(
      'name' => $default_config->get('brafton_importer.name')
    );
  }



  /**
   * {@inheritdoc}
   */

  /*
  public function access(AccountInterface $account) {
    return $account->hasPermission('access content');
  }


  public function blockAccess(AccountInterface $account, $return_as_object = FALSE) {
    return AccessResult::allowedIfHasPermission($account, 'access content');
  }
  */
}
?>
