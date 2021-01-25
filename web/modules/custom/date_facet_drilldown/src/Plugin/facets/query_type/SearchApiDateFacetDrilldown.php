<?php

namespace Drupal\date_facet_drilldown\Plugin\facets\query_type;

use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\facets\QueryType\QueryTypeRangeBase;

/**
 * Support for date facets within the Search API scope.
 *
 * This query type supports dates for all possible backends. This specific
 * implementation of the query type supports a generic solution of adding facets
 * for dates.
 *
 * @FacetsQueryType(
 *   id = "search_api_datefacetdrilldown",
 *   label = @Translation("Date facet drilldown"),
 * )
 */
class SearchApiDateFacetDrilldown extends QueryTypeRangeBase {

  /**
   * @inheritdoc
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
  }

  /**
   * Returns a start and end date based on a unix timestamp.
   *
   * This method returns a start and end date with an absolute interval, based
   * on the granularity set in the widget.
   *
   * @param int $value
   *   Unix timestamp.
   *
   * @return array
   *   An array with a start and end date as unix timestamps.
   *
   * @throws \Exception
   *   Thrown when creating a date fails.
   */
  public function calculateRange($value) {
    $dateTime = new DrupalDateTime();
    if (preg_match('/^\d{4}$/', $value)) {
      // This must be a year (YYYY).
      $startDate = $dateTime::createFromFormat('Y-m-d\TH:i:s', $value . '-01-01T00:00:00');
      $stopDate = $dateTime::createFromFormat('Y-m-d\TH:i:s', $value . '-12-31T23:59:59');
    }
    else {
      // This must be a year and month (YYYY-MM).
      $startDate = $dateTime::createFromFormat('Y-m-d\TH:i:s', $value . '-01T00:00:00');
      // Use format('t') to retrieve the number of days in the month.
      $stopDate = $dateTime::createFromFormat('Y-m-d\TH:i:s', $value . '-' . $startDate->format('t') . 'T23:59:59');
    }
    return [
      'start' => $startDate->format('U'),
      'stop' => $stopDate->format('U'),
    ];
  }

  /**
   * Calculates the result of the filter.
   *
   * @param int $value
   *   A unix timestamp.
   *
   * @return array
   *   An array with a start and end date as unix timestamps.
   */
  public function calculateResultFilter(int $value) {
    // Create a new DrupalDateTime from the value passed in.
    $date = new DrupalDateTime();
    $date->setTimestamp($value);

    // Decide whether to format the facets as years or
    // years/months depending on how many facets are active.
    $active_items = $this->facet->getActiveItems();
    if (isset($active_items) && (count($active_items) > 0)) {
      $format = 'Y-m';
    }
    else {
      $format = 'Y';
    }

    return [
      'display' => $date->format($format),
      'raw' => $date->format($format),
    ];
  }

}
