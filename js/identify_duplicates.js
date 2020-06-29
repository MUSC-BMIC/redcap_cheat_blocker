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
          eligibility_message = data.eligibility_message;

          $message = eligibility_message ? cheatBlockerSettings['eligibility_message'] : is_duplicate ? cheatBlockerSettings['rejected'] : cheatBlockerSettings['accepted'];
          $("#cheat-blocker-modal .modal-body").html($message);
          $('#cheat-blocker-modal').modal('show');

          console.log(is_duplicate);
          console.log(eligibility_message);

          $("#duplicate_check-tr .data :input").val(is_duplicate);

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

