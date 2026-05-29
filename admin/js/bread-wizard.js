jQuery(document).ready(function($){
    var BreadWizard = function() {
        BreadWizard.prototype.ajax_submit = function() {
            $(".saving").show();
            var myform = document.getElementById("wizard_form");
            var formData = new FormData(myform);
            fetch(window.location.href, {
              method: "POST",
              body: formData,
            })
              .then((response) => {
                if (!response.ok) {
                  throw new Error("network returns error");
                }
                return response.json();
              })
              .then((resp) => {
                finalInstructions(resp);
              })
              .catch((error) => {
                // Handle error
                console.log("error ", error);
              });
          }
        var href;
        var setting;
        finalInstructions = function(response) {
            $(".saving").hide();
            $('#wizard-before-create').hide();
            $('#wizard-after-create').show();
            href = window.location.href.substring(0, window.location.href.indexOf('/wp-admin'));
            setting = response.result.setting;
            href = href+"?current-meeting-list="+setting;
            const tag = '<div class="step-description"><pre>'+href+'</pre>';
            $('#wizard-show-link').html(tag);
        }
        BreadWizard.prototype.generate_meeting_list = function() {
            window.open(href, '_blank').focus();
        }
        BreadWizard.prototype.redo_layout = function() {
            $('#wizard_setting_id').val(setting);
            $('#bread-wizard').smartWizard("goToStep", 2);
        }
        BreadWizard.prototype.test_root_server = function() {
            const context = {
                root_server: 'wizard_root_server',
                service_bodies: 'wizard_service_bodies',
                service_bodies_selected: []
            }
            ask_bmlt(context, "switcher=GetServerInfo",
            (context, info) => {
                $('#wizard_root_server_result').removeClass('invalid-feedback dashicons-dismiss')
                    .addClass('valid-feedback dashicons-before dashicons-yes-alt').html($('#wizard_connected_message').html()+info[0].version);
            },
            (context, error) => {
                $('#wizard_root_server_result').removeClass('valid-feedback dashicons-yes-alt')
                    .addClass('invalid-feedback dashicons-before dashicons-dismiss').html($('#wizard_disconnected_message').html());
            });
        }
        BreadWizard.prototype.root_server_keypress = function(event) {
            if (event.code == 'Enter') this.test_root_server();
        }
        BreadWizard.prototype.root_server_changed = function() {
            $('#wizard_root_server_result').removeClass('valid-feedback').addClass('invalid-feedback dashicons-before dashicons-dismiss')
                .html($('#wizard_testnow_message').html());
        }
        BreadWizard.prototype.finish = function() {
            $('#bread-wizard').smartWizard("reset");
            var form = document.createElement("form");
            form.method = "POST";
            form.action = window.location.href;
            var input = document.createElement("input");
            input.type = "hidden";
            input.name = "current-meeting-list";
            input.value = setting;
            form.appendChild(input);
            document.body.appendChild(form);
            form.submit();
        }
        var hasVirtualMeetings = false;
        layout_options = function(meetings) {
            const meeting_count = meetings.length;
            hasVirtualMeetings = meetings.some((m) => m.formats.split(',').some(format => ['VM','HY'].includes(format)));
            $('#wizard_meeting_count').html(meeting_count);
            const layouts = breadLayouts.find((layouts) => meeting_count <= Number(layouts.maxSize));
            const options = breadLayouts.reduce((carry,group) => {
                const name = (group.maxSize == '99999') ? 'Very Large Fellowships' : 'Approx. '+group.maxSize+' meetings';
                carry.push('<optgroup label="'+name+'">');
                group.configurations.reduce((carryGroup, item, idx) => {
                    carryGroup.push(...getOptionsFromFilename(group.maxSize, item, idx, layouts));
                    return carryGroup;
                }, carry);
                carry.push('</optgroup>');
                return carry;
            }, []);
            $('#wizard_layout').html(options.join(''));
        }
        getOptionsFromFilename = function(size, filename, idx, layouts) {
            const type = filename.split('-');
            const fold = type[0];
            const orientation = type[1];
            const selected = (idx==0 && layouts.maxSize == size) ? ' selected' : '';
            const font = type[2];
            const papersize = (fold=='booklet') ? ['5inch','A5'] : ['letter','A4'];
            return papersize.reduce((carry,item) => {
                carry.push('<option value="'+size+'/'+filename+','+item+'"'+selected+'>'+fold+' - '+item+' paper/ '+orientation+' orientation</option>');
                return carry;
            },[]);
        }
        lang_options = function() {
            const options = breadTranslations.reduce((carry,item)=>{
                const selected = (item.key==='en') ? ' selected' : '';
                carry.push('<option value="'+item.key+'"'+selected+'>'+item.name+'</option>')
                return carry;
            }, []);
            $('#wizard_language').html(options.join(''));
            if (!hasVirtualMeetings) {
                $('#wizard_no_virtual_meetings').prop("checked", true);
                $('#wizard-virtual-meeting-section').hide();
            }
        }
        wizard_handle_error = function(error) {
            console.log(error);
            $('#bread-wizard').smartWizard("goToStep", 0);
        }
        BreadWizard.prototype.getContent = function(idx, stepDirection, stepPosition, selStep, callback) {
            const context = {
                root_server: 'wizard_root_server',
                service_bodies: 'wizard_service_bodies',
                service_bodies_selected: []
            }
            switch(idx) {
                case 1:
                    ask_bmlt(context, 'switcher=GetServiceBodies', fill_service_bodies, wizard_handle_error);
                    ask_bmlt(context, 'switcher=GetFormats', fill_formats, wizard_handle_error);
                    break;
                case 2:
                    const services = $('#wizard_service_bodies').val().reduce((carry,item) => {
                        carry += '&services[]='+item.split(',')[1];
                        return carry;
                    }, '&recursive=1');
                    const formats = (Number($('#wizard_format_filter').val()) > 0) ? '&formats='+$('#wizard_format_filter').val() : '';

                    ask_bmlt(context, 'switcher=GetSearchResults'+services+formats, layout_options, wizard_handle_error);
                    break;
                case 3:
                    lang_options();
                case 4:
                    if ($("wizard_layout").val()=='') wizard_handle_error('Layout not defined');
                    $('#wizard-before-create').show();
                    $('#wizard-after-create').hide();
                default:
                    break;
            }
            callback();
        }
    };
    window.breadWizard = new BreadWizard();

    // SmartWizard initialize
    $('#bread-wizard').smartWizard(
        {
            theme: 'dots',
            autoAdjustHeight: false,
            enableUrlHash: false,
            keyboard: {
                keyNavigation: false,
            },
            anchor: {
                enableNavigation: false,
            },
            getContent: breadWizard.getContent
        }
    );
    // Initialize the leaveStep event
    $("#bread-wizard").on("leaveStep", function(e, anchorObject, currentStepIndex, nextStepIndex, stepDirection) {
        switch(currentStepIndex) {
            case 0:
                const ret = $('#wizard_root_server_result').hasClass('valid-feedback');
                if (!ret) $('#wizard_root_server_result').html('Verify that this is valid root server URL before continuing');
                return ret;
            case 1:
                if ($('#wizard_service_bodies').val().length == 0 && stepDirection=='forward') {
                    $('#wizard_service_body_result').html('You must select a service body before continuing');
                    return false;
                }
            default:
                break;
        }
        return true;
    });
    $("#wizard_service_bodies").select2({
        inherit_select_classes: true,
        max_selected_options:5,
        width: "62%"
    });
});