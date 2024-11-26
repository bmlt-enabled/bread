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
        BreadWizard.prototype.test_root_server = function() {
            ask_bmlt("switcher=GetServerInfo",
            (info) => {
                $('#wizard_root_server_result').removeClass('invalid-feedback')
                    .addClass('valid-feedback').html('BMLT Server version '+info[0]['version']);
            },
            (error) => {
                $('#wizard_root_server_result').removeClass('valid-feedback')
                    .addClass('invalid-feedback').html('Could not connect: Check spelling and internet connection.');
            });
        }
        BreadWizard.prototype.root_server_keypress = function(event) {
            if (event.code == 'Enter') this.test_root_server();
        }
        BreadWizard.prototype.root_server_changed = function() {
            $('#wizard_root_server_result').removeClass('invalid-feedback').removeClass('valid-feedback')
                .html('Verify that this is valid root server URL before continuing');
        }
        write_service_body_with_childern = function(options, sb, parents, level) {
            let prefix = '';
            for (i=0; i<level; i++) prefix += '-';
            options.push('<option value="'+sb.id+'">'+prefix+sb.name+'</option>');
            found = parents.find((p) => p.id == sb.id);
            if (typeof found !== 'undefined')
                found.children.forEach((child) =>
                    options = write_service_body_with_childern(options, child, parents, level+1));
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
                options = write_service_body_with_childern(options, sb, parents, 0);
            });
            $('#wizard_service_bodies').html(options.join(''));
            $('#wizard_service_bodies').trigger("chosen:updated");
        }
        service_body_error = function(error) {
            console.log(error);
        }
        BreadWizard.prototype.getContent = function(idx, stepDirection, stepPosition, selStep, callback) {
            switch(idx) {
                case 1:
                    ask_bmlt('switcher=GetServiceBodies', fill_service_bodies, service_body_error);
                    break;
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

            default:
                break;
        }
        return true;
    });
    $("#wizard_service_bodies").chosen({
        inherit_select_classes: true,
        max_selected_options:5,
        width: "62%"
    });
});