jQuery(document).ready(function ($) {
	$(".connecting").hide();
	$(".saving").hide();
	$(".bmlt-accordion").accordion({
		heightStyle: "content",
		active: false,
		collapsible: true,
	});
	$(".bmlt_color").spectrum({
		preferredFormat: "hex",
		showInput: true,
		showPalette: false,
	});
	$("#col_color").on("click", () =>
		$("#triggerSet").spectrum("set", this.val()),
	);
	$("#bmlt_meeting_list_options").on("keypress", function (event) {
		if (event.which == 13 && !event.shiftKey) {
			event.preventDefault();
			return false;
		}
	});
	$(".gears-working").click(function (e) {
		$(".saving").show();
	});
	$('input[name="submit_import_file"]').on("click", function (e) {
		e.preventDefault();
		var import_file_val = $("input[name=import_file]").val();
		if (import_file_val == false) {
			$("#nofileModal").dialog("open");
		} else {
			$("#basicModal").dialog("open");
		}
	});
	$("#nofileModal").dialog({
		autoOpen: false,
		modal: true,
		buttons: {
			Ok: function () {
				$(this).dialog("close");
			},
		},
	});
	$("#basicModal").dialog({
		autoOpen: false,
		width: "auto",
		title: "Are you sure?",
		modal: true,
		buttons: {
			Confirm: function (e) {
				$(this).dialog("close");
				$(".saving").show();
				$("#form_import_file").submit();
			},
			Cancel: function () {
				$(this).dialog("close");
			},
		},
	});
	$(".my-tooltip").each(function (i, e) {
		$(e).tooltipster({
			contentAsHTML: true,
			theme: "tooltipster-noir",
			trigger: "click",
		});
	});
	$("#meeting-list-tabs").tabs({
		active: 0,
	});
	$("#meeting-list-tabs")
		.tabs()
		.addClass("ui-tabs-vertical ui-helper-clearfix");
	$("#container").removeClass("hide");
	var meeting_sort_val = $("#meeting_sort").val();
	$(".borough_by_suffix").hide();
	$(".county_by_suffix").hide();
	if (meeting_sort_val === "borough_county") {
		$(".borough_by_suffix").show();
		$(".county_by_suffix").show();
	} else if (meeting_sort_val === "borough") {
		$(".borough_by_suffix").show();
	} else if (meeting_sort_val === "county") {
		$(".county_by_suffix").show();
	}
	$(".neighborhood_by_suffix").hide();
	$(".city_by_suffix").hide();
	if (meeting_sort_val === "neighborhood_city") {
		$(".neighborhood_by_suffix").show();
		$(".city_by_suffix").show();
	}
	var user_defined_sub = false;
	$(".user_defined_headings").hide();
	if (meeting_sort_val === "user_defined") {
		$(".user_defined_headings").show();
		if ($("#subgrouping").val() != "") {
			user_defined_sub = true;
		}
	}
	if (
		meeting_sort_val == "weekday_area" ||
		meeting_sort_val == "weekday_city" ||
		meeting_sort_val == "weekday_county" ||
		meeting_sort_val == "state" ||
		user_defined_sub
	) {
		$(".show_subheader").show();
	} else {
		$(".show_subheader").hide();
	}
	$("#suppress_heading").click(function () {
		var val = $("#suppress_heading:checked").val();
		if (val == 1) {
			$("#header_options_div").hide();
		} else {
			$("#header_options_div").show();
		}
	});
	$("#meeting_sort").change(function () {
		var meeting_sort_val = $("#meeting_sort").val();
		$(".borough_by_suffix").hide();
		$(".county_by_suffix").hide();
		$(".neighborhood_by_suffix").hide();
		$(".city_by_suffix").hide();
		if (meeting_sort_val === "borough_county") {
			$(".borough_by_suffix").show();
			$(".county_by_suffix").show();
		} else if (meeting_sort_val === "borough") {
			$(".borough_by_suffix").show();
		} else if (meeting_sort_val === "county") {
			$(".county_by_suffix").show();
		}
		if (meeting_sort_val === "neighborhood_city") {
			$(".neighborhood_by_suffix").show();
			$(".city_by_suffix").show();
		}
		var user_defined_sub = false;
		$(".user_defined_headings").hide();
		if (meeting_sort_val === "user_defined") {
			$(".user_defined_headings").show();
			if ($("#subgrouping").val() != "") {
				user_defined_sub = true;
			}
		}
		if (
			meeting_sort_val == "weekday_area" ||
			meeting_sort_val == "weekday_city" ||
			meeting_sort_val == "weekday_county" ||
			meeting_sort_val == "state" ||
			user_defined_sub
		) {
			$(".show_subheader").show();
		} else {
			$(".show_subheader").hide();
		}
	});
	$("#subgrouping").click(function () {
		$(".user_defined_headings").hide();
		if ($("#meeting_sort").val() === "user_defined") {
			$(".user_defined_headings").show();
			if ($("#subgrouping").val() != "") {
				$(".show_subheader").show();
			} else {
				$(".show_subheader").hide();
			}
		}
	});
	function calcTimeDisplay(hasEndTime) {
		let clock = $("input[name=time_clock]:checked").val();
		let removeSpaces = $("input[name=remove_space]:checked").val();
		let startTime = "8:00 PM";
		let endTime = "9:00 PM";
		if (clock == "24") {
			startTime = "20:00";
			endTime = "21:00";
		} else if (clock == "24fr") {
			startTime = "20h00";
			endTime = "21h00";
		}
		if (hasEndTime == 2) {
			startTime += " - " + endTime;
		}
		if (hasEndTime == 3) {
			startTime = clock == "12" ? "8 - 9" : "";
		}
		if (removeSpaces != "0") {
			startTime = startTime.replaceAll(" ", "");
		}
		return startTime;
	}
	function setTimeOptionText() {
		$("label[for=option1]").html(calcTimeDisplay(1));
		$("label[for=option2]").html(calcTimeDisplay(2));
		$("label[for=option3]").html(calcTimeDisplay(3));
		if ($("input[name=time_clock]:checked").val() != "12") {
			if ($("input[name=time_clock]:checked").val() == 3)
				$("#option2").prop("checked", true);
			$("#option3").hide();
		} else $("#option3").show();
	}
	setTimeOptionText();
	$(".recalcTimeLabel").on("click", setTimeOptionText);
	function bookletControlsShowHide() {
		$("#half").prop("checked") && $("#landscape").prop("checked", true);
		$("#full").prop("checked") && $("#portrait").prop("checked", true);
		$(".booklet").show();
		$(".single-page").hide();
		$("#half").prop("checked") && $("#A6").hide();
		$("#half").prop("checked") && $("#A6").hide();
		$("#half").prop("checked") && $("label[for=A6]").hide();
	}
	function singlePageControlsShowHide() {
		$(".booklet").hide();
		$(".single-page").show();
	}
	$(".single-page-check").on("click", singlePageControlsShowHide);
	$(".booklet-check").on("click", bookletControlsShowHide);
	$("input[name=page_fold]:checked").hasClass("booklet-check") &&
		bookletControlsShowHide();
	$("input[name=page_fold]:checked").hasClass("single-page-check") &&
		singlePageControlsShowHide();
	$("#service_bodies").select2();
	$("#extra_meetings").select2({
		placeholder: "Select extra meetings",
	});
	$("#bread_author_select").select2({
		placeholder: "Select authors",
	});
	$(".bread-select").on("change", function (e) {
		const self = $(this);
		const field = self.parent().find(".select2-search--inline");
		field.css("display", self.val().length == 0 ? "contents" : "block");
		field
			.find("input")
			.css("width", self.val().length == 0 ? "100%" : "auto");
	});
	$("#meeting-list-tabs").tabs({
		active: 0,
	});
	$("#meeting-list-tabs-wrapper").removeClass("hide");
	//  Define friendly index name
	var index = "key";
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
	$("#meeting-list-tabs").tabs({
		// The zero-based index of the panel that is active (open)
		active: oldIndex,
		// Triggered after a tab has been activated
		activate: function (event, ui) {
			//  Get future value
			var newIndex = ui.newTab.parent().children().index(ui.newTab);
			//  Set future value
			dataStore.setItem(index, newIndex);
		},
	});
	var aggregator = "https://aggregator.bmltenabled.org/main_server";
	$(window).on("load", function () {
		if ($("#use_aggregator").is(":checked")) {
			$("#root_server").prop("readonly", true);
		}
	});
	$("#use_aggregator").click(function () {
		if ($(this).is(":checked")) {
			$("#root_server").val(aggregator);
			$("#root_server").prop("readonly", true);
		} else {
			$("#root_server").val("");
			$("#root_server").prop("readonly", false);
		}
	});
	var rootServerValue = $("#root_server").val();
	if (~rootServerValue.indexOf(aggregator)) {
		$("#use_aggregator").prop("checked", true);
	}
	$(".service_body_select").select2({
		width: "40%",
	});
	$(".theme_select").select2({
		width: "20%",
	});
	$("#select_filters").select2({
		width: "60%",
	});
	$("#extra_meetings").select2({
		width: "60%",
		placeholder: "Select Extra Meetings",
	});
	handle_error = function (context, error) {
		console.log(error);
	};
	ask_bmlt = function (context, query, success, fail) {
		const url =
			$("#" + context.root_server).val() +
			"/client_interface/jsonp/?" +
			query;
		fetchJsonp(url)
			.then((response) => {
				if (response.ok) {
					return response.json();
				}
				return Promise.reject(response); // 2. reject instead of throw
			})
			.then((json) => {
				success(context, json);
				return json;
			})
			.catch((response) => {
				fail(context, response);
				return false;
			});
	};
	const admin_context = {
		root_server: "root_server",
		service_bodies: "service_bodies",
		service_bodies_selected: bread_admin.service_bodies_selected,
	};
	test_root_server = function () {
		if (!$("#root_server").val()) {
			$("#connected_message").hide();
			$("#disconnected_message").hide();
			fill_service_bodies(admin_context, []);
			fill_extra_meetings(admin_context, []);
			return;
		}
		ask_bmlt(
			admin_context,
			"switcher=GetServerInfo",
			(context, info) => {
				$("#server_version").html(info[0].version);
				$("#connected_message").show();
				$("#disconnected_message").hide();
				ask_bmlt(
					context,
					"switcher=GetServiceBodies",
					fill_service_bodies,
					handle_error,
				);
				query_extra_meetings(context);
			},
			(context, error) => {
				console.log(error);
				$("#connected_message").hide();
				$("#disconnected_message").show();
				fill_service_bodies(context, []);
				fill_extra_meetings(context, []);
			},
		);
	};
	query_extra_meetings = function (context) {
		if ($("#extra_meetings_enabled").is(":checked")) {
			$("#extra_meetings_select").hide();
			$("#fetching_meetings").show();
			ask_bmlt(
				context,
				"switcher=GetSearchResults",
				fill_extra_meetings,
				handle_error,
			);
		} else {
			fill_extra_meetings(context, []);
		}
	};
	test_root_server();
	$("#extra_meetings_enabled").on("change", function () {
		query_extra_meetings(admin_context);
	});
	write_service_body_with_childern = function (
		context,
		options,
		sb,
		parents,
		my_parent,
		level,
	) {
		let prefix = "";
		for (i = 0; i < level; i++) prefix += "-";
		const sbVal = [sb.name, sb.id, sb.parent_id, my_parent].join(",");
		const option =
			'<option value="' +
			sbVal +
			'" ' +
			(context.service_bodies_selected.includes(sb.id)
				? "selected"
				: "") +
			">" +
			prefix +
			sb.name +
			"(" +
			sb.id +
			")</option>";
		options.push(option);
		found = parents.find((p) => p.id == sb.id);
		if (typeof found !== "undefined")
			found.children.forEach(
				(child) =>
					(options = write_service_body_with_childern(
						context,
						options,
						child,
						parents,
						sb.name,
						level + 1,
					)),
			);
		return options;
	};
	fill_service_bodies = function (context, service_bodies) {
		service_bodies = service_bodies.sort((a, b) =>
			a.name.localeCompare(b.name),
		);
		const roots = service_bodies.filter((sb) => sb.parent_id == "0");
		const parents = service_bodies.reduce((carry, item) => {
			const found = carry.find((p) => p.id == item.parent_id);
			if (found) {
				found.children.push(item);
			} else {
				carry.push({ id: item.parent_id, children: [item] });
			}
			return carry;
		}, []);
		let options = [];
		roots.forEach((sb) => {
			options = write_service_body_with_childern(
				context,
				options,
				sb,
				parents,
				"ROOT",
				0,
			);
		});
		$("#" + context.service_bodies).html(options.join(""));

		query_used_formats(context);
	};
	root_server_keypress = function (event) {
		if (event.code == "Enter") {
			this.test_root_server();
			event.preventDefault();
		}
		return true;
	};
	fill_extra_meetings = function (context, extra_meetings_array) {
		$("#fetching_meetings").hide();
		if ($("#extra_meetings_enabled").is(":checked")) {
			$("#extra_meetings").next(".select2-container").show();
			$("#extra_meetings_hint").show();
		} else {
			$("#extra_meetings").next(".select2-container").hide();
			$("#extra_meetings_hint").hide();
		}
		const options = extra_meetings_array.map(
			(extra_meeting) =>
				'<option value="' +
				extra_meeting.id_bigint +
				'" ' +
				(bread_admin.extra_meetings.includes(extra_meeting.id_bigint)
					? "selected"
					: "") +
				">" +
				extra_meeting.meeting_name +
				" [" +
				extra_meeting.weekday_tinyint +
				"][" +
				extra_meeting.start_time +
				"][" +
				extra_meeting.location_municipality +
				"][" +
				extra_meeting.service_body_bigint +
				"]</option>",
		);
		$("#extra_meetings").html(options.join(""));
	};
	fill_formats = function (context, formats) {
		const select = (key) =>
			bread_admin.used_format == key ? "selected" : "";
		const options = formats.formats.reduce(
			(carry, item) => {
				carry.push(
					'<option value="' +
						item.id +
						'" ' +
						select(item.id) +
						">" +
						item.name_string +
						"</option>",
				);
				return carry;
			},
			['<option value="" ' + select("") + '">All Meetings</option>'],
		);
		$("#used_format_1").html(options.join(""));
		fill_additional_list_formats(context, formats);
	};
	fill_additional_list_formats = function (context, formats) {
		const select = (key) =>
			bread_admin.additional_list_format_key == key ? "selected" : "";
		const options = formats.formats.reduce(
			(carry, item) => {
				carry.push(
					'<option value="' +
						item.key_string +
						'" ' +
						select(item.key_string) +
						">" +
						item.name_string +
						"</option>",
				);
				return carry;
			},
			[
				'<option value="" ' + select("") + '">Not Used</option>',
				'<option value="@Virtual@" ' +
					select("@Virtual@") +
					'">Virtual Meetings</option>',
				'<option value="@F2F@" ' +
					select("@F2F@") +
					'">In-Person Meetings</option>',
			],
		);
		$("#additional_list_format_key").html(options.join(""));
	};
	query_used_formats = function (context) {
		const serviceBodies = $("#service_bodies")
			.val()
			.map((s) => s.split(",")[1]);
		if (serviceBodies.length == 0) {
			fill_formats(context, []);
			return;
		}
		const query = serviceBodies.reduce(
			(carry, item) => {
				return carry + "&services[]=" + item;
			},
			$("#recurse_service_bodies").is(":checked") ? "&recursive=1" : "",
		);
		ask_bmlt(
			context,
			"switcher=GetSearchResults&get_formats_only" + query,
			fill_formats,
			handle_error,
		);
	};
	/**
	 * Get Tab Key
	 */
	function getTabKey(href) {
		return href.replace("#", "");
	}
	/**
	 * Hide all tabs
	 */
	function hideAllTabs() {
		tabs.each(function () {
			var href = getTabKey(jQuery(this).attr("href"));
			jQuery("#" + href).hide();
		});
	}
	/**
	 * Activate Tab
	 */
	function activateTab(tab) {
		var href = getTabKey(tab.attr("href"));
		tabs.removeClass("nav-tab-active");
		tab.addClass("nav-tab-active");
		jQuery("#" + href).show();
	}
	var activeTab, firstTab;
	// First load, activate first tab or tab with nav-tab-active class
	firstTab = false;
	activeTab = false;
	tabs = $("a.nav-tab");
	hideAllTabs();
	tabs.each(function () {
		var href = $(this).attr("href").replace("#", "");
		if (!firstTab) {
			firstTab = $(this);
		}
		if ($(this).hasClass("nav-tab-active")) {
			activeTab = $(this);
		}
	});
	if (!activeTab) {
		activeTab = firstTab;
	}
	activateTab(activeTab);
	//Click tab
	tabs.click(function (e) {
		e.preventDefault();
		hideAllTabs();
		activateTab($(this));
	});
});
