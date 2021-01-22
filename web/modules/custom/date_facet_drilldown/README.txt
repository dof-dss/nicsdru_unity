This module is built for one purpose only - to allow yearly date facets to drill-down to month level.
(This functionality was available in Drupal 7 but has not yet been provided in Drupal8.)

The impressive facet_granular_date module does implement this functionality but unfortunately does not work
with facet 'pretty paths'.

The idea of this module is to be as simple as possible - there is no need to change the source of your facets,
you can just leave the URL processor as 'pretty paths'.

Usage
To use this module, just install it and then edit the facet that you wish to use it for. You should use the
'Date item processor' and set the granularity to 'year'.
You will see a new checkbox labelled 'Date facet drilldown' so just check this and the drilldown should be available.


