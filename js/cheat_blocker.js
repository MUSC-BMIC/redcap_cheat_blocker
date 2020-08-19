$(document).ready(function () {
    var $modal = $('#external-modules-configure-modal');

    $modal.on('DOMSubtreeModified', function (e) {

      if ($(this).data('module') !== cheatBlockerSettings.modulePrefix) {
        return;
      }

      var $target = $(e.target);
      if ($target.is('tr.sub_start.sub_parent')) {
          $target.find(".external-modules-add-instance").text('Add Criteria');
          $target.find(".external-modules-remove-instance").text('Remove Criteria');
      }
    });

    $modal.on('show.bs.modal', function () {

      // Making sure we are overriding this modules's modal only.
      if ($(this).data('module') !== cheatBlockerSettings.modulePrefix) {
          return;
      }

      $(document).ajaxComplete(function () {
        $modal.find("select[name*='criteria_name']").each(function () {
          cleanupFieldNameSelect();
        });

        $modal.find("select").each(function () {
          $(this).attr('data-live-search', true);
          $(this).selectpicker();
        });

      });

      /* Need to clear out the placeholder value that's assigned in the
       * 'rendered.bs.select hidden.bs.select' event handler so that the
       * user can actually use the search box for typeahead search.
       */
      $(document).on('shown.bs.select', function (e) {
        $(e.target).parent().find('input[type=search]').val('');
      });

      /* Need to assign this value to the search input whenever the bootstrap
       * select is closed because the general validation on all tds with class
       * 'requiredm' looks for a value for all interior inputs. By duplicating
       * the selected value in this input we can avoid unintentionally triggering
       * validation just because the typeahead search input is empty.
       */
      $(document).on('rendered.bs.select hidden.bs.select', function (e) {
        var $target = $(e.target);
        $target.parent().find('input[type=search]').val($target.val());
      });

      $(document).on('change', "select[name*='criteria_name']", function () {

        $modal.find("select").each(function () {
          $(this).attr('data-live-search', true);
          $(this).selectpicker();
        });

      });

    });

  });

function cleanupFieldNameSelect() {
    // clean up the dropdown so that only fields that should be used for duplicates are shown
    selector = "select[name*='criteria_name'] option"
    $.each($(selector), function () {
        if (cheatBlockerValidFieldNameOptions.indexOf($(this).val()) == -1) {
            $(this).remove();
        }
    });

    setTimeout(cleanupFieldNameSelect, 100);
}




