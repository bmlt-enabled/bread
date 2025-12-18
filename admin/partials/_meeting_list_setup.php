<?php
if (basename($_SERVER['PHP_SELF']) == basename(__FILE__)) {
    die('Sorry, but you cannot access this page directly.');
}
function Bread_meeting_list_setup_page_render(Bread_AdminDisplay $breadAdmin)
{
    $bread = $breadAdmin->getBreadInstance();
    $plugin_data = get_plugin_data(WP_PLUGIN_DIR . '/bread/bmlt-meeting-list.php');
    $plugin_version = "could not access version";
    if ($plugin_data) {
        $plugin_version = $plugin_data['Version'];
    }
    global $wp_version;
    ?>
<div id="poststuff">
    <div id="postbox-container" class="postbox-container">
        <div id="accordion" class="bmlt-accordion">
            <h3 class="help-accordion"><strong>Start Here: Meeting List Setup Wizard</strong></h3>
            <?php include '_bread_wizard.php'; ?>
            <h3 class="help-accordion">Multiple Meeting Lists</h3>
            <div>
                <p>This tool supports multiple meeting lists per site.</p>
                <p>This feature is configured from the <pre>Backup/Restore</pre> Tab. There, each concurrent meeting list can be given a
                    name, and the system gives the meeting list a numberic identifier. The meeting list can then be generated using </p>
                a link of the form http://[host]?current-meeting-list=[id]</p>
                <p>After switching to another meeting list, any changes in the admin UI impact the currently selected meeting list</p>
                <p>If you want to give another user access to bread you can give that use the "manage_bread" capability using a custom role editor.</p>
            </div>
            <h3 class="help-accordion">Reusable Templating</h3>
            <div>
                <p>You can dynamically set some of the options to create a reusable template.</p>
                <p>In order to change the meeting information you can pass a dynamic custom query using &custom_query=, ensure you are using URL encoding.</p>
                <p>You can also use any combinations of [querystring_custom_*], where * is any digit. You can then override that specific value using it in querystring as &querystring_custom_1= (for instance).</p>
                <p>You can use any HTML characters, including line breaks.</p>
                <p>Here is a video of it in action: <a target="_blank">https://bmlt.app/reusable-templates-with-bread-1-6-x/</a></p>
            </div>
            <h3 class="help-accordion">Extending Bread</h3>
            <div>
                <p>Advanced users can extend the functionality of Bread using the WordPress <a href="https://developer.wordpress.org/plugins/hooks/filters/">filter</a> mechanism.</p>
                <p>The <code>Bread_Enrich_Meeting_Data</code> filter allows the user to expose calculated fields in their the templates to their meetings.
            An example for the use of this is to make meetings in other languages easier to find by modifying the name of such a meeting to include a text in the other language. (Normally, other languages are
            just noted in the formats, which make the meetings quite hard to find.)</p>
                <p>This extension has the form:</p>
                <pre>
                add_filter('Bread_Enrich_Meeting_Data', 'enrichMeetingData', 10, 2);
                function enrichMeetingData($value, $formatsByKey) {...}
                </pre>
                where <code>$value</code> is an array containing the properties of the meeting, and <code>formatsByKey</code> is a list of the available formats and their properties.
                <p>Similarly, the <code>Bread_Section_Shortcodes</code> filter allows the user to make additional commands available in the Front-Page and Custom-Section parts of their meeting lists.
                <p>This can be used to exposed additional mPdf commands.
                <p>This extension has the form:</p>
                <pre>
                add_filter('Bread_Section_Shortcodes', 'sectionShortcodes', 10, 3);
                function sectionShortcodes($section_shortcodes, $areas, $formats_used) {...}
                </pre>
                where <code>$section_shortcodes</code> is an array containing the default shortcodes, <code>areas</code> is a list of the service bodies and their metadata and <code>formats_used</code> is an array of format metadata.

            </div>
            <h3 class="help-accordion">Support and Help</h3>
            <div>
                <p>File an issue <a href="https://github.com/radius314/bread/issues">https://github.com/bmlt-enabled/bread/issues</a></p>
                <u>Debug Information</u>
                <ul>
                    <li><b>Bread Version:</b> <?php echo esc_html($plugin_version); ?></li>
                    <li><b>Wordpress Version:</b> <?php echo esc_html($wp_version); ?></li>
                    <li><b>Protocol:</b> <?php echo esc_html($bread->getProtocol()); ?></li>
                    <li><b>PHP Version:</b> <?php echo esc_html(phpversion()); ?></li>
                    <li><b>Server Version:</b> <?php echo esc_html($_SERVER["SERVER_SOFTWARE"]); ?></li>
                    <li><b>Temporary Directory:</b> <?php echo esc_html(get_temp_dir()); ?></li>
                </ul>
            </div>
        </div>
    </div>
    <br class="clear">
</div>
    <?php
}