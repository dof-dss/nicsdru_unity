<?php

namespace Drupal\unity_search_pages\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Block\BlockManagerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Controller for setting browser tab title.
 */
class SearchPagesController extends ControllerBase {

   /**
   * Controller callback for the page title.
   *
   * Use this to examine route parameters/any other conditions
   * and vary the string that is returned.
   *
   * @return string
   *   The page title.
   */
  public function getTitle($route = null) {
    if ($route == null) {
      $route = \Drupal::routeMatch()->getRouteName();
    }

    $facet = \Drupal::request()->get('facets_query');
    $title = \Drupal::request()->get('_title');
    $search = \Drupal::request()->query->all();

    if ($route == 'search.view') {
      if (!empty($search)) {
        return t($title . ' results');
      }
      else {
        return $title;
      }
    }
    else {
      if (!empty($facet) || !empty($search)) {
        return t($title . ' - search results');
      }
      else {
        return $title;
      }
    }
  }
}
