jQuery(document).ready(function($) {
    $('.bread_button_form').submit(async function(event) {
        event.preventDefault(); // Block default submission
        const doFetchP = async (url) => {
            try {
                const x = await fetchJsonp(url);
                return x.json();
            } catch (e) {console.log(e)}
        }
        const doFetch = async (url) => {
            try {
                const x = await fetch(url);
                const json = await x.json();
                console.log(json);
                return json;
            } catch (e) {console.log(e)}
        }
        const currentMeetingList = $(event.target).children('input[name="current-meeting-list"]').val();
        let config = await doFetch(ajax_object.ajax_url + '?action=bread_generate_queries_action&current-meeting-list=' + currentMeetingList);
        const preload = {};
        preload.mainResults = await doFetchP(config.root_server + '/' + config.main_query);
        if (config.extra_meetings_query) preload.extraMeetings = await doFetchP(config.root_server + '/' + config.extra_meetings_query);
        if (config.additional_list_query) preload.additionalListMeetings = await doFetchP(config.root_server + '/' + config.additional_list_query);
        preload.serviceBodies = await doFetchP(config.root_server + '/client_interface/jsonp/?switcher=GetServiceBodies');
        preload.allFormats = {};
        const langs = config.weekday_language.split('_');
        for (let i = 0; i < langs.length; i++) {
            preload.allFormats[langs[i]] = await doFetchP(config.root_server + '/client_interface/jsonp/?switcher=GetFormats&lang_enum=' + langs[i]);
        }
        if (config.additional_list_language && config.additional_list_language != '')
            preload.allFormats[config.additional_list_language] = await doFetchP(config.root_server + '/client_interface/jsonp/?switcher=GetFormats&lang_enum=' + config.additional_list_language);
        $(event.target).children('input[name="preload"]').val(JSON.stringify(preload));
        $(event.target).unbind('submit');
        $(event.target).submit();
        return false;
    }
    )})
