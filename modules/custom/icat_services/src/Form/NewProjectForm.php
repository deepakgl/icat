<?php

namespace Drupal\icat_services\Form;

use Drupal\user\Entity\User;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Database\Database;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\HtmlCommand;
use Drupal\Core\Url;

/**
 *
 */
class NewProjectForm extends FormBase {

  /**
   *
   */
  public function getFormId() {
    return "NewProject_form";
  }

  /**
   *
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('welcome.adminsettings');
    $form['generalinfo'] = [
      '#type' => 'fieldset',
      '#prefix' => '<div class="row">',
      '#suffix' => '</div>',
      '#attached' => [
        'library' => ['icat_services/icat_services'],
      ],
    ];
    // $randumno = substr(str_shuffle(str_repeat($x = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ', ceil(10 / strlen($x)))), 1, 10);
    $form['generalinfo']['request_id'] = [
      '#type' => 'textfield',
      '#title' => t('ICAT ID'),
      // '#default_value' => 'RFQ' . $randumno,
      '#required' => TRUE,
    ];
    $form['generalinfo']['Origin'] = [
      '#type' => 'textarea',
      '#title' => t('Origin:'),
      '#required' => TRUE,
    ];
    $form['generalinfo']['pickup_date'] = [
      '#type' => 'date',
      '#title' => t('Pickup Date'),
      '#required' => TRUE,
      '#attributes' => ['id' => 'txtFromDate'],
    ];

    $form['generalinfo']['eta_deadline'] = [
      '#type' => 'date',
      '#title' => t('ETA Deadline'),
      '#required' => TRUE,
      '#attributes' => ['id' => 'txtToDate'],
    ];

    $form['generalinfo']['Destination'] = [
      '#type' => 'textfield',
      '#title' => t('Destination:'),
      '#required' => TRUE,
    ];
    $form['generalinfo']['mode_transport'] = [
      '#type' => 'select',
      '#title' => t('Mode of Transport:'),
      '#required' => TRUE,
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
      '#required' => TRUE,
    ];

    $form['generalinfo']['quote_deadline'] = [
      '#title' => t('Quote Deadline'),
      '#type' => 'date',
      '#required' => TRUE,
      '#attributes' => ['id' => 'quoteDate'],
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
    foreach ($num_names as $key) {
      $form['names_fieldset'][$key]['Pieces'] = [
        '#type' => 'textfield',
        '#title' => t('Pieces'),
        '#prefix' => "<div class='col-sm-12 inner-fieldset'><div class='col-sm-2'>",
        '#suffix' => "</div>",
        '#ajax' => [
          'callback' => '::settotal',
        ],
        '#attributes' => ['data-disable-refocus' => 'true'],
        '#required' => TRUE,
      ];
      $form['names_fieldset'][$key]['Length'] = [
        '#type' => 'textfield',
        '#title' => t('L(IN):'),
        '#prefix' => "<div class='col-sm-2'>",
        '#suffix' => "</div>",
        '#ajax' => [
          'callback' => '::settotal',
        ],
        '#attributes' => ['data-disable-refocus' => 'true'],
        '#required' => TRUE,
      ];
      $form['names_fieldset'][$key]['width'] = [
        '#type' => 'textfield',
        '#title' => t('W(IN):'),
        '#prefix' => "<div class='col-sm-2'>",
        '#suffix' => "</div>",
        '#ajax' => [
          'callback' => '::settotal',
        ],
        '#attributes' => ['data-disable-refocus' => 'true'],
        '#required' => TRUE,
      ];
      $form['names_fieldset'][$key]['height'] = [
        '#type' => 'textfield',
        '#title' => t('H(IN):'),
        '#prefix' => "<div class='col-sm-2'>",
        '#suffix' => "</div>",
        '#ajax' => [
          'callback' => '::settotal',
        ],
        '#attributes' => ['data-disable-refocus' => 'true'],
        '#required' => TRUE,
      ];
      $form['names_fieldset'][$key]['weight'] = [
        '#type' => 'textfield',
        '#title' => t('Weight(KG):'),
        '#prefix' => "<div class='col-sm-2'>",
        '#suffix' => "</div>",
        '#ajax' => [
          'callback' => '::settotal',
        ],
        '#attributes' => ['data-disable-refocus' => 'true'],
        '#required' => TRUE,
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
        // '#required' => TRUE,
    ];
    $form['fieldset_commodity']['requirements'] = [
      '#title' => t('Requirements'),
      '#type' => 'textarea',
      '#suffix' => '</div>',
        // '#required' => TRUE,
    ];

    // $ids = \Drupal::entityQuery('user')
    //      ->condition('status', 1)
    //      ->condition('roles', 'vendor')
    //      ->execute();
    //
    //    $users = User::loadMultiple($ids);
    //
    //    foreach ($users as $user) {
    //      $username = $user->get('name')->value;
    //      $uid = $user->get('uid')->value;
    //      $userlist[$uid] = $username;
    //    }
    $form['total'] = [
      '#type' => 'fieldset',
      '#title' => t('Total'),
      '#prefix' => '<div class="total-main">',
      '#suffix' => '</div>',
    ];
    $form['total']['total_pieces'] = [
      '#type' => 'markup',
      '#title' => t('Total Pieces:'),
      '#markup' => '<div class="total_Piecest">Total Pieces</div>',
      '#prefix' => '<div class="col-sm-3" id="replace-this">',
      '#suffix' => '</div>',
    ];
    $form['total']['total_weight'] = [
      '#type' => 'markup',
      '#title' => t('Total Weight(KG):'),
      '#markup' => '<div class="total_weight">Total Weight(KG):</div>',
      '#prefix' => '<div class="col-sm-3" id="replace-this">',
      '#suffix' => '</div>',
    ];
    $form['total']['dimesnsional_weight'] = [
      '#type' => 'markup',
      '#title' => t('Dimensional Weight(KG):'),
      '#markup' => '<div class="total_dimesnsiona">Dimensional Weight(KG):</div>',
      '#prefix' => '<div class="col-sm-3" id="replace-this">',
      '#suffix' => '</div>',
    ];
    $form['total']['total_cubic'] = [
      '#type' => 'markup',
      '#title' => t('Total Cubic Meters(CBM):'),
      '#markup' => '<div class="total_cubic">Total Cubic Meters(CBM):</div>',
      '#prefix' => '<div class="col-sm-3" id="replace-this">',
      '#suffix' => '</div>',
    ];

    $current_user = \Drupal::currentUser();
    $currentuseremail = $current_user->getEmail();
    $currentusername = $current_user->getAccountName();
    $data = db_select('users_field_data', 'u');
    $data->leftjoin('user__roles',
    'ur', 'ur.entity_id = u.uid');
    $data->leftjoin('user__field_country',
    'uc', 'uc.entity_id = u.uid');
    $data->fields('u', ['name', 'uid']);
    $data->condition('ur.roles_target_id', 'vendor', '=');
    $results = $data->execute()->fetchAll();
    $userlist = [];
    foreach ($results as $user) {
      $username = $user->name;
      $uid = $user->uid;
      $userlist[$uid] = $username;
    }
    $form['fieldset_testing'] = [
      '#type' => 'fieldset',
      '#title' => t('Select Vendors'),
      '#prefix' => '<div class="total-main-test">',
      '#suffix' => '</div>',
    ];
    $form['fieldset_testing']['country'] = [
      '#type' => 'select',
      '#title' => 'Select the country',
      '#options' => ['all' => 'All', 'Afghanistan' => 'Afghanistan', 'Albania' => 'Albania', 'Algeria' => 'Algeria', 'Andorra' => 'Andorra', 'Angola' => 'Angola', 'Antigua and Barbuda' => 'Antigua and Barbuda', 'Argentina' => 'Argentina', 'Armenia' => 'Armenia', 'Australia' => 'Australia', 'Austria' => 'Austria', 'Azerbaijan' => 'Azerbaijan', 'Bahamas' => 'Bahamas', 'Bahrain' => 'Bahrain', 'Bangladesh' => 'Bangladesh', 'Barbados' => 'Barbados', 'Belarus' => 'Belarus', 'Belgium' => 'Belgium', 'Belize' => 'Belize', 'Benin' => 'Benin', 'Bhutan' => 'Bhutan', 'Bolivia' => 'Bolivia', 'Bosnia and Herzegovina' => 'Bosnia and Herzegovina', 'Botswana' => 'Botswana', 'Brazil' => 'Brazil', 'Brunei' => 'Brunei', 'Bulgaria' => 'Bulgaria', 'Burkina Faso' => 'Burkina Faso', 'Burundi' => 'Burundi', 'Côte d Ivoire' => 'Côte d Ivoire', 'Cabo Verde' => 'Cabo Verde', 'Cambodia' => 'Cambodia', 'Cameroon' => 'Cameroon', 'Canada' => 'Canada', 'Central African Republic' => 'Central African Republic', 'Chad' => 'Chad', 'Chile' => 'Chile', 'China' => 'China', 'Colombia' => 'Colombia', 'Comoros' => 'Comoros', 'Congo' => 'Congo', 'Costa Rica' => 'Costa Rica', 'Croatia' => 'Croatia', 'Cuba' => 'Cuba', 'Cyprus' => 'Cyprus', 'Czechia' => 'Czechia', 'Democratic Republic of the Congo' => 'Democratic Republic of the Congo', 'Denmark' => 'Denmark', 'Djibouti' => 'Djibouti', 'Dominica' => 'Dominica', 'Dominican Republic' => 'Dominican Republic', 'Ecuador' => 'Ecuador', 'Egypt' => 'Egypt', 'El Salvador' => 'El Salvador', 'Equatorial Guinea' => 'Equatorial Guinea', 'Eritrea' => 'Eritrea', 'Estonia' => 'Estonia', 'Eswatini' => 'Eswatini', 'Ethiopia' => 'Ethiopia', 'Fiji' => 'Fiji', 'Finland' => 'Finland', 'France' => 'France', 'Gabon' => 'Gabon', 'Gambia' => 'Gambia', 'Georgia' => 'Georgia', 'Germany' => 'Germany', 'Ghana' => 'Ghana', 'Greece' => 'Greece', 'Grenada' => 'Grenada', 'Guatemala' => 'Guatemala', 'Guinea' => 'Guinea', 'Guinea-Bissau' => 'Guinea-Bissau', 'Guyana' => 'Guyana', 'Haiti' => 'Haiti', 'Holy See' => 'Holy See', 'Honduras' => 'Honduras', 'Hungary' => 'Hungary', 'Iceland' => 'Iceland', 'India' => 'India', 'Indonesia' => 'Indonesia', 'Iraq' => 'Iraq', 'Ireland' => 'Ireland', 'Israel' => 'Israel', 'Italy' => 'Italy', 'Jamaica' => 'Jamaica', 'Japan' => 'Japan', 'Jordan' => 'Jordan', 'Kazakhstan' => 'Kazakhstan', 'Kenya' => 'Kenya', 'Kiribati' => 'Kiribati', 'Kuwait' => 'Kuwait', 'Kyrgyzstan' => 'Kyrgyzstan', 'Laos' => 'Laos', 'Latvia' => 'Latvia', 'Lebanon' => 'Lebanon', 'Lesotho' => 'Lesotho', 'Liberia' => 'Liberia', 'Libya' => 'Libya', 'Liechtenstein' => 'Liechtenstein', 'Lithuania' => 'Lithuania', 'Luxembourg' => 'Luxembourg', 'Madagascar' => 'Madagascar', 'Malawi' => 'Malawi', 'Malaysia' => 'Malaysia', 'Maldives' => 'Maldives', 'Mali' => 'Mali', 'Malta' => 'Malta', 'Marshall Islands' => 'Marshall Islands', 'Mauritania' => 'Mauritania', 'Mauritius' => 'Mauritius', 'Mexico' => 'Mexico', 'Micronesia' => 'Micronesia', 'Moldova' => 'Moldova', 'Monaco' => 'Monaco', 'Mongolia' => 'Mongolia', 'Montenegro' => 'Montenegro', 'Morocco' => 'Morocco', 'Mozambique' => 'Mozambique', 'Myanmar' => 'Myanmar', 'Namibia' => 'Namibia', 'Nauru' => 'Nauru', 'Nepal' => 'Nepal', 'Netherlands' => 'Netherlands', 'New Zealand' => 'New Zealand', 'Nicaragua' => 'Nicaragua', 'Niger' => 'Niger', 'Nigeria' => 'Nigeria', 'North Macedonia' => 'North Macedonia', 'Norway' => 'Norway', 'Oman' => 'Oman', 'Pakistan' => 'Pakistan', 'Palau' => 'Palau', 'Palestine State' => 'Palestine State', 'Panama' => 'Panama', 'Papua New Guinea' => 'Papua New Guinea', 'Paraguay' => 'Paraguay', 'Peru' => 'Peru', 'Philippines' => 'Philippines', 'Poland' => 'Poland', 'Portugal' => 'Portugal', 'Qatar' => 'Qatar', 'Romania' => 'Romania', 'Russia' => 'Russia', 'Rwanda' => 'Rwanda', 'Saint Kitts and Nevis' => 'Saint Kitts and Nevis', 'Saint Lucia' => 'Saint Lucia', 'Saint Vincent and the Grenadines' => 'Saint Vincent and the Grenadines', 'Samoa' => 'Samoa', 'San Marino' => 'San Marino', 'Sao Tome and Principe' => 'Sao Tome and Principe', 'Saudi Arabia' => 'Saudi Arabia', 'Senegal' => 'Senegal', 'Serbia' => 'Serbia', 'Seychelles' => 'Seychelles', 'Sierra Leone' => 'Sierra Leone', 'Singapore' => 'Singapore', 'Slovakia' => 'Slovakia', 'Slovenia' => 'Slovenia', 'Solomon Islands' => 'Solomon Islands', 'Somalia' => 'Somalia', 'South Africa' => 'South Africa', 'South Korea' => 'South Korea', 'South Sudan' => 'South Sudan', 'Spain' => 'Spain', 'Sri Lanka' => 'Sri Lanka', 'Sudan' => 'Sudan', 'Suriname' => 'Suriname', 'Sweden' => 'Sweden', 'Switzerland' => 'Switzerland', 'Syria' => 'Syria', 'Tajikistan' => 'Tajikistan', 'Tanzania' => 'Tanzania', 'Thailand' => 'Thailand', 'Timor-Leste' => 'Timor-Leste', 'Togo' => 'Togo', 'Tonga' => 'Tonga', 'Trinidad and Tobago' => 'Trinidad and Tobago', 'Tunisia' => 'Tunisia', 'Turkey' => 'Turkey', 'Turkmenistan' => 'Turkmenistan', 'Tuvalu' => 'Tuvalu', 'Uganda' => 'Uganda', 'Ukraine' => 'Ukraine', 'United Arab Emirates' => 'United Arab Emirates', 'United Kingdom' => 'United Kingdom', 'United States of America' => 'United States of America', 'Uruguay' => 'Uruguay', 'Uzbekistan' => 'Uzbekistan', 'Vanuatu' => 'Vanuatu', 'Venezuela' => 'Venezuela', 'Vietnam' => 'Vietnam', 'Yemen' => 'Yemen', 'Zambia' => 'Zambia', 'Zimbabwe' => 'Zimbabwe'],
      '#ajax' => [
        'callback' => '::statesCallback',
        'wrapper' => 'state-wrapper',
        'method' => 'replace',
      ],
      '#prefix' => '<div class="select-vendor-main">',
      // '#required' => TRUE,
    ];
    // $form['fieldset_testing']['select_multiple']['states_wrapper'] = [
    //   '#type' => 'container',
    //   '#attributes' => ['id' => 'state-wrapper'],
    //   '#required' => TRUE,
    //   '#prefix' => '<div class="select-vendor">',
    //   '#suffix' => '</div>',
    // ];
    $form['fieldset_testing']['states_wrapper']['states'] = [
      '#type' => 'select',
      '#multiple' => TRUE,
      '#attributes' => ['id' => 'state-wrapper'],
      '#options' => $userlist,
      '#prefix' => '<div class="select-vendor">',
      '#suffix' => '</div>',
      // '#required' => TRUE,
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
      '#default_value' => $currentusername,
      '#disabled' => TRUE,
      '#required' => TRUE,
    ];
    $form['addressdetail']['email'] = [
      '#title' => t('Email'),
      '#type' => 'email',
      '#prefix' => '<div class="col-sm-4 col-md-4">',
      '#suffix' => '</div>',
      '#default_value' => $currentuseremail,
      '#disabled' => TRUE,
      '#required' => TRUE,
    ];
    /*$form['addressdetail']['quote_deadline'] = [
    '#title' => t('Quote Deadline'),
    '#type' => 'date',
    '#prefix' => '<div class="col-sm-4 col-md-4">',
    '#suffix' => '</div>',
    '#required' => TRUE,
    ];*/
    $form['fieldset_help']['archieve'] = [
      '#title' => t('Archieve'),
      '#type' => 'hidden',
      '#default_value' => 0,
    ];

