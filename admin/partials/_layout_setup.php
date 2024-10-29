<?php
if (basename($_SERVER['PHP_SELF']) == basename(__FILE__)) {
    die('Sorry, but you cannot access this page directly.');
} ?>
<div id="poststuff">
    <div id="postbox-container" class="postbox-container">
        <div id="normal-sortables" class="meta-box-sortables ui-sortable">
            <div id="pagelayoutdiv" class="postbox">
                <?php $title = '
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
                <h3 class="hndle">Page Layout<span title='<?php echo $title; ?>' class="bottom-tooltip"></span></h3>
                <div class="inside">
                    <p>
                        <input name="bread_version" value="2.0" type="hidden">
                        <input class="mlg" id="flyer" type="radio" name="page_fold" value="flyer" <?php echo (Bread::getOption('page_fold') == 'flyer' ? 'checked' : '') ?>><label for="flyer">Flyer&nbsp;&nbsp;&nbsp;</label>
                        <input class="mlg" id="tri" type="radio" name="page_fold" value="tri" <?php echo (Bread::getOption('page_fold') == 'tri' ? 'checked' : '') ?>><label for="tri">Tri-Fold&nbsp;&nbsp;&nbsp;</label>
                        <input class="mlg" id="quad" type="radio" name="page_fold" value="quad" <?php echo (Bread::getOption('page_fold') == 'quad' ? 'checked' : '') ?>><label for="quad">Quad-Fold&nbsp;&nbsp;&nbsp;</label>
                        <input class="mlg" id="half" type="radio" name="page_fold" value="half" <?php echo (Bread::getOption('page_fold') == 'half' ? 'checked' : '') ?>><label for="half">Half-Fold&nbsp;&nbsp;&nbsp;</label>
                        <input class="mlg" id="full" type="radio" name="page_fold" value="full" <?php echo (Bread::getOption('page_fold') == 'full' ? 'checked' : '') ?>><label for="full">Full Page</label>
                    </p>
                    <p>
                        <input class="mlg" id="portrait" type="radio" name="page_orientation" value="P" <?php echo (Bread::getOption('page_orientation') == 'P' ? 'checked' : '') ?>><label for="portrait">Portrait&nbsp;&nbsp;&nbsp;</label>
                        <input class="mlg" id="landscape" type="radio" name="page_orientation" value="L" <?php echo (Bread::getOption('page_orientation') == 'L' ? 'checked' : '') ?>><label for="landscape">Landscape</label>
                        <input class="mlg" id="booklet_pages" type="checkbox" name="booklet_pages" value="1" <?php echo (Bread::getOption('booklet_pages') == '1' ? 'checked' : '') ?> /><label for="booklet_pages">Add extra pages for booklet</label>
                    <p>
                        <input class="mlg" id="5inch" type="radio" name="page_size" value="5inch" <?php echo (Bread::getOption('page_size') == '5inch' ? 'checked' : '') ?>><label for="5inch">5 inch&nbsp;&nbsp;&nbsp;</label>
                        <input class="mlg" id="letter" type="radio" name="page_size" value="letter" <?php echo (Bread::getOption('page_size') == 'letter' ? 'checked' : '') ?>><label for="letter">Letter&nbsp;&nbsp;&nbsp;</label>
                        <input class="mlg" id="legal" type="radio" name="page_size" value="legal" <?php echo (Bread::getOption('page_size') == 'legal' ? 'checked' : '') ?>><label for="legal">Legal&nbsp;&nbsp;&nbsp;</label>
                        <input class="mlg" id="ledger" type="radio" name="page_size" value="ledger" <?php echo (Bread::getOption('page_size') == 'ledger' ? 'checked' : '') ?>><label for="ledger">Ledger&nbsp;&nbsp;&nbsp;</label>
                        <input class="mlg" id="A4" type="radio" name="page_size" value="A4" <?php echo (Bread::getOption('page_size') == 'A4' ? 'checked' : '') ?>><label for="A4">A4&nbsp;&nbsp;&nbsp;</label>
                        <input class="mlg" id="A5" type="radio" name="page_size" value="A5" <?php echo (Bread::getOption('page_size') == 'A5' ? 'checked' : '') ?>><label for="A5">A5&nbsp;&nbsp;&nbsp;</label>
                        <input class="mlg" id="A6" type="radio" name="page_size" value="A6" <?php echo (Bread::getOption('page_size') == 'A6' ? 'checked' : '') ?>><label for="A6">A6&nbsp;&nbsp;&nbsp;</label>
                    </p>
                    </p>
                    <div id="marginsdiv" style="border-top: 1px solid #EEE;">
                        <p>
                            Page Margin Top: <input min="0" max="20" step="1" size="3" maxlength="3" type="number" class="bmlt-input-field" style="display:inline;" id="margin_top" name="margin_top" value="<?php echo Bread::getOptionForDisplay('margin_top', '3'); ?>" />&nbsp;&nbsp;&nbsp;
                            Bottom: <input min="0" max="20" step="1" size="3" maxlength="3" type="number" class="bmlt-input-field" style="display:inline;" id="margin_bottom" name="margin_bottom" value="<?php echo Bread::getOptionForDisplay('margin_bottom', '3'); ?>" />&nbsp;&nbsp;&nbsp;
                            Left: <input min="0" max="20" step="1" size="3" maxlength="3" type="number" class="bmlt-input-field" style="display:inline;" id="margin_left" name="margin_left" value="<?php echo Bread::getOptionForDisplay('margin_left', '3'); ?>" />&nbsp;&nbsp;&nbsp;
                            Right: <input min="0" max="20" step="1" size="3" maxlength="3" type="number" class="bmlt-input-field" style="display:inline;" id="margin_right" name="margin_right" value="<?php echo Bread::getOptionForDisplay('margin_right', '3'); ?>" />&nbsp;&nbsp;&nbsp;
                        </p>
                    </div>
                    <div id="watermarkandheaderdiv" style="border-top: 1px solid #EEE;">
                        <p>
                            The page header is a title that goes across the entire page above the meetings.
                        </p>
                        <p>
                            <label for="pageheader_fontsize">Font Size: </label><input min="4" max="40" step=".1" size="3" maxlength="3" type="number" class="bmlt-input-field" style="display:inline;" id="pageheader_fontsize" name="pageheader_fontsize" value="<?php echo Bread::getOption('pageheader_fontsize'); ?>" />
                            <label for="pageheader_textcolor" style="margin-left:10px;">Text Color:</label> <input style="display: inline-block !important; width: 70px; margin-right: 5px;" type='text' id="pageheader_textcolor" name="pageheader_textcolor" value="<?php echo Bread::getOption('pageheader_textcolor'); ?>" />
                            <label for="pageheader_backgroundcolor" style="margin-left:10px;">Background Color:</label> <input style="display: inline-block !important; width: 70px; margin-right: 5px;" type='text' id="pageheader_backgroundcolor" name="pageheader_backgroundcolor" value="<?php echo Bread::getOption('pageheader_backgroundcolor'); ?>" />
                        </p>
                        <p>
                            Header Margin Top: <input min="0" max="20" step="1" size="3" maxlength="3" type="number" class="bmlt-input-field" style="display:inline;" id="margin_header" name="margin_header" value="<?php echo esc_html(Bread::getOption('margin_header')); ?>" />&nbsp;&nbsp;&nbsp;
                            <br>Header Text: <input size="100" type="text" id="pageheader_content" name="pageheader_content" value="<?php echo Bread::getOptionForDisplay('pageheader_content', ''); ?>" />&nbsp;&nbsp;&nbsp;
                            <br>Watermark: <input size="100" type="text" id="watermark" name="watermark" autocomplete="off" value="<?php echo Bread::getOptionForDisplay('watermark', ''); ?>" />&nbsp;&nbsp;&nbsp;
                        </p>
                    </div>
                    <?php if (Bread::getOption('page_fold') == 'half' || Bread::getOption('page_fold') == 'full') {
                        ?>
                        <div class="myfooter_div">
                            <label for="nonmeeting_footer">Custom Footer: </label>
                            <input name="nonmeeting_footer" type="text" size="50" value="<?php echo Bread::getOption('nonmeeting_footer'); ?>">
                            <br />
                            <label for="margin_footer">Margin Footer: </label>
                            <input min="0" max="20" step="1" size="3" maxlength="3" type="number" class="bmlt-input-field" style="display:inline;" id="margin_footer" name="margin_footer" value="<?php echo Bread::getOptionForDisplay('margin_footer', '5'); ?>" />

                        </div>
                        <div id="pagenodiv" style="border-top: 1px solid #EEE;">
                            <p>
                                Page Numbers Font Size: <input min="1" max="20" step="1" size="3" maxlength="3" type="number" class="bmlt-input-field" style="display:inline;" id="pagenumbering_font_size" name="pagenumbering_font_size" value="<?php echo Bread::getOptionForDisplay('pagenumbering_font_size', '9'); ?>" />
                            </p>
                        </div>
                    <?php } else {
                        ?>
                        <div id="columngapdiv" style="border-top: 1px solid #EEE;">
                            <p>
                                Column Gap Width: <input min="1" max="20" step="1" size="3" maxlength="3" type="number" class="bmlt-input-field" style="display:inline;" id="column_gap" name="column_gap" value="<?php echo Bread::getOptionForDisplay('column_gap', '5'); ?>" />
                            </p>
                        </div>
                        <div id="columnseparatordiv" style="border-top: 1px solid #EEE;">

                            <p>
                            <table>
                                <tr>
                                    <input class="mlg" name="column_line" value="0" type="hidden">
                                    <td style="">Separator: <input type="checkbox" name="column_line" value="1" <?php echo (Bread::getOption('column_line') == '1' ? 'checked' : '') ?> /></td>
                                    <td style="">
                                        <div class="theme" id="sp-light">
                                            <label for="col_color">Color:</label> <input style="display: inline-block !important; width: 70px; margin-right: 5px;" type='text' id="col_color" name="col_color" value="<?php echo Bread::getOptionForDisplay('col_color', '#bfbfbf'); ?>" />
                                        </div>
                                    </td>
                                </tr>
                            </table>

                            </p>
                        </div>
                    <?php }
                    ?>
                    <div id="basefontdiv" style="border-top: 1px solid #EEE;">
                        <p>
                        <table>
                            <tr>
                                <input class="mlg" name="base_font" value="0" type="hidden">
                                <td style="padding-right: 10px;">
                                    <label for="base_font">Base Font: </label>
                                    <select id="base_font" name="base_font">
                                        <option value="dejavusanscondensed" <?php echo Bread::getOption('base_font') == 'dejavusanscondensed' ? "selected=\"selected\"" : "" ?>>DejaVu Sans Condensed</option>
                                        <option value="courier" <?php echo Bread::getOption('base_font') == 'courier' ? "selected=\"selected\"" : "" ?>>Courier</option>
                                        <option value="times" <?php echo Bread::getOption('base_font') == 'times' ? "selected=\"selected\"" : "" ?>>Times</option>
                                        <option value="arial" <?php echo Bread::getOption('base_font') == 'arial' ? "selected=\"selected\"" : "" ?>>Arial</option>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <input class="mlg" name="colorspace" value="0" type="hidden">
                                <td style="padding-right: 10px;">
                                    <label for="colorspace">Color space: </label>
                                    <select id="colorspace" name="colorspace">
                                        <option value="0" <?php echo Bread::getOption('colorspace') == '0' ? "selected=\"selected\"" : "" ?>>Unrestricted</option>
                                        <option value="1" <?php echo Bread::getOption('colorspace') == '1' ? "selected=\"selected\"" : "" ?>>Greyscale</option>
                                        <option value="2" <?php echo Bread::getOption('colorspace') == '2' ? "selected=\"selected\"" : "" ?>>RGB</option>
                                        <option value="3" <?php echo Bread::getOption('colorspace') == '3' ? "selected=\"selected\"" : "" ?>>CMYK</option>
                                    </select>
                                </td>

                            </tr>
                        </table>
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
                        <p><input type="checkbox" name="include_protection" value="1" <?php echo (Bread::getOption('include_protection') == '1' ? 'checked' : '') ?>>Enable PDF Protection<span title='<?php echo $title; ?>' class="top-tooltip"></span></p>
                        <p>
                        <div style="overflow: none; height: 0px;background: transparent;" data-description="dummyPanel for Chrome auto-fill issue">
                            <input type="text" style="height:0;width:0; background: transparent; color: transparent;border: none;" data-description="dummyUsername">
                            <input type="password" style="height:0;width:0;background: transparent; color: transparent;border: none;" data-description="dummyPassword">
                        </div>
                        <label for="protection_password">Password: </label>
                        <input class="protection_pass" id="protection_password" type="password" name="protection_password" value="<?php echo Bread::getOptionForDisplay('protection_password', ''); ?>" />
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php if ($this->admin->current_user_can_modify()) {
        echo '
    <input type="submit" value="Save Changes" id="bmltmeetinglistsave1" name="bmltmeetinglistsave" class="button-primary" />
 ';
    } ?>
    <?php echo '<p style="display: inline; margin-top:.5em;margin-bottom:1.0em;margin-left:.2em;"><a target="_blank" class="button-primary" href="' . home_url() . '/?current-meeting-list=' . $this->admin->loaded_setting . '">Generate Meeting List</a></p>'; ?>
    <div style="display:inline;"><i>&nbsp;&nbsp;Save Changes before Generate Meeting List.</i></div>
    <br class="clear">
</div>