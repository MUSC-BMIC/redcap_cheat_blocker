setTimeout(function() {
  $(function() {

    function identifyDuplicates(e) {
      $is_duplicate = false;
      $form_data = $('form').serialize() + '&event_id=' + event_id;

      $.get({
        url: cheatBlockerSettings.url,
        async: false,
        data: $form_data,
        success: function(data) {
          data = JSON.parse(data);
          is_duplicate = data.is_duplicate;
          automatic_duplicate_check = data.automatic_duplicate_check;
          eligibility_message = data.cheat_eligibility_message;
          potential_duplicate_message = data.potential_duplicate_message;
          potential_duplicate_record_ids = data.potential_duplicate_record_ids;
          potential_failed_criteria = data.potential_failed_criteria;
          duplicate_record_ids = data.duplicate_record_ids;
          failed_criteria = data.failed_criteria;
          duplicates_count = data.duplicates_count;
          data_entry_time = data.data_entry_time;

          if(automatic_duplicate_check == false){
            $message = eligibility_message ? cheatBlockerSettings['eligibility_message'] : potential_duplicate_message ? cheatBlockerSettings['potential_duplicate_message'] : is_duplicate ? cheatBlockerSettings['rejected'] : cheatBlockerSettings['accepted'];
          }
          else{
            $message = is_duplicate ? cheatBlockerSettings['rejected'] : cheatBlockerSettings['accepted'];
          }

          $("#cheat-blocker-modal .modal-body").html($message);
          $('#cheat-blocker-modal').modal('show');

          $("#duplicate_check-tr :input").val(is_duplicate);
          $("#pot_duplicate_record_ids-tr :input").val(potential_duplicate_record_ids);
          $("#potential_failed_criteria-tr :input").val(potential_failed_criteria);
          $("#duplicate_record_ids-tr :input").val(duplicate_record_ids);
          $("#failed_criteria-tr :input").val(failed_criteria);
          $("#duplicates_count-tr :input").val(duplicates_count);
          if(data_entry_time){
            $("#data_entry_time-tr :input").val(data_entry_time);
          }


          e.preventDefault();
          e.stopPropagation();
          e.stopImmediatePropagation();

          $('#cheat-blocker-modal').off('hidden.bs.modal');
          $('#cheat-blocker-modal').on('hidden.bs.modal', function (e2) {
            dataEntrySubmit(e.target.id);
          });
        }
      });
    }

    var submitBtns = $("[id^=submit-btn-save], [name^=submit-btn-save]");

    submitBtns.prop("onclick", null).off("click");
    submitBtns.each((i, elt) => {
      elt.onclick = identifyDuplicates;
    });
  });
}, 0);
