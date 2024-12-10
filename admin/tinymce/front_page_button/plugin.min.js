(function () {
    tinymce.PluginManager.add(
        'front_page_button', function ( editor, url ) {
            editor.addButton(
                'front_page_button', {
                    text: 'Meeting List Shortcodes',
                    icon: false,
                    type: 'listbox',
                    menu: [
                    {
                        text: 'INSTRUCTIONS',
                        menu: [
                           {
                                text: 'Video Instructions',
                                onclick: function () {
                                        editor.windowManager.open(
                                            {
                                                title: 'Shortcode Instructions',
                                                url: 'https://nameetinglist.org/videos/nameetinglist.mp4',
                                                width: 800,
                                                height: 600,
                                                buttons: [{
                                                    text: 'Close',
                                                    onclick: 'close'
                                                }]
                                            }
                                        );
                                }
                        },
                          ]
                    },
                    {
                        text: 'Format Code Legend',
                        menu: [
                        {
                            text: 'Used Format Codes - Abbreviated - Same language as weekdays text',
                            onclick: function () {
                                editor.insertContent('<table style="width: 100%;"><tbody><tr><td style="padding: 2pt; text-align: center; background-color: #000000;"><strong><span style="color: #fff;">Meeting Format Legend</span></strong></td></tr></tbody></table>[format_codes_used_basic]</p>');
                            }
                        },
                        {
                            text: 'Used Format Codes - Detailed - Same language as weekdays text',
                            onclick: function () {
                                editor.insertContent('<table style="width: 100%;"><tbody><tr><td style="padding: 2pt; text-align: center; background-color: #000000;"><strong><span style="color: #fff;">Meeting Format Legend</span></strong></td></tr></tbody></table><p>[format_codes_used_detailed]</p>');
                            }
                        },
                        {
                            text: 'All Format Codes - Abbreviated - Same language as weekdays text',
                            onclick: function () {
                                editor.insertContent('<table style="width: 100%;"><tbody><tr><td style="padding: 2pt; text-align: center; background-color: #000000;"><strong><span style="color: #fff;">Meeting Format Legend</span></strong></td></tr></tbody></table><p>[format_codes_all_basic]</p>');
                            }
                        },
                       {
                            text: 'All Format Codes - Detailed - Same language as weekdays text',
                            onclick: function () {
                                editor.insertContent('<table style="width: 100%;"><tbody><tr><td style="padding: 2pt; text-align: center; background-color: #000000;"><strong><span style="color: #fff;">Meeting Format Legend</span></strong></td></tr></tbody></table><p>[format_codes_all_detailed]</p>');
                            }
                        },
                        {
                            text: 'Format Table in other languages',
                            onclick: function() {alert("To insert a format table in another language, simply add _{code} to the shortcode, where code is the standard 2-letter abbreviation for the language.  Eg, use '_es' to get spanish")}
                        }
                    ]
                    },
                    {
                        text: 'Helpline Template',
                        onclick: function () {
                            editor.insertContent('<table style="width: 100%;"><tbody><tr><td style="padding: 2pt; text-align: center; background-color: #000000;"><strong><span style="color: #fff;">Helplines</span></strong></td></tr></tbody></table><table style="width: 100%;"><tbody><tr><td style="border-bottom-style: solid; border-bottom-width: 1px; border-bottom-color: #555555; vertical-align: top;"><p>Big Bend Area - Tallahassee</p></td><td style="border-bottom-style: solid; border-bottom-width: 1px; border-bottom-color: #555555; text-align: right; vertical-align: top;"><p>877-340-5096</p><p>850-224-2321</p></td></tr><tr><td style="border-bottom-style: solid; border-bottom-width: 1px; border-bottom-color: #555555; vertical-align: top;"><p>Daytona Beach Area - Volusia County</p></td><td style="border-bottom-style: solid; border-bottom-width: 1px; border-bottom-color: #555555; text-align: right; vertical-align: top;"><p>800-206-0731</p><p>386-628-0318</p></td></tr><tr><td style="border-bottom-style: solid; border-bottom-width: 1px; border-bottom-color: #555555; vertical-align: top;"><p>First Coast Area - Duval County</p></td><td style="border-bottom-style: solid; border-bottom-width: 1px; border-bottom-color: #555555; text-align: right; vertical-align: top;"><p>904-723-5683</p></td></tr><tr><td style="border-bottom-style: solid; border-bottom-width: 1px; border-bottom-color: #555555; vertical-align: top;"></td><td style="border-bottom-style: solid; border-bottom-width: 1px; border-bottom-color: #555555; text-align: right; vertical-align: top;"></td></tr><tr><td style="border-bottom-style: solid; border-bottom-width: 1px; border-bottom-color: #555555; vertical-align: top;"></td><td style="border-bottom-style: solid; border-bottom-width: 1px; border-bottom-color: #555555; text-align: right; vertical-align: top;"></td></tr></tbody></table>');
                        }
                    },
                    {
                        text: 'Phone List Template',
                        onclick: function () {
                            editor.insertContent('<table style="width: 100%;"><tbody><tr><td style="padding: 2pt; text-align: center; background-color: #000000;"><strong><span style="color: #fff;">PHONE NUMBERS</span></strong></td></tr></tbody></table><table style="width: 100%;" cellspacing="10" cellpadding="10"><tbody><tr><td style="border-bottom-width: 1px; border-bottom-style: solid; border-bottom-color: #111111;"> </td></tr><tr><td style="border-bottom-width: 1px; border-bottom-style: solid; border-bottom-color: #111111;"> </td></tr><tr><td style="border-bottom-width: 1px; border-bottom-style: solid; border-bottom-color: #111111;"> </td></tr><tr><td style="border-bottom-width: 1px; border-bottom-style: solid; border-bottom-color: #111111;"> </td></tr><tr><td style="border-bottom-width: 1px; border-bottom-style: solid; border-bottom-color: #111111;"> </td></tr><tr><td style="border-bottom-width: 1px; border-bottom-style: solid; border-bottom-color: #111111;"> </td></tr><tr><td style="border-bottom-width: 1px; border-bottom-style: solid; border-bottom-color: #111111;"> </td></tr><tr><td style="border-bottom-width: 1px; border-bottom-style: solid; border-bottom-color: #111111;"> </td></tr><tr><td style="border-bottom-width: 1px; border-bottom-style: solid; border-bottom-color: #111111;"> </td></tr><tr><td style="border-bottom-width: 1px; border-bottom-style: solid; border-bottom-color: #111111;"> </td></tr><tr><td style="border-bottom-width: 1px; border-bottom-style: solid; border-bottom-color: #111111;"> </td></tr></tbody></table>');
                        }
                    },
                    {
                        text: 'Header Template',
                        onclick: function () {
                            editor.insertContent('<table style="width: 100%;"><tbody><tr><td style="padding: 2pt; text-align: center; background-color: #000000;"><strong><span style="color: #fff;">HEADER TEXT</span></strong></td></tr></tbody></table>');
                        }
                    },
                    {
                        text: 'Additional Meetinglist Template',
                        onclick: function () {
                            editor.insertContent('<table style="width: 100%;"><tbody><tr><td style="padding: 2pt; text-align: center; background-color: #000000;"><strong><span style="color: #fff;">Additional Meetings</span></strong></td></tr></tbody></table><p>[additional_meetinglist]</p>');
                        }
                    },
                    {
                        text: 'Service Body',
                        menu: [
                           {
                                text: 'Service Body 1',
                                onclick: function () {
                                    editor.insertContent('[service_body_1]');
                                }
                        },
                           {
                                text: 'Service Body 2',
                                onclick: function () {
                                    editor.insertContent('[service_body_2]');
                                }
                        },
                           {
                                text: 'Service Body 3',
                                onclick: function () {
                                    editor.insertContent('[service_body_3]');
                                }
                        },
                           {
                                text: 'Service Body 4',
                                onclick: function () {
                                    editor.insertContent('[service_body_4]');
                                }
                        },
                           {
                                text: 'Service Body 5',
                                onclick: function () {
                                    editor.insertContent('[service_body_5]');
                                }
                        }
                          ]
                    },
                    {
                        text: 'Date',
                        menu: [
                           {    text: 'Month (UPPER CASE)',
                                onclick: function () {
                                editor.insertContent('[month_upper]');
                                        }                        },                        {                            text: 'Month (Lower Case)',                            onclick: function () {
                                editor.insertContent('[month_lower]');                            }                        },                        {                            text: 'Month French (UPPER CASE)',                            onclick: function () {
                                editor.insertContent('[month_upper_fr]');                            }                        },                        {                            text: 'Month French (Lower Case)',                            onclick: function () {
                                editor.insertContent('[month_lower_fr]');                            }                        },                        {                            text: 'Month Spanish (UPPER CASE)',                            onclick: function () {
                                editor.insertContent('[month_upper_es]');                            }                        },                        {                            text: 'Month Spanish (Lower Case)',                            onclick: function () {
                                editor.insertContent('[month_lower_es]');                            }                        },                        {
                                                        text: 'Day',
                                                        onclick: function () {
                                                            editor.insertContent('[day]');
                                                        }
                        },
                           {
                                text: 'Year',
                                onclick: function () {
                                    editor.insertContent('[year]');
                                }
                        }
                          ]
                    },
                    {
                        text: 'Querystring Overrides',
                        menu: [
                           {
                                text: 'QueryString Custom Text',
                                onclick: function () {
                                    editor.insertContent('[querystring_custom_*]');
                                }
                        }
                          ]
                    },
                    {
                        text: 'Meeting Count',
                        onclick: function () {
                            editor.insertContent('[meeting_count]');
                        }
                    },
                    {
                        text: 'New Page (Booklet Only)',
                        onclick: function () {
                            editor.insertContent('<p>[page_break]</p>');
                        }
                    },
                    {
                        text: 'Start Page Numbers (Booklet Only)',
                        onclick: function () {
                            editor.insertContent('<p>[start_page_numbers]</p>');
                        }
                    },
                    {
                        text: 'New Column (Tri & Quad Fold Only)',
                        onclick: function () {
                            editor.insertContent('<p>[new_column]</p>');
                        }
                    }
                    ]
                }
            );
            editor.addButton(
                'custom_template_button_1', {
                    text: 'Meeting Templates',
                    icon: false,
                    type: 'listbox',
                    menu: Object.keys(meetingDataTemplates).reduce((carry,item) => {
                        carry.push({
                            text: item,
                            onclick: () => editor.setContent(
                                Array.isArray(meetingDataTemplates[item]) ?
                                    meetingDataTemplates[item].join('') :meetingDataTemplates[item])
                         })
                        return carry;
                    },[])
                }
            );
            editor.addButton(
                'custom_template_button_2', {
                    text: 'Meeting Template Fields',
                    icon: false,
                    type: 'listbox',
                    menu: [
                    {
                        text: 'Area',
                        menu: [
                           {
                                text: 'Area Name (area)',
                                onclick: function () {
                                    editor.insertContent('area');
                                }
                        },
                           {
                                text: 'Area Initial (area_i)',
                                onclick: function () {
                                    editor.insertContent('area_i');
                                }
                        }
                          ]
                    },
                    {
                        text: 'Meeting Location',
                        menu: [
                           {
                                text: 'Address (street)',
                                onclick: function () {
                                    editor.insertContent('street');
                                }
                        },
                           {
                                text: 'Borough (borough)',
                                onclick: function () {
                                    editor.insertContent('borough');
                                }
                        },
                           {
                                text: 'Bus Lines (bus_lines)',
                                onclick: function () {
                                    editor.insertContent('bus_lines');
                                }
                        },
                           {
                                text: 'City (city)',
                                onclick: function () {
                                    editor.insertContent('city');
                                }
                        },
                           {
                                text: 'County (county)',
                                onclick: function () {
                                    editor.insertContent('county');
                                }
                        },
                           {
                                text: 'Location Name (location)',
                                onclick: function () {
                                    editor.insertContent('location');
                                }
                        },
                           {
                                text: 'Location Extra Info (info)',
                                onclick: function () {
                                    editor.insertContent('info');
                                }
                        },
                           {                            text: 'Neighborhood (neighborhood)',                            onclick: function () {
                                editor.insertContent('neighborhood');                            }                        },                        {                            text: 'State (state)',                            onclick: function () {
                                    editor.insertContent('state');                            }                        },                        {
                                        text: 'Zip Code (zip)',
                                        onclick: function () {
                                                            editor.insertContent('zip');
                                        }
                        }
                          ]
                    },
                    {
                        text: 'Meeting Information',
                        menu: [
                           {
                                text: 'Comments (comments)',
                                onclick: function () {
                                    editor.insertContent('comments');
                                }
                        },
                           {
                                text: 'Duration Hours (hrs)',
                                onclick: function () {
                                    editor.insertContent('hrs');
                                }
                        },
                           {
                                text: 'Duration Minutes (mins)',
                                onclick: function () {
                                    editor.insertContent('mins');
                                }
                        },
                           {
                                text: 'Email Contact (email)',
                                onclick: function () {
                                    editor.insertContent('email');
                                }
                        },
                           {
                                text: 'Format Codes (formats)',
                                onclick: function () {
                                    editor.insertContent('formats');
                                }
                        },
                           {
                                text: 'Group Name (group)',
                                onclick: function () {
                                    editor.insertContent('group');
                                }
                        },
                           {
                                text: 'Start Time (time)',
                                onclick: function () {
                                    editor.insertContent('time');
                                }
                        },
                           {
                                text: 'Weekday (day)',
                                onclick: function () {
                                    editor.insertContent('day');
                                }
                        },
                           {
                                text: 'Weekday Abbreviated (day_abbr)',
                                onclick: function () {
                                    editor.insertContent('day_abbr');
                                }
                        },
                           {
                                text: 'Virtual Meeting Link (virtual_meeting_link)',
                                onclick: function () {
                                    editor.insertContent('virtual_meeting_link');
                                }
                        },
                           {
                                text: 'Virtual Meeting Info (virtual_meeting_additional_info)',
                                onclick: function () {
                                    editor.insertContent('virtual_meeting_additional_info');
                                }
                        },
                           {
                                text: 'Phone Meeting Number (phone_meeting_number)',
                                onclick: function () {
                                    editor.insertContent('phone_meeting_number');
                                }
                        },
                           {
                                text: 'QRCode (virtual_meeting_link)',
                                onclick: function () {
                                    editor.insertContent('[QRCode code="virtual_meeting_link" size="0.8"]');
                                }
                        },
                          ]
                    }
                    ]
                }
            );
        }
    );
})();