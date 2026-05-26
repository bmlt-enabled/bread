jQuery(document).ready(function($) {
    $('#bread_button').click(async function() {
        const doFetch = async (url) => {
            try {
                x = await fetchJsonp(url);
                return x.json();
            } catch (e) {console.log(e)}
        }
        let config = bread_ajax_obj.config;
        const preload = {};
        preload.mainResults = await doFetch(config.root_server + '/' + config.main_query);
        if (config.extra_meetings_query) preload.extraMeetings = await doFetch(config.root_server + '/' + config.extra_meetings_query);
        if (config.additional_list_query) preload.additionalListMeetings = await doFetch(config.root_server + '/' + config.additional_list_query);
        preload.serviceBodies = await doFetch(config.root_server + '/client_interface/jsonp/?switcher=GetServiceBodies');
        preload.allFormats = [];
        const langs = config.weekday_language.split('_');
        for (let i = 0; i < langs.length; i++) {
            preload.allFormats[langs[i]] = await doFetch(config.root_server + '/client_interface/jsonp/?switcher=GetFormats&lang_enum=' + langs[i]);
        }
        if (config.additional_list_language && config.additional_list_language != '')
            preload.allFormats[config.additional_list_language] = await doFetch(config.root_server + '/client_interface/jsonp/?switcher=GetFormats&lang_enum=' + config.additional_list_language);
        console.log(bread_ajax_obj.ajaxurl)
$.ajax({
    type: 'POST',
    url: bread_ajax_obj.ajaxurl,
    data: {
        action: 'bread_preload_action',
        nonce: bread_ajax_obj.nonce,
        preload: JSON.stringify(preload),
    },
    success: function(response) {
        console.log(response);
    }
})
})})