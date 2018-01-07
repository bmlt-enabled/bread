<?php
if (basename($_SERVER['PHP_SELF']) == basename(__FILE__)) {
    die('Sorry, but you cannot access this page directly.');
} ?>
<div id="poststuff">
    <div id="postbox-container" class="postbox-container">
        <div id="accordion">
            <h3 class="help-accordian"><strong>Read This Section First</strong></h3>
            <div>
                <h2>Getting Started</h2>
                <p>bread is first activated using a "Tri Fold - Landscape - Letter Size" layout. This is a "starter" meeting list that uses an Area with about 100 meetings.  The starter meeting list will contain standard content for a basic meeting list that can be printed on a home computer.  A basic NA logo will be added to your media libray.  The starter meeting list uses a logo being hosted on <a target="_blank" href="http://nameetinglist.org">http://nameetinglist.org</a>.</p>
                <h2>Step 1.</h2>
                <p>Click on the BMLT Server tab to the left.  Change the BMLT Server and click the Save Changes button.</p>
                <p><em>To find your BMLT Server click on the red question (?) mark.</em></p>
                <h2>Step 2.</h2>
                <p>From the Service Body 1 dropdown select your Area or Region.  Then click Save Changes.</p>
                <h2>Step 3.</h2>
                <p>Click Generate Meeting List.  Your meeting list will open in a new tab or window.</p>
                <h2>Step 4.</h2>
                <p>See the "Meeting List Setup" section below for additional defaults.</p>
                <p><em>Repeat steps 1, 2 and 3 after changing to new Default Settings.</em></p>
                <h2>What Now?</h2>
                <p>From here you will move forward with setting up your meeting list by exploring the Page Layout, Front Page, Custom Section, Meetings, etc tabs.  There are countless ways to setup a meeting list.</p>
                <p>Please allow yourself to experiment with mixing and matching different settings and content.  There is a good chance you can find a way to match or at least come very close to your current meeting list.</p>
                <p>When setting up the meeting list it is helpful to have some knowledge of HTML when using the editors.  Very little or no knowledge of HTML is required to maintain the meeting list after the setup.  If you get stuck or would like some help with the setup, read the Support section below.</p>
            </div>
            <h3 class="help-accordian">Meeting List Setup</h3>
            <div>
                <h2>Default Settings and Content</h2>
                <p>Changing the Default Settings and Content should only be considered when first using the Meeting List Generator or when you decide to completely start over with setting up your meeting list.</p>
                <p><i>The buttons below will completely reset your meeting list settings (and content) to whichever layout you choose. There is no Undo.</i></p>
                <p style="color: #f00; margin-bottom: 15px;">Consider backing up settings by using the Backup/Restore Tab before changing your Meeting List Settings.</p>
                <input type="submit" value="Tri Fold - Letter Size" id="submit_three_column" class="button-primary" />
                <input type="submit" value="Quad Fold - Legal Size" id="submit_four_column" class="button-primary" />
                <input type="submit" value="Half Fold - Booklet" id="submit_booklet" class="button-primary" />
                <h2>Small or Medium Size Areas</h2>
                <p>Areas with up to about 100 meetings would benefit from using the tri-fold layout on letter sized paper.  Areas larger than 100 meetings would typically use a quad fold meeting list on legal sized paper.  These are just basic guidelines and are by no means set in stone.  For example, an Area with over 100 meetings could use the tri-fold on letter sized paper using smaller fonts to allow the content to fit.  The meeting list configuration is extremely flexible.</p>
                <p></i>The Custom Content section is used to add information like helplines, service meetings, meeting format legend, etc.</i></p>
                <h2>Large Areas, Metro Areas or Regions</h2>
                <p>Larger service bodies would benefit from using a booklet meeting list.</p>
                <p></i>The booklet uses the Front and Last pages for custom content.  There is no Custom Content section on a booklet meeting list.</i></p>
                <h2>Support</h2>
                <p>Assistance is available with setting up a meeting list.  For support file an issue at https://github.com/radius314/bread/issues</p>
            </div>
            <h3 class="help-accordian">Multiple Meeting Lists</h3>
            <div>
                <p>Currently, this tool supports one meeting list per site.</p>
                <p>The following methods could be used to get additional meeting lists.</p>
                <p>Method 1. Install additional Wordpress installations on your server.  For example:</p>
                <ol>
                <li>Add a sub-domain for each meeting list. For example:</li>
                <ul>
                <li>area1.region.org</li>
                <li>area2.region.org</li>
                <li>area3.region.org</li>
                </ul>
                <li>Install Wordpress on each sub-domain.</li>
                <li>Install the bread plugin on each sub-domain.</li>
                <li>Provide the login to each Wordpress installation to each local web-servant.</li>
                </ol>
                <p>Method 2. Create a Wordpress multi-site installation.</p>
                <p>Method 3. Contribute to this project so that multiple meeting lists could be configured in this plugin.</p>
            </div>
            <h3 class="help-accordian">Support and Help</h3>
            <div>
                <p>File an issue <a href="https://github.com/radius314/bread/issues">https://github.com/radius314/bread/issues</a></p>
            </div>
        </div>
    </div>
    <br class="clear">
</div>
