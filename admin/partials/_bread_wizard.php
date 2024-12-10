<div id="bread-wizard">
    <ul class="nav">
        <li class="nav-item">
          <a class="nav-link" href="#step-1">
            <div class="num">1</div>
            BMLT Root Server
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="#step-2">
            <span class="num">2</span>
            Select Service Bodies
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="#step-3">
            <span class="num">3</span>
            Choose Layout
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link " href="#step-4">
            <span class="num">4</span>
            Meeting List Options
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link " href="#step-4">
            <span class="num">5</span>
            Create Meeting List
          </a>
        </li>
    </ul>
    <form method="post" id="wizard_form">
        <?php wp_nonce_field('pwsix_wizard_nonce', 'pwsix_wizard_nonce'); ?>
        <input type="hidden" name="pwsix_action" value="wizard" />
        <input type="hidden" id="wizard_setting_id" name="wizard_setting_id" value="" />
    <div class="tab-content">
        <div id="step-1" class="tab-pane" role="tabpanel" aria-labelledby="step-1">
        <h3>Step 1: Enter your BMLT root server</h3>
        <div class="step-description">
        <p>This wizard guides you through the initial configuation of a meeting list.  If you have already created one or more meeting lists,
          don't worry, this process alway creates a new configuration, and never overwrites old ones.</p>
        <p>Bread is highly customizable, as areas often try to maintain the appearance of their old meeting lists.  This wizard only attempts to give you a reasonable
          starting point, not an end product.  At the end of the wizard we make a few suggestions for possible next steps.</p>
        <p>The first thing we need is the location of your BMLT root server.  Once you have entered it, you need check that the URL is correct
          by clicking the "Test Connection" button.</p>
        </div>
        <p>Visit <a target="_blank" href="https://doihavethebmlt.org/">Do I have the BMLT?</a> to find your BMLT server<br/>
            <label for="wizard_root_server">BMLT Server URL: </label>
            <input class="bmlt-input" id="wizard_root_server" type="text" name="wizard_root_server"
                            onKeypress="breadWizard.root_server_keypress(event)" onChange="breadWizard.root_server_changed()" />
            <button type="button" onClick="breadWizard.test_root_server()">Test Root Server Connection</button>
            <div id="wizard_root_server_result">
            Verify that this is valid root server URL before continuing
            </div></p>
        </div>
        <div id="step-2" class="tab-pane" role="tabpanel" aria-labelledby="step-2">
            <div style="min-height: 300px;">
            <h3>Step 2: Select a service body</h3>
            <div class="step-description">
            <p>Next, we need to know which meetings are going to be on the meeting list.  This will help the wizard when it
              tries to choose an a appropriate layout for you.</p>
            </div>
            <select class="chosen-select" style="width: 400px;" data-placeholder="Choose up to 5 service bodies" id="wizard_service_bodies" name="wizard_service_bodies[]" multiple="multiple">
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
            <p>If you want to limit the meeting list to a particular format, for instance, to create a language-specific meeting list, you can enter it here.
            <br/><select style="width: 400px;" id="wizard_format_filter" name="wizard_format_filter">
            </select></p>
            </div>
        </div>
        <div id="step-3" class="tab-pane" role="tabpanel" aria-labelledby="step-3">
          <h3>Step 3: Page Layout</h3>
          <div class="step-description">
          <p>Most areas try to fit their meetings onto a single sheet of paper.  And it is particularly adventageous to
            use a tri-fold format, so that the list can be printed at home and placed along side the pamphlets. The priority should always be that the
            meeting list is readable.  For many areas, a booklet format is better, even if the reqire more effort or expense to print.</p>
          <p>In this step we suggest suitible formats based on your meeting count.  Having the meetings fit on a single side has the advantage
            that the list can be posted on a bulleitin board, and this will be used when suggesting a page layout.  However, you may
            also wish to have the meetings "overflow" on to the back side of the paper.  If so, select a layout <em>above</em> layout
            that the wizard pre-selects for you.</p>
          </div>
          <p>Number of meetings on list: <span id="wizard_meeting_count"></span></p>
          <p>Select one of the layouts appropriate to the number of meetings</p>
          <select id="wizard_layout" name="wizard_layout">
          </select>
        </div>
        <div id="step-4" class="tab-pane" role="tabpanel" aria-labelledby="step-4">
          <h3>Step 4: Select Options</h3>
          <p>Choose a language (only applies to names of days and format code descriptions):</p>
          <select id="wizard_language" name="wizard_language">
          </select><br/>
          <div id="wizard-virtual-meeting-section">
          Where should virtual meetings be included:
          <fieldset id="wizard_virtual_meetings">
              <input type="radio" value="1" name="wizard_virtual_meetings" id="wizard_additional_list" checked>
              <label for="wizard_additional_list">Place virtual meetings in a separate list, with specialized format.</label><br/>
              <input type="radio" value="0" name="wizard_virtual_meetings" id="wizard_no_additional_list">
              <label for="wizard_no_additional_list">Include virtual meetings in main meeting list.</label><br/>
              <input type="radio" value="-1" name="wizard_virtual_meetings" id="wizard_no_virtual_meetings">
              <label for="wizard_no_virtual_meetings">Don't include virtual meetings at all.</label><br/>
          </fieldset></div>
          <p>Organize the meetings by:
          <select name="wizard_meeting_sort">
            <option value="day">Day</option>
            <option value="city">City</option>
            <option value="group">Group</option>
          </select>
        </div>
        <div id="step-5" class="tab-pane" role="tabpanel" aria-labelledby="step-5">
          <div class="step-description">
            <p>In this step, we create the meeting list settings.  If you already have a meeting list defined
              on this site, the old settings will not be overwriten, rather, a new setting will be created.</p>
            <p>Before creating the meeting list configuration, you are given the opportunity to give the configuration
              a mnemonic name.  If you are managing multiple meeting lists on this same, this name can help you keep track of which
              configuration is which.</p>
            <p>After creating the configuration, you will be able to view the resulting meeting list.  Note that this is not usually
              ready to be printed.  Users almost always want to add content, such as service meetings, help lines, place for people to
              collect phone numbers etc.  To do this, click on the "Customize" tab to the left, and then go to either the "Front Page" or
              "Custom Section" tabs.  The editors there contain a "Meeting List Shortcodes" dropdown menu.  Using these, new structures can
              be added with only a few mouse clicks.</p>
            <p>You may also want to adjust the font sizes on your meeting list so that it fits on a single sheet or is more readable.  This is only
              of the many options available in the "Customizer".</p>
          </div>
          <div id="wizard-before-create">
            <p><label for="wizard-setting-name">Enter a custom name for this configuration: </label>
            <input type="text" placeholder="Use default value" id="wizard-setting-name" name="wizard-setting-name"/></p>
            <button type="button" class="btn btn-primary" onClick="breadWizard.ajax_submit()">Create Meeting List</button>
          </div>
          <div id="wizard-after-create">
            <p class="valid-feedback dashicons-before dashicons-yes-alt">Congratulations! The meeting list configuration has been created!</p>
            <p>The first thing you probably want to do is have a look at the meeting list:</p>
            <button type="button" class="btn btn-primary" onClick="breadWizard.generate_meeting_list()">Generate Meeting List</button>
            <p>
              To add a link to the meeting list on your website, use the following link.
            </p><div id="wizard-show-link"></div>
            <p>If you want to try a different layout: <button type="button" class="btn btn-primary" onClick="breadWizard.redo_layout()">Go Back</button>
            </p><div id="wizard-show-link"></div>
            <p>You probably want to add some content to the first page or the custom content:</p>
            <button type="button" class="btn btn-primary" onClick="breadWizard.finish()">Open customizer</button>
          </div>
        </div>
    </div>
    <!-- Include optional progressbar HTML -->
    <div class="progress">
      <div class="progress-bar" role="progressbar" style="width: 0%" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
    </div>
    </form>
</div>