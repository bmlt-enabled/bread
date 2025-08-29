jQuery(document).ready(function($){
    var BreadWizard = function() {
        ask_bmlt = function(query, success, fail) {
            const url = $("#wizard_root_server").val()+"/client_interface/jsonp/?"+query;
            fetchJsonp(url)
              .then((response) => {
                if (response.ok) {
                    return response.json();
                }
                return Promise.reject(response); // 2. reject instead of throw
            })
            .then((json) => {
                success(json);
                return json;
            })
            .catch((response) => {
                fail(response)
                return false;
            })
        }
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
            ask_bmlt("switcher=GetServerInfo",
            (info) => {
                $('#wizard_root_server_result').removeClass('invalid-feedback dashicons-dismiss')
                    .addClass('valid-feedback dashicons-before dashicons-yes-alt').html($('#wizard_connected_message').html()+info[0].version);
            },
            (error) => {
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
        write_service_body_with_childern = function(options, sb, parents, my_parent, level) {
            let prefix = '';
            for (i=0; i<level; i++) prefix += '-';
            const sbVal = [sb.name,sb.id,sb.parent_id, my_parent].join(',');
            options.push('<option value="'+sbVal+'">'+prefix+sb.name+'</option>');
            found = parents.find((p) => p.id == sb.id);
            if (typeof found !== 'undefined')
                found.children.forEach((child) =>
                    options = write_service_body_with_childern(options, child, parents, sb.name, level+1));
            return options;
        }
        fill_service_bodies = function(service_bodies) {
            service_bodies = service_bodies.sort((a,b) => a.name.localeCompare(b.name));
            const roots = service_bodies.filter((sb) => sb.parent_id=='0');
            const parents = service_bodies.reduce((carry,item) => {
                const found = carry.find((p) => p.id == item.parent_id);
                if (found) {
                    found.children.push(item);
                } else {
                    carry.push({id: item.parent_id, children:[item]})
                }
                return carry;
            }, []);
            let options = [];
            roots.forEach((sb) => {
                options = write_service_body_with_childern(options, sb, parents, 'ROOT', 0);
            });
            $('#wizard_service_bodies').html(options.join(''));
            $('#wizard_service_bodies').trigger("chosen:updated");
        }
        fill_formats = function(formats) {
            const options = formats.reduce((carry,item) => {
                carry.push('<option value="'+item.id+'">Only '+item.name_string+'</option>');
                return carry;
            }, ['<option value="" selected>All Meetings</option>']);
            $('#wizard_format_filter').html(options.join(''));
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
        handle_error = function(error) {
            console.log(error);
            $('#bread-wizard').smartWizard("goToStep", 0);
        }
        BreadWizard.prototype.getContent = function(idx, stepDirection, stepPosition, selStep, callback) {
            switch(idx) {
                case 1:
                    ask_bmlt('switcher=GetServiceBodies', fill_service_bodies, handle_error);
                    ask_bmlt('switcher=GetFormats', fill_formats, handle_error);
                    break;
                case 2:
                    const services = $('#wizard_service_bodies').val().reduce((carry,item) => {
                        carry += '&services[]='+item.split(',')[1];
                        return carry;
                    }, '&recursive=1');
                    const formats = (Number($('#wizard_format_filter').val()) > 0) ? '&formats='+$('#wizard_format_filter').val() : '';

                    ask_bmlt('switcher=GetSearchResults'+services+formats, layout_options, handle_error);
                    break;
                case 3:
                    lang_options();
                case 4:
                    if ($("wizard_layout").val()=='') handle_error('Layout not defined');
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