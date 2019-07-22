var s = document.getElementsByTagName('SELECT')[5].options, 
  l = [],
  d = '';
for(i = 0; i < s.length; i++){
column = s[i].text.split(';');
for(j = 0; j < column.length; j++){
  if(!l[j]) l[j] = 0;
  if(column[j].length > l[j]){
	l[j] = column[j].length;
  }      
}    
}  
for(i = 0; i < s.length; i++){
column = s[i].text.split(';');
temp_line = '';
for(j = 0; j < column.length; j++){
  t = (l[j] - column[j].length);
  d = '\u00a0';
  for(k = 0; k < t; k++){
	d += '\u00a0';
  }
  temp_line += column[j] + d;
}
s[i].text = temp_line;    
}  
 function root_server_video() {
	jQuery('.tooltip').tooltipster('hide');
	jQuery('#root-server-video').bPopup({
		transition: 'slideIn',
		closeClass: 'b-close',
		onClose: function() {
			for (var player in mejs.players) {
				mejs.players[player].media.stop();
			}
		}
	});
};
function current_meeting_list_video() {
	jQuery('.tooltip').tooltipster('hide');
	jQuery('#current-meeting-list-video').bPopup({
		transition: 'slideIn',
		closeClass: 'b-close',
		onClose: function() {
			for (var player in mejs.players) {
				mejs.players[player].media.stop();
			}
		}
	});
};
function numbersonly(myfield, e, dec) {
	var key;
	var keychar;
	if (window.event)
		key = window.event.keyCode;
	else if (e)
		key = e.which;
	else
		return true;
	keychar = String.fromCharCode(key);
	// control keys
	if ((key == null) || (key == 0) || (key == 8) ||
		(key == 9) || (key == 13) || (key == 27))
		return true;
	// numbers
	else if ((("0123456789").indexOf(keychar) > -1))
		return true;
	// decimal point jump
	else if (dec && (keychar == ".")) {
		myfield.form.elements[dec].focus();
		return false;
	} else
		return false;
}
var $ml = jQuery.noConflict
 jQuery(document).ready(function($ml) {
	$ml(".connecting").hide();
	$ml(".saving").hide();
	$ml("#accordion").accordion({
		heightStyle: "content",
		active: false,
		collapsible: true
	});
	$ml("#accordion2").accordion({
		heightStyle: "content",
		active: false,
		collapsible: true
	});
	$ml("#accordion3").accordion({
		heightStyle: "content",
		active: false,
		collapsible: true
	});
	$ml("#accordion_asm").accordion({
		heightStyle: "content",
		active: false,
		collapsible: true
	});
	$ml("#col_color").spectrum({
		preferredFormat: "hex",
		showInput: true,
		showInitial: true,
		theme: "sp-light"
	});
	$ml("#col_color").click(function() {
		$ml("#triggerSet").spectrum("set", $("#col_color").val());
	});
	$ml("#header_text_color").spectrum({
		preferredFormat: "hex",
		showInput: true,
		showInitial: true,
		theme: "sp-light"
	});
	$ml("#header_text_color").click(function() {
		$ml("#triggerSet").spectrum("set", $("#header_text_color").val());
	});
	$ml("#header_background_color").spectrum({
		preferredFormat: "hex",
		showInput: true,
		showInitial: true,
		theme: "sp-light"
	});
	$ml("#header_background_color").click(function() {
		$ml("#triggerSet").spectrum("set", $("#header_background_color").val());
	});
	$ml("#bmlt_meeting_list_options").on("keypress", function(event) {
		if (event.which == 13 && !event.shiftKey) {
			event.preventDefault();
			return false;
		}
	});
	$ml("#bmltmeetinglistsave1").click(function(e) {
		$ml(".saving").show();
	});
	$ml("#bmltmeetinglistsave2").click(function(e) {
		$ml(".saving").show();
	});
	$ml("#bmltmeetinglistsave3").click(function(e) {
		$ml(".saving").show();
	});
	$ml("#bmltmeetinglistsave4").click(function(e) {
		$ml(".saving").show();
	});
	$ml("#bmltmeetinglistsave5").click(function(e) {
		$ml(".saving").show();
	});
	$ml("#bmltmeetinglistsave6").click(function(e) {
		$ml(".saving").show();
	});
	$ml("#submit_booklet").click(function(e) {
		e.preventDefault();
		$ml('#basicModal3').dialog('open');
	});
	$ml('#basicModal3').dialog({
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
	});
	$ml("#submit_four_column").click(function(e) {
		e.preventDefault();
		$ml('#basicModal2').dialog('open');
	});
	$ml('#basicModal2').dialog({
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
	});
	$ml("#submit_three_column").click(function(e) {
		e.preventDefault();
		$ml('#basicModal1').dialog('open');
	});
	$ml('#basicModal1').dialog({
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
	});
	$ml('input[name="submit_import_file"]').on('click', function(e) {
		e.preventDefault();
		var import_file_val = $ml('input[name=import_file]').val();
		if (import_file_val == false) {
			$ml('#nofileModal').dialog('open');
		} else {
			$ml('#basicModal').dialog('open');
		}
	});
	$ml("#nofileModal").dialog({
		autoOpen: false,
		modal: true,
		buttons: {
			Ok: function() {
				$ml(this).dialog("close");
			}
		}
	});
	$ml('#basicModal').dialog({
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
	});
	$ml('#root-server-button').bind('click', function(e) {
		e.preventDefault();
		$ml('#root-server-video').bPopup({
			transition: 'slideIn',
			closeClass: 'b-close',
			onClose: function() {
				for (var player in mejs.players) {
					mejs.players[player].media.stop();
				}
			}
		});
	});
	$ml('#service-body-button').bind('click', function(e) {
		e.preventDefault();
		$ml('#service-body-video').bPopup({
			transition: 'slideIn',
			closeClass: 'b-close',
			onClose: function() {
				for (var player in mejs.players) {
					mejs.players[player].media.stop();
				}
			}
		});
	});
	$ml('.my-tooltip').tooltipster({
		animation: 'grow',
		delay: 200,
		theme: 'tooltipster-noir',
		contentAsHTML: true,
		//positionTracker: false,
		touchDevices: false,
		hideOnClick: true,
		icon: '(?)',
		iconCloning: true,
		iconDesktop: true,
		iconTouch: false,
		iconTheme: 'tooltipster-icon',
		interactive: true,
		arrow: false,
		position: 'right',
		//maxWidth: 900,
		//offsetX: 150,
		offsetY: -200,
		trigger: 'click'
	});
	$ml('.tooltip').tooltipster({
		animation: 'grow',
		delay: 200,
		theme: 'tooltipster-noir',
		hideOnClick: true,
		contentAsHTML: true,
		positionTracker: false,
		icon: '(?)',
		iconCloning: true,
		iconDesktop: true,
		iconTouch: false,
		iconTheme: 'tooltipster-icon',
		interactive: true,
		arrow: true,
		position: 'right',
		trigger: 'click'
	});
	$ml('.bottom-tooltip').tooltipster({
		animation: 'grow',
		delay: 200,
		theme: 'tooltipster-noir',
		hideOnClick: true,
		contentAsHTML: true,
		positionTracker: false,
		icon: '(?)',
		iconCloning: true,
		iconDesktop: true,
		iconTouch: false,
		iconTheme: 'tooltipster-icon',
		interactive: true,
		arrow: true,
		position: 'bottom-left',
		offsetX: -10,
		trigger: 'click'
	});
	$ml('.top-tooltip').tooltipster({
		animation: 'grow',
		delay: 200,
		theme: 'tooltipster-noir',
		hideOnClick: true,
		contentAsHTML: true,
		positionTracker: false,
		icon: '(?)',
		iconCloning: true,
		iconDesktop: true,
		iconTouch: false,
		iconTheme: 'tooltipster-icon',
		interactive: true,
		arrow: true,
		position: 'top-left',
		offsetX: -10,
		trigger: 'click'
	});
	$ml('.top-middle-tooltip').tooltipster({
		animation: 'grow',
		delay: 200,
		theme: 'tooltipster-noir',
		hideOnClick: true,
		contentAsHTML: true,
		positionTracker: false,
		icon: '(?)',
		iconCloning: true,
		iconDesktop: true,
		iconTouch: false,
		iconTheme: 'tooltipster-icon',
		interactive: true,
		arrow: true,
		position: 'top',
		offsetX: -10,
		trigger: 'click'
	});
	$ml("#meeting-list-tabs").tabs({
		active: 0
	});
	$ml('#meeting-list-tabs').tabs().addClass('ui-tabs-vertical ui-helper-clearfix');
	$ml("#container").removeClass('hide');
	var meeting_sort_val = $ml("#meeting_sort").val();
	if (meeting_sort_val === 'day' || meeting_sort_val === 'weekday_area' || meeting_sort_val === 'weekday_city' ||  meeting_sort_val === 'weekday_county') {
		$ml('.weekday_start_div').show();
	} else {
        $ml('.weekday_start_div').hide();
	}
     $ml('.borough_by_suffix').hide();
     $ml('.county_by_suffix').hide();
     if (meeting_sort_val === 'borough_county') {
         $ml('.borough_by_suffix').show();
         $ml('.county_by_suffix').show();
     } else if (meeting_sort_val === 'borough') {
         $ml('.borough_by_suffix').show();
     } else if (meeting_sort_val === 'county') {
         $ml('.county_by_suffix').show();
     }
     $ml('.neighborhood_by_suffix').hide();
     $ml('.city_by_suffix').hide();
     if (meeting_sort_val === 'neighborhood_city') {
         $ml('.neighborhood_by_suffix').show();
         $ml('.city_by_suffix').show();
     }
	$ml("#meeting_sort").click(function() {
		var meeting_sort_val = $ml("#meeting_sort").val();
        $ml('.borough_by_suffix').hide();
        $ml('.county_by_suffix').hide();
        $ml('.neighborhood_by_suffix').hide();
        $ml('.city_by_suffix').hide();
		if (meeting_sort_val === 'day' || meeting_sort_val === 'weekday_area' || meeting_sort_val === 'weekday_city' ||  meeting_sort_val === 'weekday_county') {
            $ml('.weekday_start_div').show();
		} else {
            $ml('.weekday_start_div').hide();
		}
		if (meeting_sort_val === 'borough_county') {
			$ml('.borough_by_suffix').show();
			$ml('.county_by_suffix').show();
		} else if (meeting_sort_val === 'borough') {
			$ml('.borough_by_suffix').show();
		} else if (meeting_sort_val === 'county') {
			$ml('.county_by_suffix').show();
		}
		if (meeting_sort_val === 'neighborhood_city') {
			$ml('.neighborhood_by_suffix').show();
			$ml('.city_by_suffix').show();
		}
	});
	var time_clock_val = $ml('input[name=time_clock]:checked').val();
	if (time_clock_val == '24') {
		$ml('#option3').hide();
		$ml('label[for=option3]').hide();
	} else if (time_clock_val == '24fr') {
		$ml('#option3').hide();
		$ml('label[for=option3]').hide();
	} else {
		$ml('#option3').show();
		$ml('label[for=option3]').show();
	}
	$ml("#two").click(function() {
		var time_clock_val = $ml('input[name=time_clock]:checked').val();
		if (time_clock_val == '24') {
			$ml('label[for=option1]').html('20:00');
			$ml('label[for=option2]').html('20:00 - 21:00');
			$ml('#option3').hide();
			$ml('label[for=option3]').html('');
		} else if (time_clock_val == '24fr') {
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
	});
	$ml("#four").click(function() {
		var time_clock_val = $ml('input[name=time_clock]:checked').val();
		if (time_clock_val == '24') {
			$ml('label[for=option1]').html('20:00');
			$ml('label[for=option2]').html('20:00-21:00');
			$ml('#option3').hide();
			$ml('label[for=option3]').html('');
		} else if (time_clock_val == '24fr') {
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
	});
	$ml("#time_clock12").click(function() {
		var remove_space_val = $ml('input[name=remove_space]:checked').val();
		$ml('#option3').show();
		$ml('label[for=option3]').show();
		if (remove_space_val == '1') {
			$ml('label[for=option1]').html('8:00PM');
			$ml('label[for=option2]').html('8:00PM-9:00PM');
			$ml('label[for=option3]').html('8-9PM');
		} else {
			$ml('label[for=option1]').html('8:00 PM');
			$ml('label[for=option2]').html('8:00 PM - 9:00 PM');
			$ml('label[for=option3]').html('8 - 9 PM');
		}
	});
	$ml("#time_clock24").click(function() {
		var remove_space_val = $ml('input[name=remove_space]:checked').val();
		$ml('#option3').hide();
		$ml('label[for=option3]').html('');
		if (remove_space_val == '1') {
			$ml('label[for=option1]').html('20:00');
			$ml('label[for=option2]').html('20:00-21:00');
		} else {
			$ml('label[for=option1]').html('20:00');
			$ml('label[for=option2]').html('20:00 - 21:00');
		}
	});
	$ml("#time_clock24fr").click(function() {
		var remove_space_val = $ml('input[name=remove_space]:checked').val();
		$ml('#option3').hide();
		$ml('label[for=option3]').html('');
		if (remove_space_val == '1') {
			$ml('label[for=option1]').html('20h00');
			$ml('label[for=option2]').html('20h00-21h00');
		} else {
			$ml('label[for=option1]').html('20h00');
			$ml('label[for=option2]').html('20h00 - 21h00');
		}
	});
	var page_fold_val = $ml('input[name=page_fold]:checked').val();
	console.log("got a page_fold_val="+page_fold_val);
	function bookletControlsShowHide() {
		console.log("in halfpage2");
		$ml('#pagenodiv').show();
		$ml('#columngapdiv').hide();
		$ml('#columnseparatordiv').hide();
		$ml("#portrait, label[for=portrait]").hide();
		$ml("#letter, label[for=letter]").show();
		$ml("#legal, label[for=legal]").show();
		$ml("#ledger, label[for=ledger]").show();
		$ml("#booklet_pages, label[for=booklet_pages]").hide();
		$ml("#A4, label[for=A4]").show();
		$ml("#A5, label[for=A5]").show();
		$ml("#A6, label[for=A6]").hide();
		$ml("#portrait, label[for=portrait]").hide();
		$ml("#meeting-list-tabs ul li:eq(5)").hide();
		$ml("#meeting-list-tabs ul li:eq(6)").show();
		$ml('#meeting-list-tabs').tabs('disable', 5);
		$ml('#meeting-list-tabs').tabs('enable', 6);
		$ml("#half-fold").css({
			"display": "inline-block"
		});
		$ml("#tri-fold").css({
			"display": "none"
		});
	}
	function fullPageControlsShowHide() {
		$ml('#pagenodiv').hide();
		$ml("#A4, label[for=A4]").show();
		$ml("#A5, label[for=A5]").show();
		$ml("#A6, label[for=A6]").show();
		$ml("#portrait, label[for=portrait]").show();
		$ml("#booklet_pages, label[for=booklet_pages]").show();
		$ml("#meeting-list-tabs ul li:eq(5)").hide();
		$ml("#meeting-list-tabs ul li:eq(6)").show();
		$ml('#meeting-list-tabs').tabs('disable', 5);
		$ml('#meeting-list-tabs').tabs('enable', 6);
		$ml('input[name=page_height]').val(['']);
		$ml('#pageheightdiv').hide();
		$ml('#columngapdiv').hide();
		$ml('#columnseparatordiv').hide();
		$ml("#half-fold").css({
			"display": "inline-block"
		});
		$ml("#tri-fold").css({
			"display": "none"
		});
	}
	function foldControlsShowHide(fold) {
		$ml("#letter, label[for=letter]").show();
		$ml("#legal, label[for=legal]").show();
		$ml("#ledger, label[for=ledger]").show();
		$ml("#booklet_pages, label[for=booklet_pages]").hide();
		$ml("#A4, label[for=A4]").show();
		$ml("#A5, label[for=A5]").hide();
		$ml("#A6, label[for=A6]").hide();
		if (fold=='quad')
			$ml("#portrait, label[for=portrait]").show();
		else
			$ml("#portrait, label[for=portrait]").hide();
		$ml("#meeting-list-tabs ul li:eq(5)").hide();
		$ml("#booklet_pages, label[for=booklet_pages]").hide();
		$ml('#meeting-list-tabs').tabs('disable', 6);
		$ml("#meeting-list-tabs ul li:eq(6)").hide();
	}
	if (page_fold_val == 'half') {
		bookletControlsShowHide();
	} else if (page_fold_val == 'full') {
		fullPageControlsShowHide();
	} else {
		foldControlsShowHide(page_fold_val);
	}
	$ml('input[name=page_fold]:radio').click(function() {
		console.log('clicked');
		var page_fold_val = $ml('input[name=page_fold]:checked').val();
		var page_orientation_val = $ml('input[name=page_orientation]:checked').val();
		var page_size_val = $ml('input[name=page_size]:checked').val();
		console.log("page_fold="+page_fold_val);
		if (page_fold_val == 'half') {
			bookletControlsShowHide();
		}
		else if (page_fold_val == 'full') {
			fullPageControlsShowHide();
		}
		else {
			foldControlsShowHide(page_fold_val);
		};
		if (page_fold_val === 'tri') {
			$ml('input[name=page_size]').val(['letter']);
		};
	});
	$ml(".service_body_select").chosen({
		inherit_select_classes: true,
		width: "62%"
	});
	$ml("#extra_meetings").chosen({
		no_results_text: "Oops, nothing found!",
		width: "100%",
		placeholder_text_multiple: "Select Extra Meetings",
		search_contains: true
	});
	$ml('#extra_meetings').on('chosen:showing_dropdown', function(evt, params) {
		$ml(".ctrl_key").show();
	});	
	$ml('#extra_meetings').on('chosen:hiding_dropdown', function(evt, params) {
		$ml(".ctrl_key").hide();
	});	
	$ml("#author_chosen").chosen({
		no_results_text: "Oops, nothing found!",
		width: "100%",
		placeholder_text_multiple: "Select authors",
		search_contains: true
	});
	$ml('#author_chosen').on('chosen:showing_dropdown', function(evt, params) {
		$ml(".ctrl_key").show();
	});	
	$ml('#author_chosen').on('chosen:hiding_dropdown', function(evt, params) {
		$ml(".ctrl_key").hide();
	});	
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
	/* 	minimumResultsForSearch: 1, */
		/* closeOnSelect: false, */
/* 		escapeMarkup: function (markup) { return markup; }
	}).maximizeSelect2Height({cushion: 100});
	$ml('.select2-choices').css('background-image','none').css('background-color','#111111 !important');
 */
	$ml("#meeting-list-tabs").tabs({
		active: 0
	});
	$ml("#meeting-list-tabs-wrapper").removeClass('hide');
	//  Define friendly index name
	var index = 'key';
	//  Define friendly data store name
	var dataStore = window.sessionStorage;
	//  Start magic!
	try {
		// getter: Fetch previous value
		var oldIndex = dataStore.getItem(index);
	} catch (e) {
		// getter: Always default to first tab in error state
		var oldIndex = 0;
	}
	$ml('#meeting-list-tabs').tabs({
		// The zero-based index of the panel that is active (open)
		active: oldIndex,
		// Triggered after a tab has been activated
		activate: function(event, ui) {
			//  Get future value
			var newIndex = ui.newTab.parent().children().index(ui.newTab);
			//  Set future value
			dataStore.setItem(index, newIndex)
		}
	});
});