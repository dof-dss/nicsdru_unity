<?php

namespace Drupal\unity_internal_link_checker\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Implements admin form to allow setting of audit text.
 */
class LinkCheckerForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'unity_internal_link_checker.linksettings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'link_checker_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('unity_internal_link_checker.linksettings');

    $message = "List all domain names here that must be stripped out of absolute links and changed to relative as they are saved.";
    $message .= "<br/>For example adding 'http://uregni.gov.uk' here will cause any links starting with that domain name to be saved as relative links instead.";
    $message .= "<br/>You may add as many domain names as you like, along with the appropriate 'http' or 'https' protocol.";

    $form['site_url_list'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Site URLs'),
      '#description' => $this->t($message),
      '#default_value' => $config->get('site_url_list'),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    if ($form_state->getValue('site_url_list')) {
      $replace_url_list = explode(PHP_EOL, $form_state->getValue('site_url_list'));
      foreach ($replace_url_list as $replace_url) {
        // Make sure url is 'clean'.
        $replace_url = str_replace(["\n", "\t", "\r"], '', $replace_url);
        $pass = FALSE;
        if (preg_match('|^http:\/\/|', $replace_url) || preg_match('|^https:\/\/|', $replace_url)) {
          $pass = TRUE;
        }
        if (!$pass) {
          $form_state->setErrorByName('site_url_list', $this->t("URLs must start with 'http' or 'https'"));
        }
      }
    }
    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);

    $this->config('unity_internal_link_checker.linksettings')
      ->set('site_url_list', $form_state->getValue('site_url_list'))
      ->save();
  }

}
