<?php

/**
 * @file
 * Functions to support theming in the urologyservicesinquiry_theme theme.
 */

function independentreviewofeducation_theme_form_system_theme_settings_alter(&$form, \Drupal\Core\Form\FormStateInterface $form_state, $form_id = NULL) {
  // Work-around for a core bug affecting admin themes. See issue #943212.
  if (isset($form_id)) {
    return;
  }

  $form['social_links'] = array(
    '#type' => 'details',
    '#title' => t('Social media links'),
    '#open' => TRUE,
  );

  $form['social_links']['twitter_profile_url'] = array(
    '#type' => 'textfield',
    '#title' => t('Twitter URL'),
    '#default_value' => theme_get_setting('twitter_profile_url'),
    '#description' => t("Enter your Twitter profile URL."),
  );
  $form['social_links']['facebook_profile_url'] = array(
    '#type' => 'textfield',
    '#title' => t('Facebook URL'),
    '#default_value' => theme_get_setting('facebook_profile_url'),
    '#description' => t("Enter your Facebook profile URL."),
  );
  $form['social_links']['linkedin_profile_url'] = array(
    '#type' => 'textfield',
    '#title' => t('Linkedin URL'),
    '#default_value' => theme_get_setting('linkedin_profile_url'),
    '#description' => t("Enter your Linkedin profile URL."),
  );
  $form['social_links']['pinterest_profile_url'] = array(
    '#type' => 'textfield',
    '#title' => t('Pinterest URL'),
    '#default_value' => theme_get_setting('pinterest_profile_url'),
    '#description' => t("Enter your Linkedin Pinterest URL."),
  );
  $form['social_links']['youtube_profile_url'] = array(
    '#type' => 'textfield',
    '#title' => t('Youtube URL'),
    '#default_value' => theme_get_setting('youtube_profile_url'),
    '#description' => t("Enter your Youtube profile URL."),
  );
}
