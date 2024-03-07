<?php

namespace MUSC\CheatBlocker;

use REDCap;

class CheatBlocker extends \ExternalModules\AbstractExternalModule {

   // this only pertains to the setup page for Cheat Blocker
  function redcap_every_page_top(int $project_id) {
    if (strpos(PAGE, 'ExternalModules/manager/project.php') !== false) {
      $this->setJsSettings('cheatBlockerSettings', array('modulePrefix' => $this->PREFIX));

      // Get all field variable names in project
      // Get the data dictionary for the current project in array format
      $allowable_field_types = array('dropdown', 'radio', 'text', 'calc');

      $filtered_dd_array = array();
      $dd_array = REDCap::getDataDictionary('array');

      //also exclude hidden fields from the dropdown list
      foreach ($dd_array as $field_name => $field_attributes) {
        if (in_array($field_attributes['field_type'], $allowable_field_types) && trim($field_attributes['field_annotation']) != '@HIDDEN-SURVEY') {
          array_push($filtered_dd_array, $field_name);
        }
      }

      $this->setJsSettings('cheatBlockerFields', $dd_array);
      $this->setJsSettings('cheatBlockerValidFieldNameOptions', $filtered_dd_array);
      $this->includeJs('js/cheat_blocker.js');
      $this->includeCss('css/config.css');

      //render the JS file only if quota_config is not enabled
      $enabledModules = \ExternalModules\ExternalModules::getEnabledModules($_GET['pid']);
      if (!isset($enabledModules['quota_config'])){
        $this->includeJs('js/bootstrap-select.min.js');
        $this->includeCss('css/bootstrap-select.min.css');
      }

    }
  }

  function init_page_top($project_id, $record, $instrument, $event_id, $group_id, $repeat_instance) {
    // this pertains to data entry forms and surveys

    //Continue with this method only if quota_config is not enabled
    $enabledModules = \ExternalModules\ExternalModules::getEnabledModules($_GET['pid']);
    if (isset($enabledModules['quota_config'])){
      return;
    }

    $config = $this->getProjectSettings();
    $modal_title = $config['modal_title'];

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
      'accepted' => $config['accepted'],
      'rejected' => $config['rejected'],
      'eligibility_message' => $config['eligibility_message'],
      'potential_duplicate_message' => $config['potential_duplicate_message']
    );

