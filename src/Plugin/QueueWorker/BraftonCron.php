<?php

/**
 * @file
 * Contains \Drupal\brafton_importer\Plugin\QueueWorker\BraftonCron.
 */

namespace Drupal\brafton_importer\Plugin\QueueWorker;

use Drupal\Core\Queue\QueueWorkerBase;

/**
 * Updates a feed's items.
 *
 * @QueueWorker(
 *   id = "brafton_cron",
 *   title = @Translation("Brafton Cron"),
 *   cron = {"time" = 60}
 * )
 */
class BraftonCron extends QueueWorkerBase {

  /**
   * {@inheritdoc}
   */
  public function processItem($data) {


  }

}
