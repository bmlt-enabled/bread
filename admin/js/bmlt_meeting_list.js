var s = document.getElementById('extra_meetings').options,
    l = [],
    d = '';
for(i = 0;i < s.length;i++) {
    column = s[i].text.split(';');
    for(j = 0;j < column.length;j++) {
        if(!l[j]) {
            l[j] = 0;
        }
        if(column[j].length > l[j]) {
            l[j] = column[j].length;
        }
    }
}
for(i = 0;i < s.length;i++) {
    column = s[i].text.split(';');
    temp_line = '';
    for(j = 0;j < column.length;j++) {
        t = (l[j] - column[j].length);
        d = '\u00a0';
        for(k = 0;k < t;k++) {
            d += '\u00a0';
        }
        temp_line += column[j] + d;
    }
    s[i].text = temp_line;
}
var $ml = jQuery.noConflict
jQuery(document).ready(
    function($ml) {
        $ml(".connecting").hide();
        $ml(".saving").hide();
        $ml(".bmlt-accrodian").accordion(
            {
                heightStyle: "content",
                active: false,
                collapsible: true
            }
        );
        $ml(".bmlt_color").spectrum();
        $ml("#col_color").on("click", ()=>$ml("#triggerSet").spectrum("set", this.val()));
        $ml("#bmlt_meeting_list_options").on(
            "keypress", function(event) {
                if(event.which == 13 && !event.shiftKey) {
                    event.preventDefault();
                    return false;
                }
            }
        );
        $ml("#bmltmeetinglistsave").click(
            function(e) {
                $ml(".saving").show();
            }
        );
        $ml("#submit_booklet").click(
            function(e) {
                e.preventDefault();
                $ml('#basicModal3').dialog('open');
            }
        );
        $ml('#basicModal3').dialog(
            {
                autoOpen: false,
                width: 'auto',
                title: "Are you sure?",
                modal: true,
                buttons: {
                    "Confirm": function(e) {
                        $ml(this).dialog('close');
                        $ml(".saving").show();
                        $ml("#booklet_default_settings").submit();
                    },
                    "Cancel": function() {
                        $ml(this).dialog('close');
                    }
                }
            }
        );
        $ml("#submit_four_column").click(
            function(e) {
                e.preventDefault();
                $ml('#basicModal2').dialog('open');
            }
        );
        $ml('#basicModal2').dialog(
            {
                autoOpen: false,
                width: 'auto',
                title: "Are you sure?",
                modal: true,
                buttons: {
                    "Confirm": function(e) {
                        $ml(this).dialog('close');
                        $ml(".saving").show();
                        $ml("#four_column_default_settings").submit();
                    },
                    "Cancel": function() {
                        $ml(this).dialog('close');
                    }
                }
            }
        );
        $ml("#submit_three_column").click(
            function(e) {
                e.preventDefault();
                $ml('#basicModal1').dialog('open');
            }
        );
        $ml('#basicModal1').dialog(
            {
                autoOpen: false,
                width: 'auto',
                title: "Are you sure?",
                modal: true,
                buttons: {
                    "Confirm": function(e) {
                        $ml(this).dialog('close');
                        $ml(".saving").show();
                        $ml("#three_column_default_settings").submit();
                    },
                    "Cancel": function() {
                        $ml(this).dialog('close');
                    }
                }
            }
        );
        $ml('input[name="submit_import_file"]').on(
            'click', function(e) {
                e.preventDefault();
                var import_file_val = $ml('input[name=import_file]').val();
                if(import_file_val == false) {
                    $ml('#nofileModal').dialog('open');
                } else {
                    $ml('#basicModal').dialog('open');
                }
            }
        );
        $ml("#nofileModal").dialog(
            {
                autoOpen: false,
                modal: true,
                buttons: {
                    Ok: function() {
                        $ml(this).dialog("close");
                    }
                }
            }
        );
        $ml('#basicModal').dialog(
            {
                autoOpen: false,
                width: 'auto',
                title: "Are you sure?",
                modal: true,
                buttons: {
                    "Confirm": function(e) {
                        $ml(this).dialog('close');
                        $ml(".saving").show();
                        $ml('#form_import_file').submit();
                    },
                    "Cancel": function() {
                        $ml(this).dialog('close');
                    }
                }
            }
        );
        $ml('#root-server-button').bind(
            'click', function(e) {
                e.preventDefault();
                $ml('#root-server-video').bPopup(
                    {
                        transition: 'slideIn',
                        closeClass: 'b-close',
                        onClose: function() {
                            for(var player in mejs.players) {
                                mejs.players[player].media.stop();
                            }
                        }
                    }
                );
            }
        );
        $ml('.my-tooltip').each(function(i,e) {
        $ml(e).tooltipster(
            {
                contentAsHTML: true,
                theme: 'tooltipster-noir',
                trigger: 'click'
            }
        )});
        $ml("#meeting-list-tabs").tabs(
            {
                active: 0
            }
        );
        $ml('#meeting-list-tabs').tabs().addClass('ui-tabs-vertical ui-helper-clearfix');
        $ml("#container").removeClass('hide');
        var meeting_sort_val = $ml("#meeting_sort").val();
        $ml('.borough_by_suffix').hide();
        $ml('.county_by_suffix').hide();
        if(meeting_sort_val === 'borough_county') {
            $ml('.borough_by_suffix').show();
            $ml('.county_by_suffix').show();
        } else if(meeting_sort_val === 'borough') {
            $ml('.borough_by_suffix').show();
        } else if(meeting_sort_val === 'county') {
            $ml('.county_by_suffix').show();
        }
        $ml('.neighborhood_by_suffix').hide();
        $ml('.city_by_suffix').hide();
        if(meeting_sort_val === 'neighborhood_city') {
            $ml('.neighborhood_by_suffix').show();
            $ml('.city_by_suffix').show();
        }
        var user_defined_sub = false;
        $ml('.user_defined_headings').hide();
        if(meeting_sort_val === 'user_defined') {
            $ml('.user_defined_headings').show();
            if($ml("#subgrouping").val() != '') {
                user_defined_sub = true;
            }
        }
        if(meeting_sort_val == 'weekday_area'
            || meeting_sort_val == 'weekday_city'
            || meeting_sort_val == 'weekday_county'
            || meeting_sort_val == 'state'
            || user_defined_sub
        ) {
            $ml('.show_subheader').show();
        } else {
            $ml('.show_subheader').hide();
        }
        $ml("#suppress_heading").click(
            function() {
                var val = $ml("#suppress_heading:checked").val();
                if(val == 1) {
                    $ml("#header_options_div").hide();
                } else {
                    $ml("#header_options_div").show();
                }
            }
        );
        $ml("#meeting_sort").change(
            function() {
                var meeting_sort_val = $ml("#meeting_sort").val();
                $ml('.borough_by_suffix').hide();
                $ml('.county_by_suffix').hide();
                $ml('.neighborhood_by_suffix').hide();
                $ml('.city_by_suffix').hide();
                if(meeting_sort_val === 'borough_county') {
                    $ml('.borough_by_suffix').show();
                    $ml('.county_by_suffix').show();
                } else if(meeting_sort_val === 'borough') {
                    $ml('.borough_by_suffix').show();
                } else if(meeting_sort_val === 'county') {
                    $ml('.county_by_suffix').show();
                }
                if(meeting_sort_val === 'neighborhood_city') {
                    $ml('.neighborhood_by_suffix').show();
                    $ml('.city_by_suffix').show();
                }
                var user_defined_sub = false;
                $ml('.user_defined_headings').hide();
                if(meeting_sort_val === 'user_defined') {
                    $ml('.user_defined_headings').show();
                    if($ml("#subgrouping").val() != '') {
                        user_defined_sub = true;
                    }
                }
                if(meeting_sort_val == 'weekday_area'
                    || meeting_sort_val == 'weekday_city'
                    || meeting_sort_val == 'weekday_county'
                    || meeting_sort_val == 'state'
                    || user_defined_sub
                ) {
                    $ml('.show_subheader').show();
                } else {
                    $ml('.show_subheader').hide();
                }
            }
        );
        $ml("#subgrouping").click(
            function() {
                var user_defined_sub = false;
                $ml('.user_defined_headings').hide();
                if($ml("#meeting_sort").val() === 'user_defined') {
                    $ml('.user_defined_headings').show();
                    if($ml("#subgrouping").val() != '') {
                        $ml('.show_subheader').show();
                    } else {
                        $ml('.show_subheader').hide();
                    }
                }
            }
        );
        var time_clock_val = $ml('input[name=time_clock]:checked').val();
        if(time_clock_val == '24') {
            $ml('#option3').hide();
            $ml('label[for=option3]').hide();
        } else if(time_clock_val == '24fr') {
            $ml('#option3').hide();
            $ml('label[for=option3]').hide();
        } else {
            $ml('#option3').show();
            $ml('label[for=option3]').show();
        }
        $ml("#two").click(
            function() {
                var time_clock_val = $ml('input[name=time_clock]:checked').val();
                if(time_clock_val == '24') {
                    $ml('label[for=option1]').html('20:00');
                    $ml('label[for=option2]').html('20:00 - 21:00');
                    $ml('#option3').hide();
                    $ml('label[for=option3]').html('');
                } else if(time_clock_val == '24fr') {
                    $ml('label[for=option1]').html('20h00');
                    $ml('label[for=option2]').html('20h00 - 21h00');
                    $ml('#option3').hide();
                    $ml('label[for=option3]').html('');
                } else {
                    $ml('#option3').show();
                    $ml('label[for=option1]').html('8:00 PM');
                    $ml('label[for=option2]').html('8:00 PM - 9:00 PM');
                    $ml('label[for=option3]').html('8 - 9 PM');
                }
            }
        );
        $ml("#four").click(
            function() {
                var time_clock_val = $ml('input[name=time_clock]:checked').val();
                if(time_clock_val == '24') {
                    $ml('label[for=option1]').html('20:00');
                    $ml('label[for=option2]').html('20:00-21:00');
                    $ml('#option3').hide();
                    $ml('label[for=option3]').html('');
                } else if(time_clock_val == '24fr') {
                    $ml('#option3').hide();
                    $ml('label[for=option1]').html('20h00');
                    $ml('label[for=option2]').html('20h00-21h00');
                    $ml('#option3').hide();
                    $ml('label[for=option3]').html('');
                } else {
                    $ml('#option3').show();
                    $ml('label[for=option1]').html('8:00PM');
                    $ml('label[for=option2]').html('8:00PM-9:00PM');
                    $ml('label[for=option3]').html('8-9PM');
                }
            }
        );
        $ml("#time_clock12").click(
            function() {
                var remove_space_val = $ml('input[name=remove_space]:checked').val();
                $ml('#option3').show();
                $ml('label[for=option3]').show();
                if(remove_space_val == '1') {
                    $ml('label[for=option1]').html('8:00PM');
                    $ml('label[for=option2]').html('8:00PM-9:00PM');
                    $ml('label[for=option3]').html('8-9PM');
                } else {
                    $ml('label[for=option1]').html('8:00 PM');
                    $ml('label[for=option2]').html('8:00 PM - 9:00 PM');
                    $ml('label[for=option3]').html('8 - 9 PM');
                }
            }
        );
        $ml("#time_clock24").click(
            function() {
                var remove_space_val = $ml('input[name=remove_space]:checked').val();
                $ml('#option3').hide();
                $ml('label[for=option3]').html('');
                if(remove_space_val == '1') {
                    $ml('label[for=option1]').html('20:00');
                    $ml('label[for=option2]').html('20:00-21:00');
                } else {
                    $ml('label[for=option1]').html('20:00');
                    $ml('label[for=option2]').html('20:00 - 21:00');
                }
            }
        );
        $ml("#time_clock24fr").click(
            function() {
                var remove_space_val = $ml('input[name=remove_space]:checked').val();
                $ml('#option3').hide();
                $ml('label[for=option3]').html('');
                if(remove_space_val == '1') {
                    $ml('label[for=option1]').html('20h00');
                    $ml('label[for=option2]').html('20h00-21h00');
                } else {
                    $ml('label[for=option1]').html('20h00');
                    $ml('label[for=option2]').html('20h00 - 21h00');
                }
            }
        );
        var page_fold_val = $ml('input[name=page_fold]:checked').val();
        function bookletControlsShowHide() {
            $ml('#landscape').prop("checked", true);
            $ml('.booklet').show();
            $ml('.single-page').hide();
        }
        function singlePageControlsShowHide() {
            $ml('.booklet').hide();
            $ml('.single-page').show();
        }
        $ml('.single-page-check').on('click',singlePageControlsShowHide);
        $ml('.booklet-check').on('click',bookletControlsShowHide);
        $ml('input[name=page_fold]:checked').hasClass('booklet-check') && bookletControlsShowHide();
        $ml('input[name=page_fold]:checked').hasClass('single-page-check') && singlePageControlsShowHide();
        $ml(".service_body_select").chosen(
            {
                inherit_select_classes: true,
                width: "62%"
            }
        );
        $ml("#extra_meetings").chosen(
            {
                no_results_text: "Oops, nothing found!",
                width: "100%",
                placeholder_text_multiple: "Select Extra Meetings",
                search_contains: true
            }
        );
        $ml('#extra_meetings').on(
            'chosen:showing_dropdown', function(evt, params) {
                $ml(".ctrl_key").show();
            }
        );
        $ml('#extra_meetings').on(
            'chosen:hiding_dropdown', function(evt, params) {
                $ml(".ctrl_key").hide();
            }
        );
        $ml("#author_chosen").chosen(
            {
                no_results_text: "Oops, nothing found!",
                width: "100%",
                placeholder_text_multiple: "Select authors",
                search_contains: true
            }
        );
        $ml('#author_chosen').on(
            'chosen:showing_dropdown', function(evt, params) {
                $ml(".ctrl_key").show();
            }
        );
        $ml('#author_chosen').on(
            'chosen:hiding_dropdown', function(evt, params) {
                $ml(".ctrl_key").hide();
            }
        );
        /*
            $ml("#extra_meetings").select2({
            //tags: "true",
            placeholder: "Select Meetings",
            containerCssClass: 'tpx-select2-container select2-container-lg',
            dropdownCssClass: 'tpx-select2-drop',
            dropdownAutoWidth: true,
            allowClear: true,
            width: "100%",
            /* dropdownParent: $ml('.exactCenter'), */
        /*     minimumResultsForSearch: 1, */
        /* closeOnSelect: false, */
        /*         escapeMarkup: function (markup) { return markup; }
            }).maximizeSelect2Height({cushion: 100});
            $ml('.select2-choices').css('background-image','none').css('background-color','#111111 !important');
            */
        $ml("#meeting-list-tabs").tabs(
            {
                active: 0
            }
        );
        $ml("#meeting-list-tabs-wrapper").removeClass('hide');
        //  Define friendly index name
        var index = 'key';
        //  Define friendly data store name
        var dataStore = window.sessionStorage;
        //  Start magic!
        try {
            // getter: Fetch previous value
            var oldIndex = dataStore.getItem(index);
        } catch(e) {
            // getter: Always default to first tab in error state
            var oldIndex = 0;
        }
        $ml('#meeting-list-tabs').tabs(
            {
                // The zero-based index of the panel that is active (open)
                active: oldIndex,
                // Triggered after a tab has been activated
                activate: function(event, ui) {
                    //  Get future value
                    var newIndex = ui.newTab.parent().children().index(ui.newTab);
                    //  Set future value
                    dataStore.setItem(index, newIndex)
                }
            }
        );
        var aggregator = "https://aggregator.bmltenabled.org/main_server";
        $ml(window).on(
            "load", function() {
                if($ml('#use_aggregator').is(':checked')) {
                    $ml("#root_server").prop("readonly", true);
                }
            }
        );
        $ml('#use_aggregator').click(
            function() {
                if($ml(this).is(':checked')) {
                    $ml("#root_server").val(aggregator);
                    $ml("#root_server").prop("readonly", true);
                } else {
                    $ml("#root_server").val("");
                    $ml("#root_server").prop("readonly", false);
                }
            }
        );
        var rootServerValue = $ml('#root_server').val();
        if(~rootServerValue.indexOf(aggregator)) {
            $ml("#use_aggregator").prop("checked", true);
        }
    }
);
/**
 * Get Tab Key
 */
function getTabKey(href) {
    return href.replace('#', '');
  }
  /**
   * Hide all tabs
   */
  function hideAllTabs() {
      tabs.each(function(){
          var href = getTabKey(jQuery(this).attr('href'));
          jQuery('#' + href).hide();
      });
  }
  /**
   * Activate Tab
   */
  function activateTab(tab) {
      var href = getTabKey(tab.attr('href'));
      tabs.removeClass('nav-tab-active');
      tab.addClass('nav-tab-active');
      jQuery('#' + href).show();
  }
  jQuery(document).ready(function($){
      var activeTab, firstTab;
      // First load, activate first tab or tab with nav-tab-active class
      firstTab = false;
      activeTab = false;
      tabs = $('a.nav-tab');
      hideAllTabs();
      tabs.each(function(){
          var href = $(this).attr('href').replace('#', '');
          if (!firstTab) {
              firstTab = $(this);
          }
          if ($(this).hasClass('nav-tab-active')) {
              activeTab = $(this);
          }
      });
      if (!activeTab) {
          activeTab = firstTab;
      }
      activateTab(activeTab);
      //Click tab
      tabs.click(function(e) {
          e.preventDefault();
          hideAllTabs();
          activateTab($(this));
      });
  });
