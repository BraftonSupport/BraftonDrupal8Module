<?php

namespace Drupal\brafton_importer\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Block\BlockPluginInterface;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides a Brafton Category block.
 *
 * @Block(
 *  id = "brafton_category_block",
 *  admin_label = @Translation("Brafton Category block"),
 * )
 */
class BraftonCategoryBlock extends BlockBase implements BlockPluginInterface {

  /**
   * {@inheritdoc}
   */
  public function build() {

    // Procedural code - for OO code, inject the TermStorage object.
    $terms = \Drupal::entityManager()->getStorage('taxonomy_term')->loadTree('brafton_tax', 0, NULL, TRUE);

    $content = '<ul>';

    foreach ($terms as $term) {
      $content .= '<li><a href="' . $term->url() . '">' . $term->getName() . '</a></li>';
    }
    $content .= '</ul>';

    return array(
      '#markup' => $content,
    );
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $form = parent::blockForm($form, $form_state);

    $config = $this->getConfiguration();

    $form['brafton_categories_block_config'] = array(
      '#type' => 'textfield',
      '#title' => 'Name',
      '#default_value' => isset($config['name']) ? $config['name'] : ''
    );

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    $this->setConfigurationValue('name', $form_state->getValue('brafton_categories_block_config'));
  }

}

?>
