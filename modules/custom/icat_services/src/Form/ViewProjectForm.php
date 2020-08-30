<?php

namespace Drupal\icat_services\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\user\Entity\User;

/**
 *
 */
class ViewProjectForm extends FormBase {

  /**
   *
   */
  public function getFormId() {
    return "view_project_form";
  }

  /**
   *
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $path = \Drupal::request()->getpathInfo();
    $arg  = explode('/', $path);
    $data = db_select('transportations', 'n')
      ->fields('n', [])
      ->condition('id', $arg[3], '=')
      ->execute()
      ->fetchAll()[0];

    $data2 = db_select('transportations_vendor', 'n')
      ->fields('n', [])
      ->condition('transportations_id', $arg[3], '=')
      ->execute()
      ->fetchAll()[0];

    $num = json_decode($data->freight_details);
    $config = $this->config('welcome.adminsettings');
    $form['generalinfo'] = [
      '#type' => 'fieldset',
      '#prefix' => '<div class="row">',
      '#suffix' => '</div>',
      '#attached' => [
        'library' => ['icat_services/icat_services'],
      ],
    ];
    $form['generalinfo']['request_id'] = [
      '#type' => 'textfield',
      '#title' => t('ICAT ID'),
      '#default_value' => $data->request_id,
      '#required' => TRUE,
      '#disabled' => TRUE,
    ];
    $form['generalinfo']['Origin'] = [
      '#type' => 'textarea',
      '#title' => t('Origin:'),
      '#default_value' => $data->origin,
      '#disabled' => TRUE,
    ];
    $form['generalinfo']['pickup_date'] = [
      '#type' => 'date',
      '#title' => t('Pickup Date'),
      '#default_value' => $data->pickup_date,
      '#disabled' => TRUE,
    ];

    $form['generalinfo']['eta_deadline'] = [
      '#type' => 'date',
      '#title' => t('ETA Deadline'),
      '#default_value' => $data->eta_deadline,
      '#disabled' => TRUE,
    ];

    $form['generalinfo']['Destination'] = [
      '#type' => 'textfield',
      '#title' => t('Destination:'),
      '#default_value' => $data->destination,
      '#disabled' => TRUE,
    ];
    $form['generalinfo']['mode_transport'] = [
      '#type' => 'select',
      '#title' => t('Mode of Transport:'),
      '#default_value' => $data->mode_transport,
      '#disabled' => TRUE,
      '#options' => [
        '' => t('Select'),
        'AIR' => t('AIR'),
        'OCEAN' => t('OCEAN'),
      ],
    ];
    $form['generalinfo']['incoterms'] = [
      '#type' => 'select',
      '#title' => ('Incoterms'),
      '#options' => [
        '' => t('Select'),
        'EXW' => t('EXW'),
        'FCA' => t('FCA'),
        'CPT' => t('CPT'),
        'CIP' => t('CIP'),
        'DAT' => t('DAT'),
        'DAP' => t('DAP'),
        'DDP' => t('DDP'),
        'FAS' => t('FAS'),
        'FOB' => t('FOB'),
        'CFR' => t('CFR'),
        'CIF' => t('CIF'),
      ],
      '#default_value' => $data->incoterms,
      '#disabled' => TRUE,
    ];

    $form['generalinfo']['quote_deadline'] = [
      '#title' => t('Quote Deadline'),
      '#type' => 'date',
      '#default_value' => $data->quote_deadline,
      '#disabled' => TRUE,
    ];
    $num_names = $form_state->get('num_names');
    if ($num_names === NULL) {
      $num_names = $form_state->set('num_names', [1]);
      $num_names = $form_state->get('num_names');
    }
    $form['#tree'] = TRUE;
    $form['names_fieldset'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('FREIGHT DETAILS'),
      '#prefix' => '<div class="freight-detail" id="names-fieldset-wrapper">',
      '#suffix' => '</div>',
    ];
    foreach ($num as $key => $value) {
      $form['names_fieldset'][$key]['Pieces'] = [
        '#type' => 'number',
        '#title' => t('Pieces'),
        '#default_value' => $value->Pieces,
        '#prefix' => "<div class='col-sm-12 inner-fieldset'><div class='col-sm-2'>",
        '#suffix' => "</div>",
        '#disabled' => TRUE,
      ];
      $form['names_fieldset'][$key]['Length'] = [
        '#type' => 'number',
        '#title' => t('L(IN):'),
        '#default_value' => $value->Length,
        '#prefix' => "<div class='col-sm-2'>",
        '#suffix' => "</div>",
        '#disabled' => TRUE,
      ];
      $form['names_fieldset'][$key]['width'] = [
        '#type' => 'number',
        '#title' => t('W(IN):'),
        '#default_value' => $value->width,
        '#prefix' => "<div class='col-sm-2'>",
        '#suffix' => "</div>",
        '#disabled' => TRUE,
      ];
      $form['names_fieldset'][$key]['height'] = [
        '#type' => 'number',
        '#title' => t('H(IN):'),
        '#default_value' => $value->height,
        '#prefix' => "<div class='col-sm-2'>",
        '#suffix' => "</div>",
        '#disabled' => TRUE,
      ];
      $form['names_fieldset'][$key]['weight'] = [
        '#type' => 'number',
        '#title' => t('Weight(KG):'),
        '#default_value' => $value->weight,
        '#prefix' => "<div class='col-sm-2'>",
        '#suffix' => "</div>",
        '#disabled' => TRUE,
        '#attributes' => ['data-disable-refocus' => 'true'],
      ];
      $form_state->setCached(FALSE);
      $form['names_fieldset'][$key]['class'] = [
        '#type' => 'item',
        '#markup' => '',
      // Close the class openend by the #prefix property.
        '#suffix' => '</div>',
      ];
    }
    $form['fieldset_commodity']['commodity'] = [
      '#title' => t('Commodity'),
      '#prefix' => '<div id="commodity-main">',
      '#type' => 'textfield',
      '#default_value' => $data->commodity,
      '#disabled' => TRUE,
    ];
    $form['fieldset_commodity']['requirements'] = [
      '#title' => t('Requirements'),
      '#type' => 'textarea',
      '#suffix' => '</div>',
      '#default_value' => $data->requirements,
      '#disabled' => TRUE,
    ];
    $ids = \Drupal::entityQuery('user')
      ->condition('status', 1)
      ->condition('roles', 'vendor')
      ->execute();

    $users = User::loadMultiple($ids);

    foreach ($users as $user) {
      $username = $user->get('name')->value;
      $uid = $user->get('uid')->value;
      $userlist[$uid] = $username;
    }
    $vendor = explode(", ", $data->vendor);
    foreach ($vendor as $key => $value) {
      $arr[$value] = $value;
    }

    $form['total'] = [
      '#type' => 'fieldset',
      '#title' => t('Total'),
      '#prefix' => '<div class="total-main">',
      '#suffix' => '</div>',
    ];
    $form['total']['total_pieces'] = [
      '#type' => 'markup',
      '#title' => t('Total Pieces:'),
      '#markup' => '<div class="total_Piecest">Total Pieces<br>' . $data->total_pieces . '</div>',
      '#prefix' => '<div class="col-sm-4" id="replace-this">',
      '#suffix' => '</div>',
    ];
    $form['total']['total_weight'] = [
      '#type' => 'markup',
      '#title' => t('Total Weight(KG):'),
      '#markup' => '<div class="total_weight">Total Weight(KG):<br>' . $data->total_weight . '</div>',
      '#prefix' => '<div class="col-sm-4" id="replace-this">',
      '#suffix' => '</div>',
    ];
    $form['total']['dimesnsional_weight'] = [
      '#type' => 'markup',
      '#title' => t('Dimesnsional Weight(KG):'),
      '#markup' => '<div class="total_dimesnsiona">Dimesnsional Weight(KG): <br>' . $data->dimesnsional_weight . '</div>',
      '#prefix' => '<div class="col-sm-4" id="replace-this">',
      '#suffix' => '</div>',
    ];
    $form['total']['total_cubic'] = [
      '#type' => 'markup',
      '#title' => t('Total Cubic Meters(CBM):'),
      '#markup' => '<div class="total_cubic">Total Cubic Meters(CBM):<br>' . $data->total_cubic . '</div>',
      '#prefix' => '<div class="col-sm-3" id="replace-this">',
      '#suffix' => '</div>',
    ];

    $uids = \Drupal::currentUser()->id();
    $cur_user = \Drupal::currentUser();

    $roles = $cur_user->getRoles();
    // kint($roles); die;.
    if (!in_array('vendor', $roles)) {
      $form['fieldset_testing'] = [
        '#type' => 'fieldset',
        '#title' => t('Select Vendors'),
        '#prefix' => '<div class="total-main-test help-remove">',
        '#suffix' => '</div>',
      ];

      $form['fieldset_testing']['select_multiple'] = [
        '#type' => 'checkboxes',
      // '#title' => 'Select Vendors',
        '#multiple' => TRUE,
        '#options' => $userlist,
        '#default_value' => $arr,
        '#disabled' => TRUE,
        '#prefix' => '<div class="select-vendor">',
        '#suffix' => '</div>',
      ];
    }

    $form['addressdetail'] = [
      '#type' => 'fieldset',
      '#title' => t('Icat Logistics inc'),
      '#prefix' => '<div class="Logistics-main">',
      '#suffix' => '</div>',
    ];
    $form['addressdetail']['name'] = [
      '#title' => t('Name'),
      '#type' => 'textfield',
      '#prefix' => '<div class="col-sm-4 col-md-4">',
      '#suffix' => '</div>',
      '#default_value' => $data->name,
      '#disabled' => TRUE,
    ];
    $form['addressdetail']['email'] = [
      '#title' => t('Email'),
      '#type' => 'email',
      '#prefix' => '<div class="col-sm-4 col-md-4">',
      '#suffix' => '</div>',
      '#default_value' => $data->email,
      '#disabled' => TRUE,
    ];

    $form['buttonsedit'] = [
      '#type' => 'fieldset',
      '#prefix' => '<div class="buttons-edit">',

    ];
    if (!in_array('vendor', $roles)) {
      $form['apply']['buttonsedit'] = [
        '#type' => 'item',
        '#markup' => t('<button type="button"><a href="http://icatlogistics.servehttp.com/icat-project/edit/@ufl">Edit</a></button>', ['@ufl' => $arg[3]]),
      ];
    }
    if (in_array('vendor', $roles)) {
      $form['coming']['buttonsedit'] = [
        '#type' => 'item',
        '#markup' => t('<button type="button"><a href="http://icatlogistics.servehttp.com/icat-project/@gfl">Submit Your Quote</a></button>', ['@gfl' => $arg[3]]),
      ];
    }
    $form['going']['buttonsedit'] = [
      '#type' => 'item',
      '#markup' => t('<button type="button"><a href="http://icatlogistics.servehttp.com/transportations">Back to Project List</a></button>'),
      '#suffix' => '</div>',
    ];
    /*$form['addressdetail']['quote_deadline'] = [
    '#title' => t('Quote Deadline'),
    '#type' => 'date',
    '#default_value' => $data->quote_deadline,
    '#disabled' => TRUE,
    '#prefix' => '<div class="col-sm-4 col-md-4">',
    '#suffix' => '</div>',
    ];*/
    $form_state->setCached(FALSE);

    return $form;
  }

  /**
   *
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

  }

}
