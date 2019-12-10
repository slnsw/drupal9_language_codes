<?php
/**
 * @author Karthikeyan Manivasagam
 * @author Karthikeyan Manivasagam <karthikeyanm.inbox@gmail.com>
 */
namespace Drupal\language_codes\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use \Drupal\taxonomy\Entity\Vocabulary;
use Drupal\Core\Ajax\HtmlCommand;
use Drupal\Core\Ajax\AjaxResponse;
use \Drupal\taxonomy\Entity\Term;

/**
 * Configure text to speech settings.
 */
class LanguageCodesSettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'language_codes_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'language_codes.settings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('language_codes.settings');

    $form['base_language'] = array(
        '#title' => 'Language code listing language/format',
        '#description' => 'Search by lang code (ex). en_US. Please don\'t change this field if you get the correct list table , if you wish to get the language list in the native language then you can change. for example if you want the language Names to be shown in chinese or japanese or france or german or mandrin etc.',
        '#type' => 'textfield',
        '#autocomplete_route_name' => 'language_codes.languagelist',
        '#autocomplete_route_parameters' => array('field_name' => 'base_language', 'count' => 20),
        '#default_value' => $config->get('base_language') ? $config->get('base_language') : 'i18n',
    );

$form['language_table'] = array(
  '#type' => 'details',
  '#title' => t('Language List'),
  '#description' => t('List of language code for the  above selected language format'),
  '#open' => FALSE,
);

$header = [
     'code' => t('Language Code'),
     'name' => t('Language Name'),
   ];

// Initialize an empty array
$output = array();
$langManager = \Drupal::service("language_codes.manager");
$results = $langManager->getLanguageList($config->get('base_language'), FALSE);
// Next, loop through the $results array
foreach ($results as $key => $value) {

       $output[$key] = [
         'code' => $key,     
         'name' => $value,
       ];

   }
//print_r($config->get('language_list')); die;
 $form['language_table']['table'] = [
'#type' => 'tableselect',
'#header' => $header,
'#options' => $output,
'#default_value' => $config->get('language_list'),
'#empty' => t('No language found'),
];

  $vocabulary_types =  taxonomy_vocabulary_get_names();
    if (empty($vocabulary_types)) {
      return NULL;
    }
    $vocabularies = Vocabulary::loadMultiple();
    
    $options = array();
    foreach ($vocabularies as $vocabulary => $type) {
      $options[$vocabulary] = $type->get('name');
    }

$form['generate'] = array(
  '#type' => 'details',
  '#title' => t('Generate language code as Term'),
  '#description' => t('Generate terms'),
  '#open' => FALSE,
);
    $form['generate']['vocabulary'] = array(
      '#title' => t('Vocabulary'),
      '#type' => 'select',
      '#description' => t('Select the vocabulary to generate language list'),
      '#options' => $options,
      '#ajax' => [
          'callback' => '::getFieldsList',
          'event' => 'change',
          'method' => 'html',
          'wrapper' => 'language_codes_fields',
          'progress' => [
                'type' => 'throbber',
                 'message' => NULL,
                ],
          ],
    );

    $form['generate']['fields'] = array(
      '#title' => t('Select a Field to add language code'),
      '#type' => 'select',
      '#description' => t('By default Term name will be Language Name and code needs one more field to be save, this selected field will have language code for the seleceted vocabulary type.'),
      '#options' => [],
      '#validated' => TRUE,
      '#id' => 'language_codes_fields'
    );

    $form['generate']['code_as_name'] = array(
      '#title' => t('Create Language code as Term Name'),
      '#type' => 'checkbox',
      '#description' => t('Select this option if you want to create language code as term name and use the above selected field to save language name'),
    );

      $form['generate']['generate_now'] = [
        '#type' => 'submit',
        '#value' => $this->t('Generate Term'), 
        '#description' => $this->t("It will generate the terms for the above vocabulary"),
        '#submit' => array('::generateTerm'),
      ];
   //}
    return parent::buildForm($form, $form_state);
  }


public function getFieldsList(array &$element, FormStateInterface $form_state) {
    $triggeringElement = $form_state->getTriggeringElement();
    $bundle = $triggeringElement['#value'];
    $langManager = \Drupal::service("language_codes.manager");
     $options  = $langManager->getFields('taxonomy_term',$bundle);
    $renderedField = '';

    foreach ($options as $key => $value) {
      if($value['type'] == "string" && strpos($key, 'field_') !== false ) {
       $renderedField .= "<option value='".$key."'>".$value['label']."</option>";
      }  
    }
    $response = new AjaxResponse();
    $response->addCommand(new HtmlCommand("#language_codes_fields", $renderedField));
    return $response;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

    $config = $this->config('language_codes.settings');
$base_language = trim($form_state->getValue('base_language'));
 $table = $form_state->getValue('table'); 
         if($base_language != $config->get('base_language')) {
          array_walk($table, function (&$v, $k) { $v = 0; }); 
         }

   $this->config('language_codes.settings', $base_language)
      ->set('base_language', $base_language);

  $this->config('language_codes.settings')->set('language_list',$table);
  
   $this->config('language_codes.settings')->save(); 
      
    parent::submitForm($form, $form_state);
  }

  public function generateTerm(array &$form, FormStateInterface $form_state) {
    //print_r($form_state->getValue('fields'));  print_r($form_state->getValue('generate_now')); die;
     if($form_state->getValue('fields') != "") {
        $code_as_name = $form_state->getValue('code_as_name');
        $vocabulary = $form_state->getValue('vocabulary');
        $field = $form_state->getValue('fields');
        $langManager = \Drupal::service("language_codes.manager");
        $results = $langManager->getSelectedLanguageList();
        $i = 1;
          foreach($results as $key => $value ) {
  $name = $value;
  $field_value = $key;
  if($code_as_name == TRUE) {
  $name = $key;
  $field_value = $value;
  }
  if(!$this->checkTermExist($name, $vocabulary)) {
  $term = Term::create([
    'name' => $name, 
    'vid' => $vocabulary,
  ]);
  $term->set($field, $field_value);
  $term->save();
  $i++;
  }
  }
if($i > 0) drupal_set_message($this->t("Language Terms created successfully"));
      }
  } 

  public function checkTermExist($name, $vid) {
    $term = \Drupal::entityTypeManager()
    ->getStorage('taxonomy_term')
    ->loadByProperties(['name' => $name, 'vid' => $vid]);
  $term = reset($term);
  if(!empty($term)) return TRUE;
   return FALSE;
  }

}
