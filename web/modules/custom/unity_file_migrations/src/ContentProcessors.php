<?php

namespace Drupal\unity_file_migrations;

/**
 * Class ContentExtractors.
 *
 * @package Drupal\unity_file_migrations
 */
class ContentProcessors {

  /**
   * Extracts link elements from html after the string 'More useful links'.
   *
   * @param array $content
   *   Drupal content field array.
   *
   * @return array
   *   Array of Drupal Link field values..
   */
  public static function relatedLinks(array $content) {

    // Find the character index of the Useful links heading.
    $links_header_position = strpos($content[0][0]['value'], 'More useful links');

    if ($links_header_position === FALSE) {
      return [];
    }

    // Extract everything after the 'More useful links'.
    $links_markup = substr($content[0][0]['value'], $links_header_position);
    // Extract uri and text.
    $link_regex = '/<a href="(\S+?)".*?>\s*([^<]*)<\//m';
    preg_match_all($link_regex, $links_markup, $matches, PREG_SET_ORDER, 0);

    $links = [];

    // Create a Drupal link field entry for each extracted HTML link element.
    foreach ($matches as $link) {
      $links[] = [
        'uri' => (strpos($link[1], '/') === 0 ? 'internal:' . $link[1] : $link[1]),
        'title' => $link[2],
        'options' => [
          'attributes' => [],
        ],
      ];
    }

    return $links;
  }

  /**
   * Extracts embedded document links and replaces them with link to publication page.
   *
   * @param array $content
   *  Drupal content field array.
   *
   * @return array
   *  Drupal content field array.
   */
  public static function embeddedDocuments(array $content) {

    // Set Content Body Value
    $body = $content[0][0]['value'];

    // Publication Page link used for replacing embedded links
    $publications_link = 'TESTING LINK'; //@TODO: Find the correct way to provide a link to content type

    // Extract and replace links. -- @TODO: Improve REGEX to ensure it's not capturing every link tag unneccessarily
    $embed_regex = '/<a href="(\S+?)"/m';
    preg_replace($embed_regex, $publications_link, $body);

    // Set Body value with new modified value
    $content[0][0]['value'] = $body;

    /*
     * @TODO: THIS WILL NEED TO BE EDITED TO CREATE CONTENT TYPE NOT FIELD ENTRY -- IF REQUIRED!

    preg_match_all($embed_regex, $body, $matches, PREG_SET_ORDER, 0);
    $embedded_documents = [];

    // Create a Drupal publications content for each extracted link element.
    foreach ($matches as $link) {
      $embedded_documents[] = [
        'uri' => (strpos($link[1], '/') === 0 ? 'internal:' . $link[1] : $link[1]),
        'title' => $link[2],
        'options' => [
          'attributes' => [],
        ],
      ];
    }
    */

    return $content;
  }

}
