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
                return json;
            } catch (e) {console.log(e)}
        }
        const $working = $("<div>", {"class": "working"});
        $("body").append($working);
        const form = $(event.target);
        const currentMeetingList = form.children('input[name="current-meeting-list"]').val();
        let config = await doFetch(ajax_object.ajax_url + '?action=bread_generate_queries_action&current-meeting-list=' + currentMeetingList + '&nonce=' + ajax_object.nonce);
        const preload = {};
        preload.mainResults = await doFetchP(config.root_server + '/' + config.main_query);
        if (config.extra_meetings_query) preload.extraMeetings = await doFetchP(config.root_server + '/' + config.extra_meetings_query);
        if (config.additional_list_query) preload.additionalListMeetings = await doFetchP(config.root_server + '/' + config.additional_list_query);
        preload.serviceBodies = await doFetchP(config.root_server + '/client_interface/jsonp/?switcher=GetServiceBodies');
        preload.allFormats = {};
        const lang1 = config.weekday_language.substring(0, 2)
        preload.allFormats[lang1] = await doFetchP(config.root_server + '/client_interface/jsonp/?switcher=GetFormats&lang_enum=' + lang1);
        if (config.additional_list_language && config.additional_list_language != '')
            preload.allFormats[config.additional_list_language] = await doFetchP(config.root_server + '/client_interface/jsonp/?switcher=GetFormats&lang_enum=' + config.additional_list_language);
        form.children('input[name="preload"]').val(JSON.stringify(preload));
        const url = $(location).attr('href') ;
        const formBody = $(event.target).serialize();
        const resp = await fetch(url, {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'},
            body: formBody
        });
        const blob = await resp.blob();

        // Ensure the blob is served as a PDF
        const respContentType = (resp.headers.get('Content-Type') || '').toLowerCase();
        let pdfBlob = blob;
        if (!respContentType.includes('pdf')) {
            // Recreate blob with application/pdf type
            pdfBlob = new Blob([await blob.arrayBuffer()], {type: 'application/pdf'});
        }
        const blobUrl = URL.createObjectURL(pdfBlob);

        // Open a new blank window and embed the PDF for preview.
        // If popup is blocked, fall back to triggering a download.
        const w = window.open('', '_blank');
        if (w) {
            w.document.title = 'PDF Preview';
            w.document.body.style.margin = '0';
            w.document.body.innerHTML = '<embed src="' + blobUrl + '" type="application/pdf" width="100%" height="100%""></embed>';
            w.document.close();
        } else {
            // Popup blocked — trigger download as fallback
            const a = document.createElement('a');
            a.href = blobUrl;
            a.download = 'meeting-list.pdf';
            document.body.appendChild(a);
            a.click();
            a.remove();
        }
        $working.remove();
        return false;
    }
    )})
