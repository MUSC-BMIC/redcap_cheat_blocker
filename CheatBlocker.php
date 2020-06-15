<?php

namespace MUSC\CheatBlocker;

use REDCap;

class CheatBlocker extends \ExternalModules\AbstractExternalModule {

   // this only pertains to the setup page for Cheat Blocker
  function redcap_every_page_top(int $project_id) {
    if (strpos(PAGE, 'ExternalModules/manager/project.php') !== false) {
      $this->setJsSettings('cheatBlockerSettings', array('modulePrefix' => $this->PREFIX, 'useOldVal' => 'false'));

      // Get all field variable names in project
      // Get the data dictionary for the current project in array format
      $filtered_dd_array = array();
      $dd_array = REDCap::getDataDictionary('array');

      foreach ($dd_array as $field_name => $field_attributes) {
        array_push($filtered_dd_array, $field_name);
      }

      $this->setJsSettings('cheatBlockerFields', $dd_array);
      $this->setJsSettings('cheatBlockerValidFieldNameOptions', $filtered_dd_array);
      $this->includeJs('js/cheat_blocker.js');
      $this->includeJs('js/bootstrap-select.min.js');
      $this->includeCss('css/config.css');
      $this->includeCss('css/bootstrap-select.min.css');
    }
  }

  function init_page_top($project_id, $record, $instrument, $event_id, $group_id, $repeat_instance) {
    // this pertains to data entry forms and surveys
    $config = $this->getProjectSettings();
    $modal_title = $config['modal_title']['value'];

    echo "
      <div id='cheat-blocker-modal' class='modal fade' role='dialog' data-backdrop='static'>
        <div class='modal-dialog'>
          <div class='modal-content'>
            <div class='modal-header'>
              <h4 class='modal-title'>$modal_title<span class='module-name'></span></h4>
              <button type='button' class='close' data-dismiss='modal'>&times;</button>
            </div>
            <div class='modal-body'></div>
            <div class='modal-footer'>
              <button type='button' class='btn btn-defaultrc' id='btnCloseCodesModalDelete' data-dismiss='modal'>Continue</button>
            </div>
          </div>
        </div>
      </div>";

    $cbs = array(
      'url' => $this->getUrl('identify_duplicates.php', true, true),
      'accepted' => $config['accepted']['value'],
      'rejected' => $config['rejected']['value'],
      'eligibility_message' => $config['eligibility_message']['value']
    );

    $this->setJsSettings('cheatBlockerSettings', $cbs);
    $this->includeJs('js/identify_duplicates.js');
  }

  function redcap_data_entry_form_top($project_id, $record, $instrument, $event_id, $group_id, $repeat_instance) {
    $params = array('return_format' => 'array', 'records' => $record, 'fields' => $fields);

    $data = REDCap::getData($params);
    $record_data = $data[$record][$event_id];

    // this is a new record
    if (is_null($record)) {
      $this->init_page_top($project_id, $record, $instrument, $event_id, $group_id, $repeat_instance);
    }
    else{
      $this->init_page_top($project_id, $record, $instrument, $event_id, $group_id, $repeat_instance);
    }

  }

   function redcap_survey_page_top($project_id, $record, $instrument, $event_id, $group_id, $survey_hash, $response_id, $repeat_instance) {
    $this->init_page_top($project_id, $record, $instrument, $event_id, $group_id, $repeat_instance);
  }

  function check_for_duplicates($params) {
    $config = $this->getProjectSettings();
    $criteria_names = $config['criteria_name']['value'];
    $automatic_duplicate_check = $config['automatic_duplicate_check']['value'];

    $is_duplicate = $params['duplicate_check']['value'];
    $total_data_count = 0;

    //If automatic duplicate check is not set, new records will show eligibility message
    //After the admin sets the duplicate_check variable, it will be set and accepted/rejected message shows up
    if($automatic_duplicate_check == false){
      if($is_duplicate == ''){
        return array(is_duplicate => false, eligibility_message => true);
      }
      else{
        return array(is_duplicate => (int)$is_duplicate);
      }
    }

    if($automatic_duplicate_check == true && $is_duplicate == ""){
      $is_duplicate = false;
    }

    for($i = 0; $i < count($criteria_names); $i++) {
      $filter_logic = '';
      for($j = 0; $j < count($criteria_names[$i]); $j++){
        $name = $criteria_names[$i][$j];
        $filter_logic .= "[$name] = '$params[$name]'";
        $filter_logic .= ($j == count($criteria_names[$i]) - 1) ? "" : " AND ";
      }
      $total_data_count = $this->dataCount($filter_logic);

      if ($total_data_count > 0){
        $is_duplicate = true;
        error_log("FAILED". $filter_logic);
        break;
      }
    }

    return array(is_duplicate => $is_duplicate);
  }

  protected function dataCount($filter_logic) {
    $params = array('return_format' => 'array', 'filterLogic' => $filter_logic, 'fields' => array('record_id'));
    $data = REDCap::getData($params);
    return count($data);
  }

  protected function setJsSettings($var, $settings) {
    echo '<script>' . $var . ' = ' . json_encode($settings) . ';</script>';
  }

  protected function includeJs($path) {
    echo '<script src="' . $this->getUrl($path) . '"></script>';
  }

  protected function includeCss($path) {
    echo '<link rel="stylesheet" href="' . $this->getUrl($path) . '">';
  }

}

?>
