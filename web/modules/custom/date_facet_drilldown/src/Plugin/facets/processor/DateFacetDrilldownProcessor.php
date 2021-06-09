<?php

namespace Drupal\date_facet_drilldown\Plugin\facets\processor;

use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\facets\FacetInterface;
use Drupal\facets\Processor\BuildProcessorInterface;
use Drupal\facets\Processor\ProcessorPluginBase;
use Drupal\facets\Result\Result;

/**
 * Provides a processor that formats date facets.
 *
 * @FacetsProcessor(
 *   id = "date_facet_drilldown",
 *   label = @Translation("Date facet drilldown"),
 *   description = @Translation("Date facets - allow drilldown to month from year"),
 *   stages = {
 *     "build" = 1
 *   }
 * )
 */
class DateFacetDrilldownProcessor extends ProcessorPluginBase implements BuildProcessorInterface {

  /**
   * Build facets.
   *
   * @param Drupal\facets\FacetInterface $facet
   *   The facet.
   * @param array $results
   *   The current results array.
   */
  public function build(FacetInterface $facet, array $results) {
    $month_facets = FALSE;
    foreach ($results as $id => $result) {
      if (preg_match('/^\d{4}-\d{2}$/', $id)) {
        // Facet key format is YYYY-MM, so we need to format
        // this as full month name followed by year
        // e.g. 'January 2020'.
        $dateTime = new DrupalDateTime();
        $facet_date = $dateTime::createFromFormat('Y-m-d\TH:i:s', $id . '-01T00:00:00');
        $result->setDisplayValue($facet_date->format('F Y'));
        $month_facets = TRUE;
      }
    }
    if ($month_facets) {
      // In this case we are drilled down to the month level and
      // need to create an additional active year facet.
      $facetActiveItems = $facet->getActiveItems();
      $activeItem = array_pop($facetActiveItems);
      $this->createActiveFacet($facet, $activeItem, $results);
    }
    return $results;
  }

  /**
   * Utility function.
   *
   * Create an active year facet.
   *
   * @param Drupal\facets\FacetInterface $facet
   *   The facet.
   * @param string $activeItem
   *   The current active item.
   * @param array $results
   *   The results array.
   */
  private function createActiveFacet(FacetInterface $facet, string $activeItem, array &$results) {
    $matches = [];
    // Take YYYY from the start of the active item.
    if (preg_match('/^(\d{4})/', $activeItem, $matches)) {
      $year = $matches[0];
      // Create new YYYY active facet.
      $results[$year] = new Result($facet, $year, $year, 0);
      $results[$year]->setActiveState(TRUE);
    }
  }

}
