<?php
if (basename($_SERVER['PHP_SELF']) == basename(__FILE__)) {
    die('Sorry, but you cannot access this page directly.');
} ?>
<div id="poststuff">
    <div id="postbox-container" class="postbox-container">
        <div id="normal-sortables" class="meta-box-sortables ui-sortable">
            <div id="pagelayoutdiv" class="postbox">
                <?PHP $title = '
                <p class="bmlt-heading-h2">Page Layout Defaults</p>
                <table style="border-collapse: collapse; font-size:12px;" border="1" cellpadding="8">
                <tbody>
                <tr>
                <td><strong>Meeting List Size</strong></td>
                <td><strong>Page Layout</strong></td>
                <td><strong>Orientation</strong></td>
                <td><strong>Paper Size</strong></td>
                <td><strong>Page Height</strong></td>
                </tr>
                <tr>
                <td>Smaller Areas</td>
                <td>Tri-Fold</td>
                <td>Landscape</td>
                <td>Letter, A4</td>
                <td>195, 180</td>
                </tr>
                <tr>
                <td>Medium Area</td>
                <td>Quad-Fold</td>
                <td>Landscape</td>
                <td>Legal, A4</td>
                <td>195, 180</td>
                </tr>
                <tr>
                <td>Large Area, Region, Metro</td>
                <td>Half-Fold</td>
                <td>Landscape</td>
                <td>Booklet, A5</td>
                <td>250, 260</td>
                </tr>
                <tr>
                <td>Anything</td>
                <td>Full Page</td>
                <td>Portrait, Landscape</td>
                <td>Letter, Legal, A4</td>
                <td>None</td>
                </tr>
                </tbody>
                </table>
                <p>When a layout is clicked defaults are reset for orientation, paper size and page height.</p>
                ';
                ?>
                <h3 class="hndle">Page Layout<span title='<?PHP echo $title; ?>' class="bottom-tooltip"></span></h3>
                <div class="inside">
                    <p>
                    <input class="mlg" id="tri" type="radio" name="page_fold" value="tri" <?php echo ($this->options['page_fold'] == 'tri' ? 'checked' : '') ?>><label for="tri">Tri-Fold&nbsp;&nbsp;&nbsp;</label>
                    <input class="mlg" id="quad" type="radio" name="page_fold" value="quad" <?php echo ($this->options['page_fold'] == 'quad' ? 'checked' : '') ?>><label for="quad">Quad-Fold&nbsp;&nbsp;&nbsp;</label>
                    <input class="mlg" id="half" type="radio" name="page_fold" value="half" <?php echo ($this->options['page_fold'] == 'half' ? 'checked' : '') ?>><label for="half">Half-Fold&nbsp;&nbsp;&nbsp;</label>
                    <input class="mlg" id="full" type="radio" name="page_fold" value="full" <?php echo ($this->options['page_fold'] == 'full' ? 'checked' : '') ?>><label for="full">Full Page</label>
                    </p>
                    <p>
                    <input class="mlg" id="portrait" type="radio" name="page_orientation" value="P" <?php echo ($this->options['page_orientation'] == 'P' ? 'checked' : '') ?>><label for="portrait">Portrait&nbsp;&nbsp;&nbsp;</label>
                    <input class="mlg" id="landscape" type="radio" name="page_orientation" value="L" <?php echo ($this->options['page_orientation'] == 'L' ? 'checked' : '') ?>><label for="landscape">Landscape</label>
                    <p>
                    <input class="mlg" id="5inch" type="radio" name="page_size" value="5inch" <?php echo ($this->options['page_size'] == '5inch' ? 'checked' : '') ?>><label for="5inch">Booklet (11" X 8.5")&nbsp;&nbsp;&nbsp;</label>
                    <input class="mlg" id="A5" type="radio" name="page_size" value="A5" <?php echo ($this->options['page_size'] == 'A5' ? 'checked' : '') ?>><label for="A5">Booklet-A5 (297mm X 210mm)&nbsp;&nbsp;&nbsp;</label>
                    <input class="mlg" id="letter" type="radio" name="page_size" value="letter" <?php echo ($this->options['page_size'] == 'letter' ? 'checked' : '') ?>><label for="letter">Letter (8.5" X 11")&nbsp;&nbsp;&nbsp;</label>
                    <input class="mlg" id="legal" type="radio" name="page_size" value="legal" <?php echo ($this->options['page_size'] == 'legal' ? 'checked' : '') ?>><label for="legal">Legal (8.5" X 14")&nbsp;&nbsp;&nbsp;</label>
                    <input class="mlg" id="ledger" type="radio" name="page_size" value="ledger" <?php echo ($this->options['page_size'] == 'ledger' ? 'checked' : '') ?>><label for="ledger">Ledger (17" X 11")&nbsp;&nbsp;&nbsp;</label>
                    <input class="mlg" id="A4" type="radio" name="page_size" value="A4" <?php echo ($this->options['page_size'] == 'A4' ? 'checked' : '') ?>><label for="A4">A4 (210mm X 297mm)</label>
                    </p>
                    </p>
                    <div id="marginsdiv" style="border-top: 1px solid #EEE;">
                        <p>
                        Page Margin Top: <input min="0" max="20" step="1" size="3" maxlength="3" type="number" class="bmlt-input-field" style="display:inline;" id="margin_top" name="margin_top" value="<?php echo $this->options['margin_top'] ;?>" />&nbsp;&nbsp;&nbsp;
                        Bottom: <input min="0" max="20" step="1" size="3" maxlength="3" type="number" class="bmlt-input-field" style="display:inline;" id="margin_bottom" name="margin_bottom" value="<?php echo $this->options['margin_bottom'] ;?>" />&nbsp;&nbsp;&nbsp;
                        Left: <input min="0" max="20" step="1" size="3" maxlength="3" type="number" class="bmlt-input-field" style="display:inline;" id="margin_left" name="margin_left" value="<?php echo $this->options['margin_left'] ;?>" />&nbsp;&nbsp;&nbsp;
                        Right: <input min="0" max="20" step="1" size="3" maxlength="3" type="number" class="bmlt-input-field" style="display:inline;" id="margin_right" name="margin_right" value="<?php echo $this->options['margin_right'] ;?>" />&nbsp;&nbsp;&nbsp;
                        </p>
                    </div>
                    <div id="columngapdiv" style="border-top: 1px solid #EEE;">
                        <p>
                        Column Gap Width: <input min="1" max="20" step="1" size="3" maxlength="3" type="number" class="bmlt-input-field" style="display:inline;" id="column_gap" name="column_gap" value="<?php echo $this->options['column_gap'] ;?>" />									
                        </p>
                    </div>
                    <div id="columnseparatordiv" style="border-top: 1px solid #EEE;">
                    
                        <p>
                        <table><tr>
                        <input class="mlg" name="column_line" value="0" type="hidden">
                        <td style="">Separator: <input type="checkbox" name="column_line" value="1" <?php echo ($this->options['column_line'] == '1' ? 'checked' : '') ?> /></td>
                        <td style="">
                            <div class="theme" id="sp-light">
                                <label for="col_color">Color:</label>  <input style="display: inline-block !important; width: 70px; margin-right: 5px;" type='text' id="col_color" name="col_color" value="<?php echo $this->options['col_color'] ;?>" />
                            </div>
                        </td>
                        </tr></table>
                        
                        </p>
                    </div>
                    <div id="includeprotection" style="border-top: 1px solid #EEE;">
                        <?PHP $title = '
                        <p>Enable <strong>PDF Protection</strong>.</p>
                        <p>Encrypts and sets the PDF document permissions for the PDF file.</p>
                        
                        <p>PDF can be opened and printed.
                        
                        <p>Optional Password to allow editing in a PDF editor.
                        <p>Note: PDF is encrypted and cannot be opened in MS Word at all.</p>
                        ';
                        ?>
                        <input name="include_protection" value="0" type="hidden">
                        <p><input type="checkbox" name="include_protection" value="1" <?php echo ($this->options['include_protection'] == '1' ? 'checked' : '') ?>>Enable PDF Protection<span title='<?PHP echo $title; ?>' class="top-tooltip"></span></p>
                        <p>
                        <label for="protection_password">Password: </label>
                        <input class="protection_pass" id="protection_password" type="password" name="protection_password" value="<?php echo $this->options['protection_password'] ;?>" />
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <input type="submit" value="Save Changes" id="bmltmeetinglistsave2" name="bmltmeetinglistsave" class="button-primary" />
    <?php echo '<p style="display: inline; margin-top:.5em;margin-bottom:1.0em;margin-left:.2em;"><a target="_blank" class="button-primary" href="'.home_url() . '/?current-meeting-list=1">Generate Meeting List</a></p>'; ?>
    <div style="display:inline;"><i>&nbsp;&nbsp;Save Changes before Generate Meeting List.</i></div>
    <br class="clear">
</div>
