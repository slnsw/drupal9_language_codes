<?php

namespace Drupal\language_codes\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Drupal\Component\Utility\Tags;
use Drupal\Component\Utility\Unicode;

/**
 * Defines a route controller for entity autocomplete form elements.
 */
class LanguageCodesController extends ControllerBase {

  /**
   * Handler for autocomplete request.
   */
  public function getLanguageList(Request $request, $field_name, $count) {
    $results = [];
    if ($input = $request->query->get('q')) {
      $typed_string = Tags::explode($input);
      $typed_string = mb_strtolower(array_pop($typed_string));
      $manager = \Drupal::Service('language_codes.manager');
      $result = $manager->searchLanguage($typed_string,TRUE);
      $i = 1;
      foreach ($result as $key => $value) {
        if($i > $count) break;
          $results[] = [
                'value' => $value.'-' . $key,
                'label' => $value . '-' . $key,
              ];
        $i++;
      }
    }

    return new JsonResponse($results);
  }

}