Language Codes

Language code provides Language taxonomy by providing list of language with code in all locale and allows to generate language with code as taxonomy term in any taxonomy vocabulary.

Purpose of the module is to get language list with its code as a taxonomy term and also as a normal array list as language service. you can map any taxonomy/vocabulary type as language taxonomy. just need to create empty taxonomy type and add one plain text field to it.

Steps

Enable the module
Create a new vocabulary/taxonomy and add one plain text field
Now navigate to language codes settings page and generate language terms
You can also use the service of language codes module

#include the service
$langManager = \Drupal::service("language_codes.manager");
#To get the selected language codes
$langManager->getSelectedLanguageList();

#To get the list of all language codes in a specific locale
$langManager->getLanguageList('zh_Hans_HK'); //i18n for default, en_US en_GB
$langManager->getLanguageList('i18n'); // default
$langManager->getLanguageList('en_US '); // en_US 
$langManager->getLanguageList('en_GB'); // en_GB

#To search across the languages 
$string = "en"; //search string
$langManager->searchLanguage($string); //search in i18n locale list by default it can be changed if want
 
// Search in Chinese (Simplified Han, Hong Kong SAR China) locale language list.
$langManager->searchLanguage($string, FALSE, "zh_Hans_HK");