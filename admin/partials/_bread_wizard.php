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
            </div>
        </div>
        <div id="step-3" class="tab-pane" role="tabpanel" aria-labelledby="step-3">
          <h3>Step 3: Page Layout</h3>
        </div>
        <div id="step-4" class="tab-pane" role="tabpanel" aria-labelledby="step-4">
       //<?php submit_button(__('Create Meeting List'), 'button-primary', 'create_meeting_list', false); ?>
        </div>
    </div>
    <!-- Include optional progressbar HTML -->
    <div class="progress">
      <div class="progress-bar" role="progressbar" style="width: 0%" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
    </div>
    </form>
</div>