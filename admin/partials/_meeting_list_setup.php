<?php
if (basename($_SERVER['PHP_SELF']) == basename(__FILE__)) {
    die('Sorry, but you cannot access this page directly.');
}
$plugin_data = get_plugin_data(WP_PLUGIN_DIR . '/bread/bread.php');
$plugin_version = "could not access version";
if ($plugin_data) {
    $plugin_version = $plugin_data['Version'];
}
global $wp_version;
?>
<div id="poststuff">
    <div id="postbox-container" class="postbox-container">
        <div id="accordion" class="bmlt-accrodian">
            <h3 class="help-accordian"><strong>Read This Section First</strong></h3>
            <div>
                <h2>Getting Started</h2>
                <p>bread is first activated using a "Tri Fold - Landscape - Letter Size" layout. This is a "starter" meeting list that uses an Area with about 100 meetings. The starter meeting list will contain standard content for a basic meeting list that can be printed on a home computer. A basic NA logo will be added to your media libray. The starter meeting list uses a logo being hosted on <a target="_blank" href="https://nameetinglist.org">https://nameetinglist.org</a>.</p>
                <h2>Step 1.</h2>
                <p>Click on the BMLT Server tab to the left. Change the BMLT Server and click the Save Changes button.</p>
                <p><em>To find your BMLT Server click on the red question (?) mark.</em></p>
                <h2>Step 2.</h2>
                <p>From the Service Body 1 dropdown select your Area or Region. Then click Save Changes.</p>
                <h2>Step 3.</h2>
                <p>Click Generate Meeting List. Your meeting list will open in a new tab or window.</p>
                <h2>Step 4.</h2>
                <p>See the "Meeting List Setup" section below for additional defaults.</p>
                <p><em>Repeat steps 1, 2 and 3 after changing to new Default Settings.</em></p>
                <h2>What Now?</h2>
                <p>From here you will move forward with setting up your meeting list by exploring the Page Layout, Front Page, Custom Section, Meetings, etc tabs. There are countless ways to setup a meeting list.</p>
                <p>Please allow yourself to experiment with mixing and matching different settings and content. There is a good chance you can find a way to match or at least come very close to your current meeting list.</p>
                <p>When setting up the meeting list it is helpful to have some knowledge of HTML when using the editors. Very little or no knowledge of HTML is required to maintain the meeting list after the setup. If you get stuck or would like some help with the setup, read the Support section below.</p>
            </div>
            <h3 class="help-accordian">Meeting List Setup Wizard</h3>
            <?php include '_bread_wizard.php'; ?>
            <h3 class="help-accordian">Multiple Meeting Lists</h3>
            <div>
                <p>This tool supports multiple meeting lists per site.</p>
                <p>This feature is configured from the Configuration Tab. There, each concurrent meeting list can be given a
                    name, and the system gives the meeting list a numberic identifier. The meeting list can then be generated using </p>
                a link of the form http://[host]?current-meeting-list=[id]</p>
                <p>After switching to another concurrent meeting list, any changes in the admin UI impact the currently selected meeting list</p>
                <p>If you want to give another user access to bread you can give that use the "manage_bread" capability using a custom role editor.</p>
            </div>
            <h3 class="help-accordian">Reusable Templating</h3>
            <div>
                <p>You can dynamically set some of the options to create a reusable template.</p>
                <p>In order to change the meeting information you can pass a dynamic custom query using &custom_query=, ensure you are using URL encoding.</p>
                <p>You can also use any combinations of [querystring_custom_*], where * is any digit. You can then override that specific value using it in querystring as &querystring_custom_1= (for instance).</p>
                <p>You can use any HTML characters, including line breaks.</p>
                <p>Here is a video of it in action: <a target="_blank">https://bmlt.app/reusable-templates-with-bread-1-6-x/</a></p>
            </div>
            <h3 class="help-accordian">Support and Help</h3>
            <div>
                <p>File an issue <a href="https://github.com/radius314/bread/issues">https://github.com/bmlt-enabled/bread/issues</a></p>
                <u>Debug Information</u>
                <ul>
                    <li><b>Bread Version:</b> <?php echo $plugin_version; ?></li>
                    <li><b>Wordpress Version:</b> <?php echo $wp_version; ?></li>
                    <li><b>Protocol:</b> <?php echo $this->bread->getProtocol(); ?></li>
                    <li><b>PHP Version:</b> <?php echo phpversion(); ?></li>
                    <li><b>Server Version:</b> <?php echo $_SERVER["SERVER_SOFTWARE"]; ?></li>
                    <li><b>Temporary Directory:</b> <?php echo get_temp_dir(); ?></li>
                </ul>
            </div>
        </div>
    </div>
    <br class="clear">
</div>