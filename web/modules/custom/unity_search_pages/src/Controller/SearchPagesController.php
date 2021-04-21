<?php

namespace Drupal\unity_search_pages\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Routing\CurrentRouteMatch;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Controller for setting browser tab title.
 */
class SearchPagesController extends ControllerBase implements ContainerInjectionInterface {

  /**
   * The current route match.
   *
   * @var \Drupal\Core\Routing\CurrentRouteMatch
   */
  protected $routeMatch;


  /**
   * The current request stack.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $request;

  /**
   * Creates a new ModerationStateConstraintValidator instance.
   *
   * @param \Drupal\Core\Routing\CurrentRouteMatch $route_match
   *   The entity type manager.
   * @param \Symfony\Component\HttpFoundation\RequestStack $request
   *   The current request.
   */
  public function __construct(CurrentRouteMatch $route_match, RequestStack $request) {
    $this->routeMatch = $route_match;
    $this->request = $request->getCurrentRequest();
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('current_route_match'),
      $container->get('request_stack')
    );
  }


  /**
   * Controller callback for the page title.
   *
   * Use this to examine route parameters/any other conditions
   * and vary the string that is returned.
   *
   * @return string
   *   The page title.
   */
  public function getTitle($route = NULL) {
    if ($route === NULL) {
      $route =  $this->routeMatch->getRouteName();
    }

    $facet = $this->request->get('facets_query');
    $title = $this->request->get('_title');
    $search = $this->request->query->all();

    if ($route === 'search.view') {
      if (!empty($search)) {
        return t('@title results', ['@title' => $title]);
      }
      else {
        return $title;
      }
    }
    else {
      if (!empty($facet) || !empty($search)) {
        return t('@title - search results', ['@title' => $title]);
      }
      else {
        return $title;
      }
    }
  }

}
