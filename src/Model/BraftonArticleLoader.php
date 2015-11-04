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

    protected $API_key;

    /**
     * Constructor function. Is this necessary?
     *
     * @return void
     */
    public function __construct(){
        parent::__construct();
        $this->API_key = $this->brafton_config->get('brafton_importer.brafton_api_key');
    }

    /**
     * Method for loading feed. Because so basic, perhaps it should be property?
     *
     * @return object $feed of class ApiHandler
     */
    public function load_article_feed(){
        $feed = new ApiHandler($this->API_key, 'http://api.' . $this->domain);
        return $feed;
    }

    /**
     * Loops through articles and saves them as Drupal nodes
     *
     * @param string $archive_url The local file url for an uploaded XML archive. Null for non-archive article importing.
     *
     * @return void
     */
    public function run_article_loop($archive_url){

      $counter = 0;
      $import_list = array('items' => array(), 'counter' => $counter);

      if ($archive_url) {
        $article_array = \Drupal\brafton_importer\APIClientLibrary\NewsItem::getNewsList( $archive_url,'html' );
      }
      else{
        $feed = $this->load_article_feed();
        $article_array = $feed->getNewsHTML();
      }

      foreach ($article_array as $article) {

        $brafton_id = $article->getId();
        $existing_posts = $this->brafton_post_exists($brafton_id);
        $overwrite = $this->brafton_config->get('brafton_importer.brafton_overwrite');
        if ( $overwrite == 1 && !empty($existing_posts) ) {
          $nid = reset($existing_posts);
          $new_node = \Drupal\node\Entity\Node::load($nid);
        }
        elseif (empty($existing_posts)) {
          $new_node = \Drupal\node\Entity\Node::create(array('type' => 'brafton_article'));
        }
        else {
          continue;
        }

        $publish_status = $this->brafton_config->get('brafton_importer.brafton_publish');
        $author_id = $this->get_article_author($article);
        $date = $this->get_article_date($article);
    //    $categories = $this->get_taxonomy_terms($article);
        $category_names = $this->get_article_tax_names($article);
        $category_ids = $this->load_tax_terms($category_names);
        $title = $article->getHeadline();
        $body = $article->getText();
        $summary = $article->getExtract();
        $image = $this->get_article_image($article);

        $new_node->status = $publish_status;
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
        $new_node->field_brafton_image = system_retrieve_file( $image['url'], NULL, TRUE, FILE_EXISTS_REPLACE );
        $new_node->field_brafton_image->alt = $image['alt'];

        $new_node->save();

        $import_list['items'][] = array(
          'title' => $title,
          'url' => $new_node->url()
        );
        $counter = $counter + 1;
      }

      $import_list['counter'] = $counter;

      $this->display_import_message($import_list);

    }


    /**
     *  Gets the author of the article based on configs.
     *
     * @param object $article An individual article from the XML feed
     *
     * @return int $author_id The drupal user ID for the author.
     */
    public function get_article_author($article) {
      $author_id = $this->brafton_config->get('brafton_importer.brafton_article_author');
      // static existing drupal user chosen.
      if ($author_id != 0) {
        return $author_id;
      }
      // user selects Dynamic Authorship
      else {
        $byline = $article->getByLine();
      //  $byline = 'juicy';
        // if byline exists
        if (!empty($byline)) {
          $user = user_load_by_name($byline);
          // if user exists
          if ($user) {
            return $user->id();
          }
          else {
            //create user programatically
            $password = user_password(8);
            $fields = array(
                'name' => $byline,
                'mail' => $byline.rand().'@example.com',
                'pass' => $password,
                'status' => 1,
                'init' => 'email address',
                'roles' => array(
                  DRUPAL_AUTHENTICATED_RID => 'authenticated user',
                ),
              );
            $new_user = \Drupal\user\Entity\User::create($fields);
            $new_user->save();
            return $new_user->id();
          }
        }
        else {
          return $author_id;
        }
      }
    }


    /**
     * Retrieves article image information as array.
     *
     * @param object $article The individual article object from the XML API.
     *
     * @return array $image_info Array with url, alt, caption of image.
     */
    public function get_article_image($article) {
      $images = $article->getPhotos()[0];
      if(!empty($images)) {
        $image_large = $images->getLarge();
        $image_info = array(
          'url' => $image_large->getUrl(),
          'alt' => $images->getAlt(),
          'title' => $images->getCaption()
        );
      } else{
        $image_info = null;
      }
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
      $date_setting = $this->brafton_config->get('brafton_importer.brafton_publish_date');
      switch($date_setting) {
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
      return $date;
    }

  /**
   * Gets the category names for a single article and returns array of strings.
   *
   * @param object $article The article object from XML API.
   *
   * @return array $name_array Array of strings (category names).
   */
  public function get_article_tax_names($article) {
    if ($this->brafton_config->get('brafton_importer.brafton_category_switch') == 'off') {
      return array();
    }
    $name_array = array();
    $categories = $article->getCategories();
    foreach($categories as $category) {
      $name_array[] = $category->getName();
    }
    return $name_array;
  }


}