    $form_state->setCached(FALSE);
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Submit'),
      '#prefix' => '<div class="submit-main">',
    ];

    return $form;
  }

  /**
   *
   */
  public function statesCallback(array &$form, FormStateInterface $form_state) {
    $country = $form_state->getValues()['fieldset_testing']['country'];
    $data = db_select('users_field_data', 'u');
    $data->leftjoin('user__roles',
    'ur', 'ur.entity_id = u.uid');
    $data->leftjoin('user__field_country',
    'uc', 'uc.entity_id = u.uid');
    $data->fields('u', ['name', 'uid']);
    $data->condition('ur.roles_target_id', 'vendor', '=');
    if ($form_state->getValues()['fieldset_testing']['country'] != 'all') {
      $data->condition('uc.field_country_value', $country, '=');
    }
    $results = $data->execute()->fetchAll();
    $userlist = [];
    foreach ($results as $user) {
      $username = $user->name;
      $uid = $user->uid;
      $userlist[$uid] = $username;
    }
    $form['fieldset_testing']['states_wrapper']['states']['#options'] = $userlist;

    // $form['fieldset_testing']['select_multiple']['states_wrapper']['states'] = [
    //   '#type' => 'select',
    //   //'#options' => $this->states[$country],
    //   '#options' => $userlist,
    //   '#attributes' => [
    //       'multiple' => 'true'
    //       ],
    // ];
    // return $form['fieldset_testing']['select_multiple']['states_wrapper'];
    return $form['fieldset_testing']['states_wrapper'];
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
      $Pieces = $form_state->getValue(['names_fieldset', $key])['Pieces'];
      $Pieces_total = $Pieces_total + $form_state->getValue(['names_fieldset', $key])['Pieces'];
      $Length = $form_state->getValue(['names_fieldset', $key])['Length'];
      $width = $form_state->getValue(['names_fieldset', $key])['width'];
      $height = $form_state->getValue(['names_fieldset', $key])['height'];
      $weight = $weight + $form_state->getValue(['names_fieldset', $key])['weight'];
      $value = $value + round($Pieces * $width * $Length * $height / 166 / 2.2046, 2);
      $cubic = $cubic + round($Pieces * $width * $Length * $height / 1728 / 35.3146, 2);
    }
    // $value = round($width * $Length * $height / 166 / 2.2046, 2);
    $response = new AjaxResponse();
    $response->addCommand(
      new HtmlCommand(
        '.total_dimesnsiona', '<div class="my_top_message">' . t('Dimensional Weight(KG)') . '<br>' . $value . '</div>')
    );
    $response->addCommand(
      new HtmlCommand(
        '.total_Piecest', '<div class="my_top_message">' . t('Total Pieces') . '<br>' . $Pieces_total . '</div>')
    );
    $response->addCommand(
      new HtmlCommand(
        '.total_weight', '<div class="my_top_message">' . t('Total Weight(KG)') . '<br>' . $weight . '</div>')
    );
    $response->addCommand(
      new HtmlCommand(
        '.total_cubic', '<div class="my_top_message">' . t('Total Cubic Meters(CBM)') . '<br>' . $cubic . '</div>')
    );

    return $response;
  }

  /**
   * Form validation.
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $req_id = $form_state->getValues()['generalinfo']['request_id'];
    $data = db_select('transportations', 'n')
      ->fields('n', ['request_id'])
      ->execute()
      ->fetchAll();
    $data = array_column($data, 'request_id');
    if (in_array($req_id, $data)) {
      return $form_state->setErrorByName('request_id', t('ICAT ID already exists.'));
    }
  }

  /**
   *
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $conn = Database::getConnection();

    $hight = $form_state->getValue(['names_fieldset', 'height']);
    $weights = $form_state->getValue(['names_fieldset', 'weight']);
    $identity = '';
    $num_names = $form_state->get('num_names');
    // drupal_set_message('Weight<pre>' . print_r($num_names, true) . '</pre>');.
    foreach ($num_names as $key) {

      $Pieces = $form_state->getValue(['names_fieldset', $key])['Pieces'];
      $Pieces_total = $Pieces_total + $form_state->getValue(['names_fieldset', $key])['Pieces'];
      $Length = $form_state->getValue(['names_fieldset', $key])['Length'];
      $width = $form_state->getValue(['names_fieldset', $key])['width'];
      $height = $form_state->getValue(['names_fieldset', $key])['height'];
      $weight = $weight + $form_state->getValue(['names_fieldset', $key])['weight'];
      $value = $value + round($Pieces * $width * $Length * $height / 166 / 2.2046, 2);
      $cubic = $cubic + round($Pieces * $width * $Length * $height / 1728 / 35.3146, 2);

      $data[] = $form_state->getValue(['names_fieldset', $key]);
    }
    $result = json_encode($data);
    // $value = round($width * $Length * $height / 166 / 2.2046, 2);
    $vendor = array_keys($form_state->getValues()['fieldset_testing']['states_wrapper']['states']);
    if (!empty($vendor)) {
      $vendor = implode(", ", $vendor);
    }
    else {
      $vendor = '';
    }
    $abc = explode(",", $vendor);
    $accountssss = User::loadMultiple($abc);
    $conn->insert('transportations')->fields(
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
        'vendor' => $vendor,
        'total_pieces' => $Pieces_total,
        'total_weight' => $weight,
        'dimesnsional_weight' => $value,
        'total_cubic' => $cubic,
        'name' => $form_state->getValues()['addressdetail']['name'],
        'quote_deadline' => $form_state->getValues()['generalinfo']['quote_deadline'],
        'email' => $form_state->getValues()['addressdetail']['email'],
        'freight_details' => $result,
        'archive' => $form_state->getValues()['fieldset_help']['archieve'],
        'country' => $form_state->getValues()['fieldset_testing']['country'],
      ]
    )->execute();
    $account = \Drupal::currentUser();
    $test_address = 'deepbha@mailinator.com,deepbhalla@mailinator.com';
    drupal_set_message('Form submit successfully.');
    $params['subject'] = $this->t('ICAT Logistics - New project created for Quote - @kol', ['@kol' => $form_state->getValues()['generalinfo']['request_id']]);
    // $params['body'] = [$this->t('<p>Greetings from ICAT Logistics,</p><p>Please see the link below for the new RFQ available to you. This RFQ will expire by: </p><p>To access please login using the URL below<br>@link</p><p>Please let the ICAT Operations team know if you have any questions.</p><p>Thank you,</p><p>ICAT Operations</p>',array('@link' => 'http://icatlogistics.servehttp.com' ))];
    $params['body'] = [$this->t('<p>Greetings from ICAT Logistics,</p><p>Please see the link below for the new RFQ available to you.</p><p>This RFQ will expire by: @hjk</p><p>To access please login using the URL below<br>@link</p><p>Please let the ICAT Operations team know if you have any questions.</p><p>Thank you,</p><p>ICAT Operations</p>', ['@hjk' => $form_state->getValues()['generalinfo']['quote_deadline'], '@link' => 'http://icatlogistics.servehttp.com'])];
    foreach ($accountssss as $accounts) {
      $mailing = $accounts->getEmail();
      \Drupal::service('plugin.manager.mail')->mail('smtp', 'smtp-test', $mailing, 'en', $params);
    }
    $url = Url::fromRoute('view.transportations.page_1');
    $form_state->setRedirectUrl($url);

  }

}
