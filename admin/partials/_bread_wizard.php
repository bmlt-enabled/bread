<?php
if (! defined('ABSPATH')) {
    exit;
}?>
<div id="bread-wizard">
    <ul class="nav">
        <li class="nav-item">
          <a class="nav-link" href="#step-1">
            <div class="num">1</div>
            <?php esc_html_e('BMLT Server', 'bread') ?>
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="#step-2">
            <span class="num">2</span>
            <?php esc_html_e('Select Service Bodies', 'bread') ?>
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="#step-3">
            <span class="num">3</span>
            <?php esc_html_e('Choose Layout', 'bread') ?>
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link " href="#step-4">
            <span class="num">4</span>
            <?php esc_html_e('Meeting List Options', 'bread') ?>
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link " href="#step-4">
            <span class="num">5</span>
            <?php esc_html_e('Create Meeting List', 'bread') ?>
          </a>
        </li>
    </ul>
    <form method="post" id="wizard_form">
        <?php wp_nonce_field('pwsix_wizard_nonce', 'pwsix_wizard_nonce'); ?>
        <input type="hidden" name="pwsix_action" value="wizard" />
        <input type="hidden" id="wizard_setting_id" name="wizard_setting_id" value="" />
    <div class="tab-content">
        <div id="step-1" class="tab-pane" role="tabpanel" aria-labelledby="step-1">
        <h3><?php esc_html_e('Step 1: Enter your BMLT root server', 'bread') ?></h3>
        <div class="step-description">
        <p><?php esc_html_e("This wizard guides you through the initial configuation of a meeting list.  If you have already created one or more meeting lists,
          don't worry, this process alway creates a new configuration, and never overwrites old ones.", 'bread') ?></p>
        <p><?php esc_html_e("Bread is highly customizable, as areas often try to maintain the appearance of their old meeting lists.  This wizard only attempts to give you a reasonable
          starting point, not an end product.  At the end of the wizard we make a few suggestions for possible next steps.", 'bread') ?></p>
        <p><?php esc_html_e("The first thing we need is the location of your BMLT root server.  Once you have entered it, you need check that the URL is correct
          by clicking the 'Test Connection' button.", 'bread') ?></p>
        </div>
        <p><?php echo wp_kses_post(__('Visit <a target="_blank" href="https://doihavethebmlt.org/">Do I have the BMLT?</a> to find your BMLT server', 'bread')) ?><br/>
            <label for="wizard_root_server"><?php esc_html_e('BMLT Server URL: ', 'bread') ?></label>
            <input class="bmlt-input" id="wizard_root_server" type="text" name="wizard_root_server"
                            onKeypress="breadWizard.root_server_keypress(event)" onChange="breadWizard.root_server_changed()" />
            <button type="button" onClick="breadWizard.test_root_server()"><?php esc_html_e('Test Server Connection', 'bread') ?></button>
            <span id="wizard_testnow_message" style="display:none;">
              <?php esc_html_e('Test that this is valid root server URL before continuing', 'bread') ?>
            </span>
            <span id="wizard_connected_message" style="display:none;">
              <?php esc_html_e('Connected! - BMLT Server version ', 'bread') ?>
            </span>
            <span id="wizard_disconnected_message" style="display:none;">
              <?php esc_html_e('Could not connect: Check spelling and internet connection.', 'bread') ?>
            </span>

            <div id="wizard_root_server_result">
                <?php esc_html_e('Verify that this is valid root server URL before continuing', 'bread') ?>
            </div></p>
        </div>
        <div id="step-2" class="tab-pane" role="tabpanel" aria-labelledby="step-2">
            <div style="min-height: 300px;">
            <h3><?php esc_html_e('Step 2: Select a service body', 'bread') ?></h3>
            <div class="step-description">
            <p><?php esc_html_e('Next, we need to know which meetings are going to be on the meeting list.  This will help the wizard when it
              tries to choose an a appropriate layout for you.', 'bread') ?></p>
            </div>
            <select class="bread-select" style="width: 400px;" data-placeholder="<?php esc_html_e('Choose up to 5 service bodies', 'bread') ?>" id="wizard_service_bodies" name="wizard_service_bodies[]" multiple="multiple">
                <option value=1>The is placeholder 1 Hello</option>
                <option value=2>The is placeholder 1 Hello</option>
                <option value=3>The is placeholder 1 Hello</option>
                <option value=4>The is placeholder 1 Hello</option>
                <option value=5>The is placeholder 1 Hello</option>
                <option value=6>The is placeholder 1 Hello</option>
                <option value=7>The is placeholder 1 Hello</option>
                <option value=8>The is placeholder 1 Hello</option>
                <option value=9>The is placeholder 1 Hello</option>
            </select>
            <div id="wizard_service_body_result">
            </div>
            <p><?php esc_html_e('If you want to limit the meeting list to a particular format, for instance, to create a language-specific meeting list, you can enter it here.', 'bread') ?>
            <br/><select style="width: 400px;" id="wizard_format_filter" name="wizard_format_filter">
            </select></p>
            </div>
        </div>
        <div id="step-3" class="tab-pane" role="tabpanel" aria-labelledby="step-3">
          <h3><?php esc_html_e('Step 3: Page Layout', 'bread') ?></h3>
          <div class="step-description">
          <p><?php esc_html_e("Most areas try to fit their meetings onto a single sheet of paper.  And it is particularly adventageous to
            use a tri-fold format, so that the list can be printed at home and placed along side the pamphlets. The priority should always be that the
            meeting list is readable.  For many areas, a booklet format is better, even if the reqire more effort or expense to print.", 'bread') ?></p>
          <p><?php esc_html_e("In this step we suggest suitible formats based on your meeting count.  Having the meetings fit on a single side has the advantage
            that the list can be posted on a bulleitin board, and this will be used when suggesting a page layout.  However, you may
            also wish to have the meetings 'overflow' on to the back side of the paper.  If so, select a layout <em>above</em> layout
            that the wizard pre-selects for you.", 'bread') ?></p>
          </div>
          <p><?php esc_html_e('Number of meetings on list: ', 'bread') ?><span id="wizard_meeting_count"></span></p>
          <p><?php esc_html_e('Select one of the layouts appropriate to the number of meetings', 'bread') ?></p>
          <select id="wizard_layout" name="wizard_layout">
          </select>
        </div>
        <div id="step-4" class="tab-pane" role="tabpanel" aria-labelledby="step-4">
          <h3><?php esc_html_e('Step 4: Select Option', 'bread') ?></h3>
          <p><?php esc_html_e('Choose a language (only applies to names of days and format code descriptions): ', 'bread') ?></p>
          <select id="wizard_language" name="wizard_language">
          </select><br/>
          <div id="wizard-virtual-meeting-section">
          <?php esc_html_e('Where should virtual meetings be included:', 'bread') ?>
          <fieldset id="wizard_virtual_meetings">
              <input type="radio" value="1" name="wizard_virtual_meetings" id="wizard_additional_list" checked>
              <label for="wizard_additional_list"><?php esc_html_e('Place virtual meetings in a separate list, with specialized format.', 'bread') ?></label><br/>
              <input type="radio" value="0" name="wizard_virtual_meetings" id="wizard_no_additional_list">
              <label for="wizard_no_additional_list"><?php esc_html_e('Include virtual meetings in main meeting list.', 'bread') ?></label><br/>
              <input type="radio" value="-1" name="wizard_virtual_meetings" id="wizard_no_virtual_meetings">
              <label for="wizard_no_virtual_meetings"><?php esc_html_e("Don't include virtual meetings at all.", 'bread') ?></label><br/>
          </fieldset></div>
          <p><?php esc_html_e('Organize the meetings by:', 'bread') ?>
          <select name="wizard_meeting_sort">
            <option value="day"><?php esc_html_e('Day', 'bread') ?></option>
            <option value="city"><?php esc_html_e('City', 'bread') ?></option>
            <option value="group"><?php esc_html_e('Group', 'bread') ?></option>
          </select>
        </div>
        <div id="step-5" class="tab-pane" role="tabpanel" aria-labelledby="step-5">
          <div class="step-description">
            <p><?php esc_html_e("In this step, we create the meeting list settings.  If you already have a meeting list defined
              on this site, the old settings will not be overwriten, rather, a new setting will be created.", 'bread') ?></p>
            <p><?php esc_html_e("Before creating the meeting list configuration, you are given the opportunity to give the configuration
              a mnemonic name.  If you are managing multiple meeting lists on this same, this name can help you keep track of which
              configuration is which.", 'bread') ?></p>
            <p><?php esc_html_e("After creating the configuration, you will be able to view the resulting meeting list.  Note that this is not usually
              ready to be printed.  Users almost always want to add content, such as service meetings, help lines, place for people to
              collect phone numbers etc.  To do this, click on the 'Customize' tab to the left, and then go to either the 'Front Page' or
              'Custom Section' tabs.  The editors there contain a 'Meeting List Shortcodes' dropdown menu.  Using these, new structures can
              be added with only a few mouse clicks.", 'bread') ?></p>
            <p><?php esc_html_e("You may also want to adjust the font sizes on your meeting list so that it fits on a single sheet or is more readable.  This is only
              of the many options available in the 'Customizer'.", 'bread') ?>.</p>
          </div>
          <div id="wizard-before-create">
            <p><label for="wizard-setting-name"><?php esc_html_e('Enter a custom name for this configuration: ', 'bread') ?></label>
            <input type="text" placeholder="Use default value" id="wizard-setting-name" name="wizard-setting-name"/></p>
            <button type="button" class="btn btn-primary" onClick="breadWizard.ajax_submit()"><?php esc_html_e('Create Meeting List', 'bread') ?></button>
          </div>
          <div id="wizard-after-create">
            <p class="valid-feedback dashicons-before dashicons-yes-alt"><?php esc_html_e('Congratulations! The meeting list configuration has been created!', 'bread') ?></p>
            <p><?php esc_html_e('The first thing you probably want to do is have a look at the meeting list:', 'bread') ?></p>
            <button type="button" class="btn btn-primary" onClick="breadWizard.generate_meeting_list()"><?php esc_html_e('Generate Meeting List', 'bread') ?></button>
            <p>
              <?php esc_html_e('To add a link to the meeting list on your website, use the following link.', 'bread') ?>
            </p><div id="wizard-show-link"></div>
            <p><?php esc_html_e('If you want to try a different layout: ', 'bread') ?><button type="button" class="btn btn-primary" onClick="breadWizard.redo_layout()"><?php esc_html_e('Go Back', 'bread') ?></button>
            </p><div id="wizard-show-link"></div>
            <p><?php esc_html_e('You probably want to add some content to the first page or the custom content:', 'bread') ?></p>
            <button type="button" class="btn btn-primary" onClick="breadWizard.finish()"><?php esc_html_e('Open customizer', 'bread') ?></button>
          </div>
        </div>
    </div>
    <!-- Include optional progressbar HTML -->
    <div class="progress">
      <div class="progress-bar" role="progressbar" style="width: 0%" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
    </div>
    </form>
</div>