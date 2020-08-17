<?php

namespace Drupal\uregni_breadcrumbs;

/**
 * @file
 * Generates the breadcrumb trail for search page(s)
 */
use Drupal\Core\Breadcrumb\Breadcrumb;
use Drupal\Core\Breadcrumb\BreadcrumbBuilderInterface;
use Drupal\Core\Controller\TitleResolverInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Link ;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * {@inheritdoc}
 */
class PublicationViewPageBreadcrumb implements BreadcrumbBuilderInterface {

  /**
   * Symfony\Component\HttpFoundation\RequestStack definition.
   *
   * @var Symfony\Component\HttpFoundation\RequestStack
   */
  protected $request;

  /**
   * The title resolver.
   *
   * @var \Drupal\Core\Controller\TitleResolverInterface
   */
  protected $titleResolver;

  /**
   * Class constructor.
   */
  public function __construct(RequestStack $request, TitleResolverInterface $title_resolver) {
    $this->request = $request;
    $this->titleResolver = $title_resolver;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('request_stack'),
      $container->get('title_resolver')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function applies(RouteMatchInterface $route_match) {
//    // You can put any logic here. You must return a BOOLEAN TRUE or FALSE.
//    //-----[ BEGIN example ]-----
//    // Get all parameters.
//    $parameters = $route_match->getParameters()->all();
//    return $parameters;
//
//    // Determine if the current page is a node page
//    if (isset($parameters['node']) && !empty($parameters['node'])) {
//      return TRUE;
//    }
//    //-----[ END example ]-----
//
//    // Still here? This does not apply.
//    return FALSE;
    $match = FALSE;
    $route_name = $route_match->getRouteName();

      if ($route_name == 'view.publications_search.publication_search_page') {
        $match = TRUE;
      }

    return $match;

  }

  /**
   * {@inheritdoc}
   */
  public function build(RouteMatchInterface $route_match) {
    $breadcrumb = new Breadcrumb();
    $title_resolver = $this->titleResolver->getTitle($this->request->getCurrentRequest(), $route_match->getRouteObject());
    $links[] = Link::createFromRoute(t('Home'), '<front>');
    $links[] = Link::fromTextandUrl(t('About us'), Url::fromUri('entity:node/53'));
    $links[] = Link::createFromRoute($title_resolver, '<none>');
    $breadcrumb->setLinks($links);
    $breadcrumb->addCacheContexts(['url.path']);

    return $breadcrumb;
  }
}
