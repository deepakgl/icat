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
class DeleteProjectForm extends ConfirmFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return "delete_project_form";
  }

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    return t('Do you want to delete?');
  }

  /**
   * {@inheritdoc}
   */
  public function getConfirmText() {
    return t('Delete it!');
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
    $conn->delete('transportations')
      ->condition('id', $arg[3])
      ->execute();

    drupal_set_message('Deleted successfully.');
    $url = Url::fromRoute('view.transportations.page_1');
    $form_state->setRedirectUrl($url);
  }

}
