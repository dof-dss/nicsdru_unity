<?php

// @codingStandardsIgnoreFile

// Multi site configuration for Platform.sh.

$platformsh = new \Platformsh\ConfigReader\Config();

if (!$platformsh->inRuntime()) {
  return;
}

// The following block adds a $sites[] entry for each subdomain that is defined
// in routes.yaml.
// Note that the route retrieved will be fully expanded at this point (so the region,
// project id, branch name will be already filled in for dev sites).
// Note also that the corresponding directory in sites will be just up to the first dot
// in the domain name, so the route "https://uregni.gov.uk.{default}/" will correspond
// to the sites/uregni directory.
foreach ($platformsh->getUpstreamRoutes($platformsh->applicationName) as $route) {
  $host = parse_url($route['url'], PHP_URL_HOST);
  if ($host !== FALSE) {
    // At this point, host may be in either of these two forms:
    //  - www.fiscalcommissionni.org
    //  - hatecrimereviewni.org.uk.master-7rqtwti-6tlkpwbr6tndk.uk-1.platformsh.site
    $newhost = str_replace('www.','',$host);
    $subdomain = substr($newhost, 0, strpos($newhost, '.'));
    $sites[$host] = $subdomain;
  }
}
