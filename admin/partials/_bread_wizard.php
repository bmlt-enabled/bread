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
    <form method="post">
        <?php wp_nonce_field('pwsix_wizard_nonce', 'pwsix_wizard_nonce'); ?>
        <input type="hidden" name="pwsix_action" value="wizard" />
    <div class="tab-content">
        <div id="step-1" class="tab-pane" role="tabpanel" aria-labelledby="step-1">
        <h3>Step 1: Enter your BMLT root server</h3>
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
            <br/><select style="width: 400px;" id="wizard_format_filter" name="wizard_format_filte">
            </select></p>
            </div>
        </div>
        <div id="step-3" class="tab-pane" role="tabpanel" aria-labelledby="step-3">
          <h3>Step 3: Page Layout</h3>
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
          <p>Where should virtual meetings be included:
          <fieldset id="wizard_virtual_meetings">
              <input type="radio" value="1" name="wizard_virtual_meetings" id="wizard_additional_list" checked>
              <label for="wizard_additional_list">Place virtual meetings in a separate list, with specialized format.</label><br/>
              <input type="radio" value="0" name="wizard_virtual_meetings" id="wizard_no_additional_list">
              <label for="wizard_no_additional_list">Include virtual meetings in main meeting list.</label><br/>
              <input type="radio" value="-1" name="wizard_virtual_meetings" id="wizard_no_virtual_meetings">
              <label for="wizard_no_virtual_meetings">Don't include virtual meetings at all.</label><br/>
          </fieldset></p>
        </div>
        <div id="step-5" class="tab-pane" role="tabpanel" aria-labelledby="step-5">
        <?php submit_button(__('Create Meeting List'), 'button-primary', 'create_meeting_list', false); ?>
        </div>
    </div>
    <!-- Include optional progressbar HTML -->
    <div class="progress">
      <div class="progress-bar" role="progressbar" style="width: 0%" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
    </div>
    </form>
</div>