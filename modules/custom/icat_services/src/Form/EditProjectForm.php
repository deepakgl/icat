<?php

namespace Drupal\icat_services\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Database\Database;
use Drupal\Core\Url;
use Drupal\user\Entity\User;

/**
 *
 */
class EditProjectForm extends FormBase {

  /**
   *
   */
  public function getFormId() {
    return "edit_project_form";
  }

  /**
   *
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $path = \Drupal::request()->getpathInfo();
    $arg = explode('/', $path);
    $data = db_select('transportations', 'n')
      ->fields('n', [])
      ->condition('id', $arg[2], '=')
      ->execute()
      ->fetchAll()[0];
    $num = json_decode($data->freight_details);
    $config = $this->config('welcome.adminsettings');

    $form['description'] = [
      '#type' => 'item',
      '#markup' => $this->t('Please submit your rate quote at the bottom of the project page.'),
    ];

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
        '#type' => 'textfield',
        '#title' => t('Pieces'),
        '#default_value' => $value->Pieces,
        '#prefix' => "<div class='col-sm-12 inner-fieldset'><div class='col-sm-2'>",
        '#suffix' => "</div>",
        '#disabled' => TRUE,
      ];
      $form['names_fieldset'][$key]['Length'] = [
        '#type' => 'textfield',
        '#title' => t('L(IN):'),
        '#default_value' => $value->Length,
        '#prefix' => "<div class='col-sm-2'>",
        '#suffix' => "</div>",
        '#disabled' => TRUE,
      ];
      $form['names_fieldset'][$key]['width'] = [
        '#type' => 'textfield',
        '#title' => t('W(IN):'),
        '#default_value' => $value->width,
        '#prefix' => "<div class='col-sm-2'>",
        '#suffix' => "</div>",
        '#disabled' => TRUE,
      ];
      $form['names_fieldset'][$key]['height'] = [
        '#type' => 'textfield',
        '#title' => t('H(IN):'),
        '#default_value' => $value->height,
        '#prefix' => "<div class='col-sm-2'>",
        '#suffix' => "</div>",
        '#disabled' => TRUE,
      ];
      $form['names_fieldset'][$key]['weight'] = [
        '#type' => 'textfield',
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

    $cur_user = \Drupal::currentUser();

    $roles = $cur_user->getRoles();
    // kint($roles); die;.
    if (!in_array('vendor', $roles)) {
      $form['fieldset_testing'] = [
        '#type' => 'fieldset',
        '#title' => t('Select Vendors'),
        '#prefix' => '<div class="total-main-test">',
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
    /*$form['addressdetail']['quote_deadline'] = [
    '#title' => t('Quote Deadline'),
    '#type' => 'date',
    '#default_value' => $data->quote_deadline,
    '#disabled' => TRUE,
    '#prefix' => '<div class="col-sm-4 col-md-4">',
    '#suffix' => '</div>',
    ];*/
    $current_user = \Drupal::currentUser();
    $currentusername = $current_user->getAccountName();
    $db = \Drupal::database();
    $query = $db->select('transportations_vendor', 'tv');
    $query->fields('tv');
    $query->condition('name', $currentusername, '=');
    $query->condition('transportations_id', $arg[2], '=');
    $result = $query->execute()->fetchAll();
    foreach ($result as $row) {
      $id[] = $row->transportations_id;
    }
    if (in_array('vendor', $roles) && !in_array($arg[2], $id)) {

      $form['rate_quote_fieldset'] = [
        '#type' => 'fieldset',
        '#title' => $this->t('Rate Quote'),
        '#markup' => $this->t('<h6>All the rates are in USD</h6>'),
        '#prefix' => '<div class="freight-detail edit-page-freight" id="names-fieldset-wrapper">',
        '#suffix' => '</div>',
      ];

      $form['rate_quote_fieldset']['name'] = [
        '#title' => t('Name'),
        '#type' => 'hidden',
        '#default_value' => $currentusername,
      ];
      $form['rate_quote_fieldset']['pickup'] = [
        '#title' => t('Pickup (USD)'),
        '#type' => 'textfield',
        '#attributes' => [
          'class' => ['summable'],
        ],
      ];
      $form['rate_quote_fieldset']['localfees'] = [
        '#title' => t('Local Fees (USD)'),
        '#type' => 'textfield',
        '#attributes' => [
          'class' => ['summable'],
        ],
      ];
      $form['rate_quote_fieldset']['handlingfees'] = [
        '#title' => t('Handling Fees (USD)'),
        '#type' => 'textfield',
        '#attributes' => [
          'class' => ['summable'],
        ],
      ];
      $form['rate_quote_fieldset']['otherfees'] = [
        '#title' => t('Other Fees (USD)'),
        '#type' => 'textfield',
        '#attributes' => [
          'class' => ['summable'],
        ],
      ];
      $form['rate_quote_fieldset']['airfreight'] = [
        '#title' => t('Air Freight (USD)'),
        '#type' => 'textfield',
        '#attributes' => [
          'class' => ['summable'],
        ],
      ];
      $form['rate_quote_fieldset']['totalquote'] = [
        '#title' => t('Total Quote (USD)'),
        '#type' => 'textfield',
        '#attributes' => [
          'class' => ['totalsum'],
        ],
        // '#disabled' => TRUE,
      ];

    }
    else {

      $form['rate_quote_fieldset'] = [
        '#type' => 'fieldset',
        '#title' => $this->t('Rate Quote'),
        '#prefix' => '<div class="freight-detail edit-page-freight" id="names-fieldset-wrapper">',
        '#suffix' => '</div>',
      ];
      $form['rate_quote_fieldset']['name'] = [
        '#title' => t('Name'),
        '#type' => 'hidden',
        '#default_value' => $currentusername,
      ];
      $form['rate_quote_fieldset']['pickup'] = [
        '#title' => t('Pickup (USD)'),
        '#type' => 'textfield',
        '#default_value' => $row->pickup,
        '#attributes' => [
          'class' => ['summable'],
        ],
      ];
      $form['rate_quote_fieldset']['localfees'] = [
        '#title' => t('Local Fees (USD)'),
        '#type' => 'textfield',
        '#default_value' => $row->localfees,
        '#attributes' => [
          'class' => ['summable'],
        ],
      ];
      $form['rate_quote_fieldset']['handlingfees'] = [
        '#title' => t('Handling Fees (USD)'),
        '#type' => 'textfield',
        '#default_value' => $row->handlingfees,
        '#attributes' => [
          'class' => ['summable'],
        ],
      ];
      $form['rate_quote_fieldset']['otherfees'] = [
        '#title' => t('Other Fees (USD)'),
        '#type' => 'textfield',
        '#default_value' => $row->otherfees,
        '#attributes' => [
          'class' => ['summable'],
        ],
      ];
      $form['rate_quote_fieldset']['airfreight'] = [
        '#title' => t('Air Freight (USD)'),
        '#type' => 'textfield',
        '#default_value' => $row->airfreight,
        '#attributes' => [
          'class' => ['summable'],
        ],
      ];
      $form['rate_quote_fieldset']['totalquote'] = [
        '#title' => t('Total Quote (USD)'),
        '#type' => 'textfield',
        '#default_value' => $row->totalquote,
        '#attributes' => [
          'class' => ['totalsum'],
        ],
        // '#disabled' => TRUE,
      ];

    }
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Submit Rate Quote'),
      '#prefix' => '<div class="submit-main edit-submit">',
      '#suffix' => '</div>',
    ];
    $form_state->setCached(FALSE);

    return $form;
  }

  /**
   *
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $conn = Database::getConnection();
    $path = \Drupal::request()->getpathInfo();
    $arg = explode('/', $path);
    $current_user = \Drupal::currentUser();
    $currentusername = $current_user->getAccountName();
    $roles = $current_user->getRoles();
    $db = \Drupal::database();
    $query = $db->select('transportations_vendor', 'tv');
    $query->fields('tv');
    $query->condition('name', $currentusername, '=');
    $result = $query->execute()->fetchAll();
    foreach ($result as $row) {
      $id[] = $row->transportations_id;
    }
    if (in_array('vendor', $roles) && !in_array($arg[2], $id)) {
      $conn->insert('transportations_vendor')->fields(
        [
          'transportations_id' => $arg[2],
          'pickup' => $form_state->getValues()['rate_quote_fieldset']['pickup'],
          'localfees' => $form_state->getValues()['rate_quote_fieldset']['localfees'],
          'handlingfees' => $form_state->getValues()['rate_quote_fieldset']['handlingfees'],
          'otherfees' => $form_state->getValues()['rate_quote_fieldset']['otherfees'],
          'airfreight' => $form_state->getValues()['rate_quote_fieldset']['airfreight'],
          'totalquote' => $form_state->getValues()['rate_quote_fieldset']['totalquote'],
          'name' => $form_state->getValues()['rate_quote_fieldset']['name'],
        ]
      )->execute();
    }
    else {
      $conn->update('transportations_vendor')->fields(
        [
          'transportations_id' => $arg[2],
          'pickup' => $form_state->getValues()['rate_quote_fieldset']['pickup'],
          'localfees' => $form_state->getValues()['rate_quote_fieldset']['localfees'],
          'handlingfees' => $form_state->getValues()['rate_quote_fieldset']['handlingfees'],
          'otherfees' => $form_state->getValues()['rate_quote_fieldset']['otherfees'],
          'airfreight' => $form_state->getValues()['rate_quote_fieldset']['airfreight'],
          'totalquote' => $form_state->getValues()['rate_quote_fieldset']['totalquote'],
          'name' => $form_state->getValues()['rate_quote_fieldset']['name'],
        ]
      )
        ->condition('transportations_id', $arg[2])
        ->condition('name', $currentusername)
        ->execute();
    }
    drupal_set_message('Form submit successfully.');
    $url = Url::fromRoute('view.transportations.page_1');
    $form_state->setRedirectUrl($url);

  }

}
