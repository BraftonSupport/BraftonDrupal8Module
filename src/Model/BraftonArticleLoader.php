<?php

/**
 * @file
 * Contains Drupal\brafton_importer\Model\BraftonFeedLoader
 */

namespace Drupal\brafton_importer\Model;

use Drupal\brafton_importer\APIClientLibrary\ApiHandler;

/**
 * The class for articles. Child of BraftonFeedLoader.
 */
class BraftonArticleLoader extends BraftonFeedLoader{

    protected $article_date_setting;
    protected $article_author_id;

    /**
     * Constructor function. Is this necessary?
     *
     * @return void
     */
    public function __construct(){
        parent::__construct();
        $this->article_date_setting = $this->brafton_config->get('brafton_importer.brafton_publish_date');
        $this->article_author_id = $this->brafton_config->get('brafton_importer.brafton_article_author');
    }

    /**
    * Final method used to import articles.
    *
    * @param string $archive_url The local file url for an uploaded XML archive. Null for non-archive article importing.
    *
    * @return void
    */
    public function import_articles($archive_url) {
        $this->errors->set_section('Import Article master method');
        $article_array = $this->get_article_feed($archive_url);
        $this->run_article_loop($article_array);




    }
    /**
     * Get the Articles feed using the XML API.
     *
     * @param string $archive_url The local file url for an uploaded XML archive. Null for non-archive article importing.
     *
     * @return array $article_array Array containing individual NewsItem Objects.
     */
    public function get_article_feed($archive_url) {
      $this->errors->set_section('Loading article feed');

      if ($archive_url) {
        $article_array = \Drupal\brafton_importer\APIClientLibrary\NewsItem::getNewsList( $archive_url,'html' );
      }
      else{
        $feed = new ApiHandler($this->API_key, 'http://api.' . $this->domain);
        $article_array = $feed->getNewsHTML();
      }
      return $article_array;
    }

    /**
     * Loops through articles and saves them as Drupal nodes
     *
     * @param array $article_array Array containing individual NewsItem Objects.
     *
     * @return void
     */
    public function run_article_loop($article_array){
      $counter = 0;
      $import_list = array('items' => array(), 'counter' => $counter);

      foreach ($article_array as $article) {
        $brafton_id = $article->getId();
        $this->errors->set_section('Individual Article loop for '.$brafton_id);
        $existing_posts = $this->brafton_post_exists($brafton_id);

        if ( $this->overwrite == 1 && !empty($existing_posts) ) {
          $nid = reset($existing_posts);
          $new_node = \Drupal\node\Entity\Node::load($nid);
        }
        elseif (empty($existing_posts)) {
          $new_node = \Drupal\node\Entity\Node::create(array('type' => 'brafton_article'));
        }
        else {
          continue;
        }

        $author_id = $this->get_author($article->getByLine());
        $date = $this->get_article_date($article);
        $category_names = $this->get_article_tax_names($article->getCategories());
        $category_ids = $this->load_tax_terms($category_names);
        $title = $article->getHeadline();
        $body = $article->getText();
        $summary = ( !empty($article->getExtract()) ? $article->getExtract() : $article->getHtmlMetaDescription() );
        if (!empty($article->getPhotos())) {
          $image = $this->get_article_image($article->getPhotos()[0]);
          $new_node->field_brafton_image = system_retrieve_file( $image['url'], NULL, TRUE, FILE_EXISTS_REPLACE );
          $new_node->field_brafton_image->alt = $image['alt'];
        }
        $new_node->status = $this->publish_status;
        $new_node->title = $title;
        $new_node->uid = $author_id;
        $new_node->created = strtotime($date);
        $new_node->field_brafton_body = array(
          'value' => $body,
          'summary' => $summary,
          'format' => 'full_html'
        );
        $new_node->field_brafton_id = $brafton_id;
        $new_node->field_brafton_term = $category_ids;

        $new_node->save();

        $import_list['items'][] = array(
          'title' => $title,
          'url' => $new_node->url()
        );
        ++$counter;
      }

      $import_list['counter'] = $counter;

      $this->display_import_message($import_list);

    }

    /**
     * Retrieves article image information as array.
     *
     * @param object $images The first image associated with an article
     *
     * @return array $image_info Array with url, alt, caption of image.
     */
    public function get_article_image($image) {
      $loop_section = $this->errors->get_section();
      $this->errors->set_section('Getting article image');

      if(!empty($image)) {
        $image_large = $image->getLarge();
        $image_info = array(
          'url' => $image_large->getUrl(),
          'alt' => $image->getAlt(),
          'title' => $image->getCaption()
        );
      } else{
        $image_info = null;
      }

      $this->errors->set_section($loop_section);
      return $image_info;
    }

    /**
     * Gets the publish date for article based on chosen config
     *
     * @param object $article An individual article from the XML feed
     *
     * @return string $date The date in string form.
     */
    public function get_article_date($article) {
      $loop_section = $this->errors->get_section();
      $this->errors->set_section('Getting article date');
      switch($this->article_date_setting) {
        case 'published':
          $date = $article->getPublishDate();
          break;
        case 'created':
          $date = $article->getCreatedDate();
          break;
        case 'lastmodified':
          $date = $article->getLastModifiedDate();
          break;
        default:
          $date = $article->getPublishDate();
      }
      $this->errors->set_section($loop_section);
      return $date;
    }

  /**
   * Gets the category names for a single article and returns array of strings.
   *
   * @param array $categories Array of article category objects
   *
   * @return array $name_array Array of strings (category names).
   */
  public function get_article_tax_names($categories) {
    $loop_section = $this->errors->get_section();
    $this->errors->set_section('Getting article category names.');
    $name_array = array();
    if ($this->category_switch == 'on') {
      foreach($categories as $category) {
        $name_array[] = $category->getName();
      }
    }

    $this->errors->set_section($loop_section);
    return $name_array;
  }

}
