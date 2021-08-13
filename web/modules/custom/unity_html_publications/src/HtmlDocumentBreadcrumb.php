<?php

namespace Drupal\unity_html_publications;

use Drupal\Core\Breadcrumb\Breadcrumb;
use Drupal\Core\Breadcrumb\BreadcrumbBuilderInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Link;
use Drupal\node\NodeInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Generates the breadcrumb trail for HTML publications.
 *
 * Breadcrumb format:
 * Home > Publications > Publication gateway page
 *
 * @package Drupal\unity_html_publications
 */
class HtmlDocumentBreadcrumb implements BreadcrumbBuilderInterface {

  /**
   * Drupal entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Node object, or null if on a non-node page.
   *
   * @var \Drupal\node\Entity\Node
   */
  protected $node;

  /**
   * Class constructor.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager) {
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function applies(RouteMatchInterface $route_match) {
    $match = FALSE;

    $route_name = $route_match->getRouteName();

    // Full node view.
    if ($route_name === 'entity.node.canonical') {
      $this->node = $route_match->getParameter('node');
    }

    // Editorial preview.
    if ($route_name === 'entity.node.preview') {
      $this->node = $route_match->getParameter('node_preview');
    }

    if (!empty($this->node)) {

      if ($this->node instanceof NodeInterface == FALSE) {
        $this->node = $this->entityTypeManager->getStorage('node')->load($this->node);
      }

      if (!empty($this->node)) {
        $match = $this->node->bundle() === 'html_document';
      }
    }

    return $match;
  }

  /**
   * {@inheritdoc}
   */
  public function build(RouteMatchInterface $route_match) {
    $breadcrumb = new Breadcrumb();

    $links = [];

    if ($this->node instanceof NodeInterface) {
      $links[] = Link::createFromRoute(t('Home'), '<front>');
      $links[] = Link::createFromRoute(t('Publications'), 'view.publications_search.publication_search_page');

      $pub_gateway_node = $this->getPublicationGatewayNode($this->node);
      if ($pub_gateway_node instanceof NodeInterface) {
        $links[] = Link::createFromRoute(t($pub_gateway_node->label()), 'entity.node.canonical', ['node' => $pub_gateway_node->id()]);
      }
    }

    $breadcrumb->setLinks($links);
    $breadcrumb->addCacheContexts(['url.path']);

    return $breadcrumb;
  }

  protected function getPublicationGatewayNode(NodeInterface $html_document_node) {
    $references = \Drupal::service('whatlinkshere.linkmanager')->getReferenceContent($html_document_node, 1, 0);
    $pub_gw_node = $this->entityTypeManager->getStorage('node')->load($references['rows'][0]['nid']);

    return $pub_gw_node;
  }

}