    $this->setJsSettings('cheatBlockerSettings', $cbs);
    $this->includeJs('js/identify_duplicates.js');

  }

  function redcap_data_entry_form_top($project_id, $record, $instrument, $event_id, $group_id, $repeat_instance) {
    $duplicate_check_yn = $this->run_duplicate_check_for_selected_instrument_and_event($record, $event_id, $instrument);
    if($duplicate_check_yn){
      $this->init_page_top($project_id, $record, $instrument, $event_id, $group_id, $repeat_instance);
    }
  }

  function redcap_survey_page_top($project_id, $record, $instrument, $event_id, $group_id, $survey_hash, $response_id, $repeat_instance) {
    $duplicate_check_yn = $this->run_duplicate_check_for_selected_instrument_and_event($record, $event_id, $instrument);
    if($duplicate_check_yn){
      $this->init_page_top($project_id, $record, $instrument, $event_id, $group_id, $repeat_instance);
    }
  }

  function run_duplicate_check_for_selected_instrument_and_event($record, $event_id, $instrument){

    $data = REDCap::getData('array', $record);
    $record_data = $data[$record][$event_id];
    $instrument_yn = false; $event_yn = false;

    //Check for specific instrument name
    //Find the instrument that has the duplicate_check variable
    //Return true if the current instrument has the variable
    $instrument_names = REDCap::getInstrumentNames();

    foreach ($instrument_names as $instrument_name=>$instrument_label){
      $instrument_fields = REDCap::getFieldNames($instrument_name);
      foreach ($instrument_fields as $field_name=>$field_label){
        if($field_label == 'duplicate_check' && $instrument == $instrument_name){
          $instrument_yn = true;
        }
      }
    }

    //Check for specific event name
    //If events are NOT specified (which means its not a longitudinal project), no checks are required
    //If there are events, then run the duplicate check for only the baseline event

    if (!REDCap::isLongitudinal()){
      $event_yn = true;
    } else {
      $events = REDCap::getEventNames(false, true);
      $first_event_id = array_shift(array_keys($events));//Get the first event which is the baseline event
      if($event_id == $first_event_id){
        $event_yn = true;
      }
    }

    if($instrument_yn && $event_yn){
      return true;
    }

    return false;

  }

  function check_for_duplicates($params) {
    $config = $this->getProjectSettings();
    $criteria_names = $config['criteria_name'];
    $automatic_duplicate_check = $config['automatic_duplicate_check'];

    $is_duplicate = $params['duplicate_check'];
    $current_record_data_entry_time = date('m/d/Y H:i:s', $_SERVER['REQUEST_TIME']);

    $duplicate_array = $this->duplicate_check_by_iteration($params, $criteria_names);

    //Different scenarios if automatic duplicate check is not set

    if($automatic_duplicate_check == false){
        //Show eligibility message when a new record comes in
        //Update Potential Duplicate Record IDs and Potential Failed Criteria fields if its a duplicate
      if($is_duplicate == ''){
        return array("is_duplicate" => '', "automatic_duplicate_check" => false, "cheat_eligibility_message" => true, "data_entry_time" => $current_record_data_entry_time,
                    "potential_duplicate_record_ids" => $duplicate_array["duplicate_record_ids"], "potential_failed_criteria" => $duplicate_array["failed_criteria"]);
      }

      //If the admin marks as not a duplicate, but if the record is a potential duplicate, potential duplicate message shows up
      if($is_duplicate == 0 && $duplicate_array["is_duplicate"] == 1){
        return array("is_duplicate" => 0, "automatic_duplicate_check" => false, "potential_duplicate_message" => true,
                    "potential_duplicate_record_ids" => $duplicate_array["duplicate_record_ids"], "potential_failed_criteria" => $duplicate_array["failed_criteria"]);
      }
      else{
        return array("is_duplicate" => (int)$is_duplicate, "automatic_duplicate_check" => false, "duplicate_record_ids" => $duplicate_array["duplicate_record_ids"],
                    "failed_criteria" => $duplicate_array["failed_criteria"], "duplicates_count" => $duplicate_array["duplicates_count"]);
      }

    }

    return array("is_duplicate" => $duplicate_array["is_duplicate"], "failed_criteria" => $duplicate_array["failed_criteria"], "duplicates_count" => $duplicate_array["duplicates_count"], "duplicate_record_ids" => $duplicate_array["duplicate_record_ids"], "data_entry_time" => $duplicate_array["data_entry_time"]);
  }

  protected function duplicate_check_by_iteration($params, $criteria_names){

    // Get all the existing records via getData method
    // Iterate each record and check each criteria
    // For each record and for each criteria, if the field name of the new record that comes in matches
    // add yes to duplicate array
    // Finally check the duplicate array
    // Atleast 1 false in duplicate array indicates that the record is NOT a duplicate
    // Iterate through all records to find all the records that might be duplicates

    $current_record = $params['record_id'];
    $params_array = array('return_format'=>'array', 'filterLogic' => "[record_id] <> '$current_record'", 'fields' => array());
    $data = REDCap::getData($params_array); //get all the records excluding the current record

    $duplicate_exists = false; $failed_criteria_exists = ''; //these 2 variables are used to detect the duplicate & failed criteria
    $duplicate_record_ids = '';

    $current_record_data_entry_time = date('m/d/Y H:i:s', $_SERVER['REQUEST_TIME']);
    $comparison_days = $this->get_days_from_config_file();
    $duplicates_count = 0;

    //Looping through each record even though there is a duplicate
    //Doesn't stop when a duplicate is detected
    //This would get all the duplicate ids
    foreach ($data as $field => $value) {
      $event_id = $params['event_id'];
      $is_duplicate = false; //this should be reset for each record
      $criteria_query = ''; $failed_criteria = ''; //these 2 variables is reset for each record
      $existing_record_data_entry_time = $value[$event_id]['data_entry_time'];
      $date_within = date('m/d/Y H:i:s', strtotime($existing_record_data_entry_time. ' + ' . $comparison_days . ' days')); //days within 6 months or time period in the config file

      for($i = 0; $i < count($criteria_names); $i++) {
        $criteria_query = '(';
        $duplicate_array = array();
        for($j = 0; $j < count($criteria_names[$i]); $j++){
          $criteria = $criteria_names[$i][$j];
          $existing_record = $value[$event_id][$criteria];
          $new_record = $params[$criteria];

          $criteria_query .= $criteria;
          $criteria_query .= ($j == count($criteria_names[$i]) - 1) ? "" : " AND ";

          //Remove all special characters from phone number fields
          if($criteria == 'telephone'){
            $existing_record = preg_replace('/(\W*)/', '', $existing_record);
            $new_record = preg_replace('/(\W*)/', '', $new_record);
          }

          //Case sensitive check for text fields
          if(strtolower($existing_record) == strtolower($new_record) && strtotime($current_record_data_entry_time) < strtotime($date_within)){
            array_push($duplicate_array, true);
          }
          else{
            array_push($duplicate_array, false);
          }
        }

        $criteria_query .= ')';

        // If the duplicate array has all true values, then the new record is a duplicate
        // Add to failed_criteria if it's a duplicate
        if (in_array(false, $duplicate_array) == false){
          $is_duplicate = true;
          $duplicate_exists = true;
          $failed_criteria .= empty($failed_criteria) ? $criteria_query : " OR " . $criteria_query;
        }

      }

      //For each record that is a duplicate, get the failed criteria & add to the duplicated_record_ids
      if($is_duplicate){
        $duplicates_count += 1;
        $failed_criteria_exists = $failed_criteria;
        $duplicate_record_ids .= empty($duplicate_record_ids) ? $field : ", " . $field;
      }

    }

    if($duplicate_exists){
      return array("is_duplicate" => (int)$duplicate_exists, "failed_criteria" => $failed_criteria_exists, "duplicates_count" => $duplicates_count, "duplicate_record_ids" => $duplicate_record_ids, "data_entry_time" => $current_record_data_entry_time);
    }

    return array("is_duplicate" => 0, "data_entry_time" => $current_record_data_entry_time);
  }

  protected function get_days_from_config_file(){
    $config = $this->getProjectSettings();
    $compare_dates_number = $config['compare_dates_number'];
    $time_period = $config['time_period'];

    if(is_null($compare_dates_number) || is_null($time_period)){
      return 100 * 365; //100 years which is a random number
    }

    switch ($time_period) {
      case 'days':
        return $compare_dates_number * 1;
        break;
      case 'weeks':
        return $compare_dates_number * 7;
        break;
      case 'months':
        return $compare_dates_number * 30;
        break;
      case 'years':
        return $compare_dates_number * 365;
        break;
    }

    return $compare_dates_number;
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
