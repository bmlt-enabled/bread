<?php
if (basename($_SERVER['PHP_SELF']) == basename(__FILE__)) {
    die('Sorry, but you cannot access this page directly.');
} ?>
<div id="poststuff">
    <div id="postbox-container" class="postbox-container">
        <div id="normal-sortables" class="meta-box-sortables ui-sortable">
            <div id="pagelayoutdiv" class="postbox">
                <div style="display:none;">
                    <span id="pagelayout-tooltip-content" class="tooltip-content">
                        <p class="bmlt-heading-h2"><?php _e('Page Layout Defaults', 'bread-domain') ?></p>
                        <table style="border-collapse: collapse; font-size:12px;" border="1" cellpadding="8">
                            <tbody>
                                <tr>
                                    <td><strong><?php _e('Meeting List Size', 'bread-domain') ?></strong></td>
                                    <td><strong><?php _e('Page Layout', 'bread-domain') ?></strong></td>
                                    <td><strong><?php _e('Orientation', 'bread-domain') ?></strong></td>
                                    <td><strong><?php _e('Paper Size', 'bread-domain') ?></strong></td>
                                    <td><strong><?php _e('Page Height', 'bread-domain') ?></strong></td>
                                </tr>
                                <tr>
                                    <td><?php _e('Smaller Areas', 'bread-domain') ?></td>
                                    <td><?php _e('Tri-Fold', 'bread-domain') ?></td>
                                    <td><?php _e('Landscape', 'bread-domain') ?></td>
                                    <td><?php _e('Letter', 'bread-domain') ?>, <?php _e('A4', 'bread-domain') ?></td>
                                    <td>195, 180</td>
                                </tr>
                                <tr>
                                    <td><?php _e('Medium Area', 'bread-domain') ?></td>
                                    <td><?php _e('Quad-Fold', 'bread-domain') ?></td>
                                    <td><?php _e('Landscape', 'bread-domain') ?></td>
                                    <td><?php _e('Legal', 'bread-domain') ?>, <?php _e('A4', 'bread-domain') ?></td>
                                    <td>195, 180</td>
                                </tr>
                                <tr>
                                    <td><?php _e('Large Area, Region, Metro', 'bread-domain') ?></td>
                                    <td><?php _e('Half-Fold', 'bread-domain') ?></td>
                                    <td><?php _e('Landscape', 'bread-domain') ?></td>
                                    <td><?php _e('Booklet', 'bread-domain') ?>, <?php _e('A5', 'bread-domain') ?></td>
                                    <td>250, 260</td>
                                </tr>
                                <tr>
                                    <td><?php _e('Anything', 'bread-domain') ?></td>
                                    <td><?php _e('Full Page', 'bread-domain') ?></td>
                                    <td><?php _e('Portrait, Landscape', 'bread-domain') ?></td>
                                    <td><?php _e('Letter', 'bread-domain') ?>, <?php _e('Legal', 'bread-domain') ?>, <?php _e('A4', 'bread-domain') ?></td>
                                    <td><?php _e('None', 'bread-domain') ?></td>
                                </tr>
                            </tbody>
                        </table>
                        <p><?php _e('When a layout is clicked defaults are reset for orientation, paper size and page height.', 'bread-domain') ?></p>
                    </span>
                </div>
                <h3 class="hndle"><?php _e('Page Layout', 'bread-domain') ?><span data-tooltip-content="#pagelayout-tooltip-content" class="my-tooltip"><span class="tooltipster-icon">(?)</span></span></h3>
                <div class="inside">
                    <input name="bread_version" value=<?php echo esc_html(BREAD_VERSION); ?> type="hidden">
                    <div style="display:flex;">
                        <div style="border:solid;flex:1;margin-right:10px;padding:2px 6px 6px 6px;line-height:1.5;"><?php _e('Single Page', 'bread-domain') ?><br />
                            <input class="mlg single-page-check" id="flyer" type="radio" name="page_fold" value="flyer" <?php echo ($this->bread->getOption('page_fold') == 'flyer' ? 'checked' : '') ?>><label for="flyer"><?php _e('Flyer', 'bread-domain') ?>&nbsp;&nbsp;&nbsp;</label>
                            <input class="mlg single-page-check" id="tri" type="radio" name="page_fold" value="tri" <?php echo ($this->bread->getOption('page_fold') == 'tri' ? 'checked' : '') ?>><label for="tri"><?php _e('Tri-Fold', 'bread-domain') ?>&nbsp;&nbsp;&nbsp;</label>
                            <input class="mlg single-page-check" id="quad" type="radio" name="page_fold" value="quad" <?php echo ($this->bread->getOption('page_fold') == 'quad' ? 'checked' : '') ?>><label for="quad"><?php _e('Quad-Fold', 'bread-domain') ?>&nbsp;&nbsp;&nbsp;</label>
                            <br />
                            <input class="mlg single-page" id="portrait" type="radio" name="page_orientation" value="P" <?php echo ($this->bread->getOption('page_orientation') == 'P' ? 'checked' : '') ?>><label class="single-page" for="portrait"><?php _e('Portrait', 'bread-domain') ?>&nbsp;&nbsp;&nbsp;</label>
                            <input class="mlg single-page" id="landscape" type="radio" name="page_orientation" value="L" <?php echo ($this->bread->getOption('page_orientation') == 'L' ? 'checked' : '') ?>><label class="single-page" for="landscape"><?php _e('Landscape', 'bread-domain') ?></label>
                        </div>
                        <div style="border:solid;flex:1;padding:2px 6px 6px 6px;line-height:1.5;"><?php _e('Booklets', 'bread-domain') ?><br />
                            <input class="mlg booklet-check" id="half" type="radio" name="page_fold" value="half" <?php echo ($this->bread->getOption('page_fold') == 'half' ? 'checked' : '') ?>><label for="half"><?php _e('Half-Fold', 'bread-domain') ?>&nbsp;&nbsp;&nbsp;</label>
                            <input class="mlg booklet-check" id="full" type="radio" name="page_fold" value="full" <?php echo ($this->bread->getOption('page_fold') == 'full' ? 'checked' : '') ?>><label for="full"><?php _e('Full Page', 'bread-domain') ?></label>
                            <br />
                            <input class="mlg booklet" id="booklet_pages" type="checkbox" name="booklet_pages" value="1" <?php echo ($this->bread->getOption('booklet_pages') == '1' ? 'checked' : '') ?> /><label class="booklet" for="booklet_pages"><?php _e('Add extra pages for booklet', 'bread-domain') ?></label>
                        </div>
                    </div>
                    <br />
                    <div>
                        <?php _e('Page Size:', 'bread-domain') ?><br />
                        <input class="mlg booklet" id="5inch" type="radio" name="page_size" value="5inch" <?php echo ($this->bread->getOption('page_size') == '5inch' ? 'checked' : '') ?>><label for="5inch" class="booklet"><?php _e('5 inch', 'bread-domain') ?>&nbsp;&nbsp;&nbsp;</label>
                        <input class="mlg" id="letter" type="radio" name="page_size" value="letter" <?php echo ($this->bread->getOption('page_size') == 'letter' ? 'checked' : '') ?>><label for="letter"><?php _e('Letter', 'bread-domain') ?>&nbsp;&nbsp;&nbsp;</label>
                        <input class="mlg" id="legal" type="radio" name="page_size" value="legal" <?php echo ($this->bread->getOption('page_size') == 'legal' ? 'checked' : '') ?>><label for="legal"><?php _e('Legal', 'bread-domain') ?>&nbsp;&nbsp;&nbsp;</label>
                        <input class="mlg" id="ledger" type="radio" name="page_size" value="ledger" <?php echo ($this->bread->getOption('page_size') == 'ledger' ? 'checked' : '') ?>><label for="ledger"><?php _e('Ledger', 'bread-domain') ?>&nbsp;&nbsp;&nbsp;</label>
                        <input class="mlg" id="A4" type="radio" name="page_size" value="A4" <?php echo ($this->bread->getOption('page_size') == 'A4' ? 'checked' : '') ?>><label for="A4"><?php _e('A4', 'bread-domain') ?>&nbsp;&nbsp;&nbsp;</label>
                        <input class="mlg booklet" id="A5" type="radio" name="page_size" value="A5" <?php echo ($this->bread->getOption('page_size') == 'A5' ? 'checked' : '') ?>><label for="A5" class="booklet"><?php _e('A5', 'bread-domain') ?>&nbsp;&nbsp;&nbsp;</label>
                        <input class="mlg booklet A6" id="A6" type="radio" name="page_size" value="A6" <?php echo ($this->bread->getOption('page_size') == 'A6' ? 'checked' : '') ?>><label for="A6" class="booklet A6"><?php _e('A6', 'bread-domain') ?>&nbsp;&nbsp;&nbsp;</label>
                        <div id="marginsdiv" style="border-top: 1px solid #EEE;">
                            <?php _e('Page Margin Top: ', 'bread-domain') ?><input min="0" max="20" step="1" size="3" maxlength="3" type="number" class="bmlt-input-field" style="display:inline;" id="margin_top" name="margin_top" value="<?php echo esc_attr($this->bread->getOptionForDisplay('margin_top', '3')); ?>" />&nbsp;&nbsp;&nbsp;
                            <?php _e('Bottom: ', 'bread-domain') ?><input min="0" max="20" step="1" size="3" maxlength="3" type="number" class="bmlt-input-field" style="display:inline;" id="margin_bottom" name="margin_bottom" value="<?php echo esc_attr($this->bread->getOptionForDisplay('margin_bottom', '3')); ?>" />&nbsp;&nbsp;&nbsp;
                            <?php _e('Left: ', 'bread-domain') ?><input min="0" max="20" step="1" size="3" maxlength="3" type="number" class="bmlt-input-field" style="display:inline;" id="margin_left" name="margin_left" value="<?php echo esc_attr($this->bread->getOptionForDisplay('margin_left', '3')); ?>" />&nbsp;&nbsp;&nbsp;
                            <?php _e('Right: ', 'bread-domain') ?><input min="0" max="20" step="1" size="3" maxlength="3" type="number" class="bmlt-input-field" style="display:inline;" id="margin_right" name="margin_right" value="<?php echo esc_attr($this->bread->getOptionForDisplay('margin_right', '3')); ?>" />&nbsp;&nbsp;&nbsp;
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="postbox-container">
        <div class="meta-box-sortables ui-sortable">
            <div class="postbox">
                <div style="display:none;">
                    <div id="pagedeco-tooltip-content">
                        <p>
                            <?php _e('This section describes things on the page other than the contents. Headers, footers, page numbers.', 'bread-domain') ?>
                            <br />
                            <?php _e('What options you see will be dependant on the layout selected.', 'bread-domain') ?>
                        </p>
                    </div>
                </div>
                <h3 class="hndle"><?php _e('Page Decorations', 'bread-domain') ?><span data-tooltip-content="#pagedeco-tooltip-content" class="my-tooltip"><span class="tooltipster-icon">(?)</span></span></h3>
                <div class="inside">
                    <div id="watermarkandheaderdiv" style="border-top: 1px solid #EEE;" class="single-page">
                        <?php _e('The page header is a title that goes across the entire page above the meetings.', 'bread-domain') ?>
                        <br />
                        <label for="pageheader_fontsize"><?php _e('Font Size: ', 'bread-domain') ?></label><input min="4" max="40" step=".1" size="3" maxlength="3" type="number" class="bmlt-input-field" style="display:inline;" id="pageheader_fontsize" name="pageheader_fontsize" value="<?php echo esc_attr($this->bread->getOption('pageheader_fontsize')); ?>" />
                        <label for="pageheader_textcolor" style="margin-left:10px;"><?php _e('Text Color:', 'bread-domain') ?></label> <input style="display: inline-block !important; width: 70px; margin-right: 5px;" type='color' class="bmlt_color" id="pageheader_textcolor" name="pageheader_textcolor" value="<?php echo esc_attr($this->bread->getOption('pageheader_textcolor')); ?>" />
                        <label for="pageheader_backgroundcolor" style="margin-left:10px;"><?php _e('Background Color:', 'bread-domain') ?></label> <input style="display: inline-block !important; width: 70px; margin-right: 5px;" type='color' id="pageheader_backgroundcolor" class="bmlt_color" name="pageheader_backgroundcolor" value="<?php echo esc_attr($this->bread->getOption('pageheader_backgroundcolor')); ?>" />
                        <?php _e('Header Margin Top: ', 'bread-domain') ?><input min="0" max="20" step="1" size="3" maxlength="3" type="number" class="bmlt-input-field" style="display:inline;" id="margin_header" name="margin_header" value="<?php echo esc_attr($this->bread->getOption('margin_header')); ?>" />&nbsp;&nbsp;&nbsp;
                        <br><?php _e('Header Text: ', 'bread-domain') ?><input size="100" type="text" id="pageheader_content" name="pageheader_content" value="<?php echo esc_attr($this->bread->getOptionForDisplay('pageheader_content', '')); ?>" />&nbsp;&nbsp;&nbsp;
                        <br><?php _e('Watermark: ', 'bread-domain') ?><input size="100" type="text" id="watermark" name="watermark" autocomplete="off" value="<?php echo esc_url($this->bread->getOptionForDisplay('watermark', '')); ?>" />&nbsp;&nbsp;&nbsp;
                    </div>
                    <div class="myfooter_div booklet">
                        <label for="nonmeeting_footer"><?php _e('Custom Footer: ', 'bread-domain') ?></label>
                        <input name="nonmeeting_footer" type="text" size="50" value="<?php echo esc_attr($this->bread->getOption('nonmeeting_footer')); ?>">
                        <br />
                        <label for="margin_footer"><?php _e('Margin Footer: ', 'bread-domain') ?></label>
                        <input min="0" max="20" step="1" size="3" maxlength="3" type="number" class="bmlt-input-field" style="display:inline;" id="margin_footer" name="margin_footer" value="<?php echo esc_attr($this->bread->getOptionForDisplay('margin_footer', '5')); ?>" />

                    </div>
                    <div id="pagenodiv" style="border-top: 1px solid #EEE;" class="booklet">
                        <?php _e('Page Numbers Font Size: ', 'bread-domain') ?><input min="1" max="20" step="1" size="3" maxlength="3" type="number" class="bmlt-input-field" style="display:inline;" id="pagenumbering_font_size" name="pagenumbering_font_size" value="<?php echo esc_attr($this->bread->getOptionForDisplay('pagenumbering_font_size', '9')); ?>" />
                    </div>
                    <div id="columngapdiv" style="border-top: 1px solid #EEE;" class="single-page">
                        <?php _e('Column Gap Width: ', 'bread-domain') ?><input min="1" max="20" step="1" size="3" maxlength="3" type="number" class="bmlt-input-field" style="display:inline;" id="column_gap" name="column_gap" value="<?php echo esc_attr($this->bread->getOptionForDisplay('column_gap', '5')); ?>" />
                    </div>
                    <div id="columnseparatordiv" style="border-top: 1px solid #EEE;" class="single-page">
                        <input class="mlg" name="column_line" value="0" type="hidden">
                        <?php _e('Separator: ', 'bread-domain') ?><input type="checkbox" name="column_line" value="1" <?php echo ($this->bread->getOption('column_line') == '1' ? 'checked' : '') ?> /></td>
                        <label for="col_color"><?php _e('Color:', 'bread-domain') ?></label> <input style="display: inline-block !important; width: 70px; margin-right: 5px;" type='color' class="bmlt-color" id="col_color" name="col_color" value="<?php echo esc_html($this->bread->getOptionForDisplay('col_color', '#bfbfbf')); ?>" />
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="postbox-container">
        <div class="meta-box-sortables ui-sortable">
            <div class="postbox">
                <h3 class="hndle"><?php _e('Base Fonts and Colors', 'bread-domain') ?></h3>
                <div class="inside">
                    <div id="basefontdiv" style="border-top: 1px solid #EEE;">
                        <input class="mlg" name="base_font" value="0" type="hidden">
                        <label for="base_font"><?php _e('Base Font: ', 'bread-domain') ?></label>
                        <select id="base_font" name="base_font">
                            <option value="dejavusanscondensed" <?php echo $this->bread->getOption('base_font') == 'dejavusanscondensed' ? "selected=\"selected\"" : "" ?>><?php _e('DejaVu Sans Condensed', 'bread-domain') ?></option>
                            <option value="courier" <?php echo $this->bread->getOption('base_font') == 'courier' ? "selected=\"selected\"" : "" ?>><?php _e('Courier', 'bread-domain') ?></option>
                            <option value="times" <?php echo $this->bread->getOption('base_font') == 'times' ? "selected=\"selected\"" : "" ?>><?php _e('Times', 'bread-domain') ?></option>
                            <option value="arial" <?php echo $this->bread->getOption('base_font') == 'arial' ? "selected=\"selected\"" : "" ?>><?php _e('Arial', 'bread-domain') ?></option>
                        </select>
                        <input class="mlg" name="colorspace" value="0" type="hidden">
                        <label for="colorspace"><?php _e('Color space: ', 'bread-domain') ?></label>
                        <select id="colorspace" name="colorspace">
                            <option value="0" <?php echo $this->bread->getOption('colorspace') == '0' ? "selected=\"selected\"" : "" ?>><?php _e('Unrestricted', 'bread-domain') ?></option>
                            <option value="1" <?php echo $this->bread->getOption('colorspace') == '1' ? "selected=\"selected\"" : "" ?>><?php _e('Greyscale', 'bread-domain') ?></option>
                            <option value="2" <?php echo $this->bread->getOption('colorspace') == '2' ? "selected=\"selected\"" : "" ?>><?php _e('RGB', 'bread-domain') ?></option>
                            <option value="3" <?php echo $this->bread->getOption('colorspace') == '3' ? "selected=\"selected\"" : "" ?>><?php _e('CMYK', 'bread-domain') ?></option>
                        </select>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="postbox-container">
        <div class="meta-box-sortables ui-sortable">
            <div class="postbox">
                <div style="display:none;">
                    <div id="pdfpassword-tooltip-content">
                        <strong><?php _e('Enable PDF Protection.', 'bread-domain') ?></strong>
                        <?php _e('Encrypts and sets the PDF document permissions for the PDF file.', 'bread-domain') ?>
                        <br/>
                        <?php _e('Encrypted PDFs can be opened and printed.', 'bread-domain') ?>
                        <br/>
                        <?php _e('Optional Password to allow editing in a PDF editor.', 'bread-domain') ?>
                        <br/>
                        <?php _e('Note: Encrypted PDFs cannot be opened in MS Word at all.', 'bread-domain') ?>
                    </div>
                </div>
                <h3 class="hndle"><?php _e('Password Protection', 'bread-domain') ?><span data-tooltip-content="#pdfpassword-tooltip-content" class="my-tooltip"><span class="tooltipster-icon">(?)</span></span></h3>
                <div class="inside">
                    <div id="includeprotection" style="border-top: 1px solid #EEE;">
                        <input name="include_protection" value="0" type="hidden">
                        <input type="checkbox" name="include_protection" value="1" <?php echo ($this->bread->getOption('include_protection') == '1' ? 'checked' : '') ?>><?php _e('Enable Protection', 'bread-domain') ?>
                        <div style="overflow: none; height: 0px;background: transparent;" data-description="dummyPanel for Chrome auto-fill issue">
                            <input type="text" style="height:0;width:0; background: transparent; color: transparent;border: none;" data-description="dummyUsername">
                            <input type="password" style="height:0;width:0;background: transparent; color: transparent;border: none;" data-description="dummyPassword">
                        </div>
                        <label for="protection_password"><?php _e('Password: ', 'bread-domain') ?></label>
                        <input class="protection_pass" id="protection_password" type="password" name="protection_password" value="<?php echo esc_attr($this->bread->getOptionForDisplay('protection_password', '')); ?>" />
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>