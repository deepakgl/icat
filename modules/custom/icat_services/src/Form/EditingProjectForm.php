<?php

namespace Drupal\icat_services\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Database\Database;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\HtmlCommand;
use Drupal\Core\Url;

/**
 *
 */
class EditingProjectForm extends FormBase {

  /**
   *
   */
  public function getFormId() {
    return "editing_project_form";
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
      '#title' => t('REQUEST FOR TRANSPORTATION QUOTE:'),
      '#default_value' => $data->request_id,
      '#required' => TRUE,
      '#disabled' => TRUE,
    ];
    $form['generalinfo']['Origin'] = [
      '#type' => 'textfield',
      '#title' => t('Origin:'),
      '#default_value' => $data->origin,
    ];
    $form['generalinfo']['pickup_date'] = [
      '#type' => 'date',
      '#title' => t('Pickup Date'),
      '#default_value' => $data->pickup_date,
    ];
    $form['generalinfo']['Destination'] = [
      '#type' => 'textfield',
      '#title' => t('Destination:'),
      '#default_value' => $data->destination,
    ];
    $form['generalinfo']['mode_transport'] = [
      '#type' => 'textfield',
      '#title' => t('Mode of Transport:'),
      '#default_value' => $data->mode_transport,
    ];
    $form['generalinfo']['incoterms'] = [
      '#type' => 'select',
      '#title' => ('Incoterms'),
      '#options' => [
        '' => t('Select'),
        'exw' => t('EXW'),
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
    ];
    $form['generalinfo']['eta_deadline'] = [
      '#type' => 'date',
      '#title' => t('ETA Deadline'),
      '#default_value' => $data->eta_deadline,
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
    foreach ($num_names as $key => $value) {
      $form['names_fieldset'][$key]['Pieces'] = [
        '#type' => 'number',
        '#title' => t('Pieces'),
        '#default_value' => $value->Pieces,
        '#prefix' => "<div class='col-sm-12 inner-fieldset'><div class='col-sm-2'>",
        '#suffix' => "</div>",
      ];
      $form['names_fieldset'][$key]['Length'] = [
        '#type' => 'number',
        '#title' => t('L(IN):'),
        '#default_value' => $value->Length,
        '#prefix' => "<div class='col-sm-2'>",
        '#suffix' => "</div>",
      ];
      $form['names_fieldset'][$key]['width'] = [
        '#type' => 'number',
        '#title' => t('W(IN):'),
        '#default_value' => $value->width,
        '#prefix' => "<div class='col-sm-2'>",
        '#suffix' => "</div>",
      ];
      $form['names_fieldset'][$key]['height'] = [
        '#type' => 'number',
        '#title' => t('H(IN):'),
        '#default_value' => $value->height,
        '#prefix' => "<div class='col-sm-2'>",
        '#suffix' => "</div>",
      ];
      $form['names_fieldset'][$key]['weight'] = [
        '#type' => 'number',
        '#title' => t('Weight(LBS):'),
        '#default_value' => $value->weight,
        '#prefix' => "<div class='col-sm-2'>",
        '#suffix' => "</div>",
        '#ajax' => [
          'callback' => '::settotal',
        ],
        '#attributes' => ['data-disable-refocus' => 'true'],
      ];
      $form_state->setCached(FALSE);
      if ($key != 1) {
        $form['names_fieldset'][$key]['remove_name'] = [
          '#type' => 'submit',
          '#value' => t('Remove ' . $key),
          '#submit' => ['::removeCallbackOne'],
          '#ajax' => [
            'callback' => '::addmoreCallback',
            'wrapper' => 'names-fieldset-wrapper',
          ],
          '#prefix' => "<div class='col-sm-2'>",
          '#suffix' => "</div>",
        ];
      }
      $form['names_fieldset'][$key]['class'] = [
        '#type' => 'item',
        '#markup' => '',
      // Close the class openend by the #prefix property.
        '#suffix' => '</div>',
      ];
    }
    $form['names_fieldset']['actions'] = [
      '#type' => 'actions',
    ];
    $form['names_fieldset']['actions']['add_name'] = [
      '#type' => 'submit',
      '#value' => t('Add one more'),
      '#submit' => ['::addOne'],
      '#ajax' => [
        'callback' => '::addmoreCallback',
        'wrapper' => 'names-fieldset-wrapper',
      ],
    ];
    $form['fieldset_commodity']['commodity'] = [
      '#title' => t('Commodity'),
      '#prefix' => '<div id="commodity-main">',
      '#type' => 'textfield',
      '#default_value' => $data->commodity,
    ];
    $form['fieldset_commodity']['requirements'] = [
      '#title' => t('Requirements'),
      '#type' => 'textfield',
      '#suffix' => '</div>',
      '#default_value' => $data->requirements,
    ];
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
      '#title' => t('Total Weight(LBS):'),
      '#markup' => '<div class="total_weight">Total Weight(LBS):<br>' . $data->total_weight . '</div>',
      '#prefix' => '<div class="col-sm-4" id="replace-this">',
      '#suffix' => '</div>',
    ];
    $form['total']['dimesnsional_weight'] = [
      '#type' => 'markup',
      '#title' => t('Dimesnsional Weight(LBS):'),
      '#markup' => '<div class="total_dimesnsiona">Dimesnsional Weight(LBS): <br>' . $data->dimesnsional_weight . '</div>',
      '#prefix' => '<div class="col-sm-4" id="replace-this">',
      '#suffix' => '</div>',
    ];
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
    ];
    $form['addressdetail']['contact'] = [
      '#title' => t('Contact'),
      '#type' => 'number',
      '#prefix' => '<div class="col-sm-4 col-md-4">',
      '#suffix' => '</div>',
      '#default_value' => $data->contact,
    ];
    $form['addressdetail']['email'] = [
      '#title' => t('Email'),
      '#type' => 'email',
      '#prefix' => '<div class="col-sm-4 col-md-4">',
      '#suffix' => '</div>',
      '#default_value' => $data->email,
    ];
    $form_state->setCached(FALSE);
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Update'),
      '#prefix' => '<div class="submit-main">',
    ];

    return $form;
  }

  /**
   *
   */
  public function addmoreCallback(array &$form, FormStateInterface $form_state) {
    return $form['names_fieldset'];
  }

  /**
   *
   */
  public function addOne(array &$form, FormStateInterface $form_state) {
    $field_deltas_array = $form_state->get('num_names');
    foreach ($field_deltas_array as $keys) {
      $name = $form_state->getValue(['names_fieldset', $key])['Piecests'];
      $forname = $form_state->getValue(['names_fieldset', $key])['Lengths'];
      $identity .= $forname . ' ' . $name . '  ';
    }
    if (count($field_deltas_array) > 0) {
      $field_deltas_array[] = max($field_deltas_array) + 1;
    }
    else {
      $field_deltas_array[] = 0;
    }
    $form_state->set('num_names', $field_deltas_array);
    $form_state->setRebuild();
  }

  /**
   *
   */
  public function removeCallbackOne(array &$form, FormStateInterface $form_state) {
    $delta_remove = $form_state->getTriggeringElement()['#array_parents'][1];
    $field_deltas_array = $form_state->get('num_names');
    $key_to_remove = array_search($delta_remove, $field_deltas_array);
    unset($field_deltas_array[$key_to_remove]);
    foreach ($field_deltas_array as $key) {
      $output[] = $key;
    }
    $form_state->set('num_names', $output);
    $form_state->setRebuild();
  }

  /**
   *
   */
  public function settotal(array $form, FormStateInterface $form_state) {
    $hight = $form_state->getValue(['names_fieldset', 'height']);
    $weights = $form_state->getValue(['names_fieldset', 'weight']);
    $identity = '';
    $num_names = $form_state->get('num_names');
    // drupal_set_message('Weight<pre>' . print_r($num_names, true) . '</pre>');.
    foreach ($num_names as $key) {
      $Pieces += $form_state->getValue(['names_fieldset', $key])['Pieces'];
      $Length = $Length + $form_state->getValue(['names_fieldset', $key])['Length'];
      $width = $width + $form_state->getValue(['names_fieldset', $key])['width'];
      $height = $height + $form_state->getValue(['names_fieldset', $key])['height'];
      $weight = $weight + $form_state->getValue(['names_fieldset', $key])['weight'];
    }
    $value = $width * $Length * $height / 166 / 2.2046;
    $response = new AjaxResponse();
    $response->addCommand(
      new HtmlCommand(
        '.total_dimesnsiona', '<div class="my_top_message">' . t('Dimesnsional Weight(LBS)') . '<br>' . $value . '</div>')
    );
    $response->addCommand(
      new HtmlCommand(
        '.total_Piecest', '<div class="my_top_message">' . t('Total Pieces') . '<br>' . $Pieces . '</div>')
    );
    $response->addCommand(
      new HtmlCommand(
        '.total_weight', '<div class="my_top_message">' . t('Total Weight') . '<br>' . $weight . '</div>')
    );

    return $response;
  }

  /**
   *
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Echo "<pre>";
    // print_r($form_state->getValues()); die('ss');.
    $conn = Database::getConnection();

    $hight = $form_state->getValue(['names_fieldset', 'height']);
    $weights = $form_state->getValue(['names_fieldset', 'weight']);
    $identity = '';
    $num_names = $form_state->get('num_names');
    // drupal_set_message('Weight<pre>' . print_r($num_names, true) . '</pre>');.
    foreach ($num_names as $key) {
      $Pieces += $form_state->getValue(['names_fieldset', $key])['Pieces'];
      $Length = $Length + $form_state->getValue(['names_fieldset', $key])['Length'];
      $width = $width + $form_state->getValue(['names_fieldset', $key])['width'];
      $height = $height + $form_state->getValue(['names_fieldset', $key])['height'];
      $weight = $weight + $form_state->getValue(['names_fieldset', $key])['weight'];
    }
    $value = $width * $Length * $height / 166 / 2.2046;
    $path = \Drupal::request()->getpathInfo();
    $arg = explode('/', $path);
    $conn->update('transportations')->fields(
      [
        'request_id' => $form_state->getValues()['generalinfo']['request_id'],
        'origin' => $form_state->getValues()['generalinfo']['Origin'],
        'pickup_date' => $form_state->getValues()['generalinfo']['pickup_date'],
        'destination' => $form_state->getValues()['generalinfo']['Destination'],
        'mode_transport' => $form_state->getValues()['generalinfo']['mode_transport'],
        'incoterms' => $form_state->getValues()['generalinfo']['incoterms'],
        'eta_deadline' => $form_state->getValues()['generalinfo']['eta_deadline'],
        'commodity' => $form_state->getValues()['fieldset_commodity']['commodity'],
        'requirements' => $form_state->getValues()['fieldset_commodity']['requirements'],
        'total_pieces' => $Pieces,
        'total_weight' => $weight,
        'dimesnsional_weight' => $value,
        'name' => $form_state->getValues()['addressdetail']['name'],
        'contact' => $form_state->getValues()['addressdetail']['contact'],
        'email' => $form_state->getValues()['addressdetail']['email'],
      ]
    )
      ->condition('id', $arg[2])
      ->execute();

    drupal_set_message('Form submit successfully.');
    $url = Url::fromRoute('view.transportations.page_1');
    $form_state->setRedirectUrl($url);

  }

}
