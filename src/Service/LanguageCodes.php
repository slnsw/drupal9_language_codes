<?php
/**
 * @author Karthikeyan Manivasagam
 * @author Karthikeyan Manivasagam <karthikeyanm.inbox@gmail.com>
 * @file
 * Contains \Drupal\language_codes\Service.
 */
namespace Drupal\language_codes\Service;

use \Drupal\Core\Config\ConfigFactoryInterface;

/**
 * Contains the LanguageCodes.
 */
class LanguageCodes  {
	
    public $config;

/**
 * Creates a config of google_text_to_speech.settings.
 *
 * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
 *   The config factory.
 */
public function __construct(ConfigFactoryInterface $config_factory) {
  $this->config = $config_factory->get('language_codes.settings');
}

  public function getLanguageList($baseLang = "i18n", $includeI18n = false) {
      $module_handler = \Drupal::service('module_handler');
      $module_path = $module_handler->getModule('language_codes')->getPath();
      $baseLang = $this->getBaseLangFromString($baseLang);
      $path = $baseLang == "i18n" ? "i18n.json" : $baseLang."/language.json";
      $json = file_get_contents(DRUPAL_ROOT."/".$module_path."/data/".$path);
      $content = (array) json_decode($json);
      if($baseLang == "i18n" && $includeI18n)  $content["i18n"] = "i18n";
      return $content;
  }

  public function searchArray($string, $array) {
    $result = array_filter($array, function ($key, $val) use ($string) {
    $string = str_replace(array( '(', ')' ), '', $string);
    $val = str_replace(array( '(', ')' ), '', $val);
      if (stripos($key, $string) !== false || stripos($val, $string) !== false ) {
          return true;
      }
      return false;
     }, ARRAY_FILTER_USE_BOTH);
   return $result;
  }


  public function searchLanguage($string, $includeI18n = false, $baseLang = "i18n") {
       $baseLang = $this->getBaseLangFromString($baseLang);
       $list = $this->getLanguageList($baseLang, $includeI18n);
       return  $this->searchArray($string, $list);
  }

  public function getBaseLangFromString($string) {
    if (strpos($string, '-') !== false) {
      $array = explode('-', $string);
      return $array[1];
    }
    return $string;
  }

  public function getSelectedLanguageList() {
    $baseLanguage = $this->config->get('base_language');
    $list = $this->getLanguageList($baseLanguage);
    $return = [];
    $selectedList = $this->config->get('language_list');
    foreach ($selectedList as $key => $value) {
       if($value !== 0) {
        $return[$key] = $list[$key];
       }
    }
    return $return;
  }

  public function getFields($entity_type,$bundle) {
    $entity_type_id = $entity_type;
    $bundle = $bundle;
    foreach (\Drupal::entityManager()->getFieldDefinitions($entity_type_id, $bundle) as $field_name => $field_definition) {
     // if (!empty($field_definition->getTargetBundle())) {
        $bundleFields[$field_name]['type'] = $field_definition->getType();
        $bundleFields[$field_name]['label'] = $field_definition->getLabel();
     // }
    }
    return  $bundleFields;
  }
}