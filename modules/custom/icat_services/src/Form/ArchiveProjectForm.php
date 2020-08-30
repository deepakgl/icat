<?php

namespace Drupal\icat_services\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Database\Database;
use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\Url;

/**
 * Class DeleteProjectForm.
 *
 * @package Drupal\icat_services\Form
 */
class ArchiveProjectForm extends ConfirmFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return "archive_project_form";
  }

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    return t('Do you want to archive?');
  }

  /**
   * {@inheritdoc}
   */
  public function getConfirmText() {
    return t('Archive it!');
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelUrl() {
    return new Url('view.transportations.page_1');
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    // $this->id;
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);
  }

  /**
   *
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $path = \Drupal::request()->getpathInfo();
    $arg = explode('/', $path);
    $conn = Database::getConnection();
    $num_updated = $conn->update('transportations')
      ->fields([
        'archive' => 1,
      ])
      ->condition('id', $arg[3])
      ->execute();

    drupal_set_message('Archive successfully.');
    $url = Url::fromRoute('view.transportations.page_1');
    $form_state->setRedirectUrl($url);
  }

}
