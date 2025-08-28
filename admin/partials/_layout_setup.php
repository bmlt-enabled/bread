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
                        <p class="bmlt-heading-h2"><?php esc_html_e('Page Layout Defaults', 'bread') ?></p>
                        <table style="border-collapse: collapse; font-size:12px;" border="1" cellpadding="8">
                            <tbody>
                                <tr>
                                    <td><strong><?php esc_html_e('Meeting List Size', 'bread') ?></strong></td>
                                    <td><strong><?php esc_html_e('Page Layout', 'bread') ?></strong></td>
                                    <td><strong><?php esc_html_e('Orientation', 'bread') ?></strong></td>
                                    <td><strong><?php esc_html_e('Paper Size', 'bread') ?></strong></td>
                                    <td><strong><?php esc_html_e('Page Height', 'bread') ?></strong></td>
                                </tr>
                                <tr>
                                    <td><?php esc_html_e('Smaller Areas', 'bread') ?></td>
                                    <td><?php esc_html_e('Tri-Fold', 'bread') ?></td>
                                    <td><?php esc_html_e('Landscape', 'bread') ?></td>
                                    <td><?php esc_html_e('Letter', 'bread') ?>, <?php esc_html_e('A4', 'bread') ?></td>
                                    <td>195, 180</td>
                                </tr>
                                <tr>
                                    <td><?php esc_html_e('Medium Area', 'bread') ?></td>
                                    <td><?php esc_html_e('Quad-Fold', 'bread') ?></td>
                                    <td><?php esc_html_e('Landscape', 'bread') ?></td>
                                    <td><?php esc_html_e('Legal', 'bread') ?>, <?php esc_html_e('A4', 'bread') ?></td>
                                    <td>195, 180</td>
                                </tr>
                                <tr>
                                    <td><?php esc_html_e('Large Area, Region, Metro', 'bread') ?></td>
                                    <td><?php esc_html_e('Half-Fold', 'bread') ?></td>
                                    <td><?php esc_html_e('Landscape', 'bread') ?></td>
                                    <td><?php esc_html_e('Booklet', 'bread') ?>, <?php esc_html_e('A5', 'bread') ?></td>
                                    <td>250, 260</td>
                                </tr>
                                <tr>
                                    <td><?php esc_html_e('Anything', 'bread') ?></td>
                                    <td><?php esc_html_e('Full Page', 'bread') ?></td>
                                    <td><?php esc_html_e('Portrait, Landscape', 'bread') ?></td>
                                    <td><?php esc_html_e('Letter', 'bread') ?>, <?php esc_html_e('Legal', 'bread') ?>, <?php esc_html_e('A4', 'bread') ?></td>
                                    <td><?php esc_html_e('None', 'bread') ?></td>
                                </tr>
                            </tbody>
                        </table>
                        <p><?php esc_html_e('When a layout is clicked defaults are reset for orientation, paper size and page height.', 'bread') ?></p>
                    </span>
                </div>
                <h3 class="hndle"><?php esc_html_e('Page Layout', 'bread') ?><span data-tooltip-content="#pagelayout-tooltip-content" class="my-tooltip"><span class="tooltipster-icon">(?)</span></span></h3>
                <div class="inside">
                    <input name="bread_version" value=<?php echo esc_html(BREAD_VERSION); ?> type="hidden">
                    <div style="display:flex;">
                        <div style="border:solid;flex:1;margin-right:10px;padding:2px 6px 6px 6px;line-height:1.5;"><?php esc_html_e('Single Page', 'bread') ?><br />
                            <input class="mlg single-page-check" id="flyer" type="radio" name="page_fold" value="flyer" <?php echo ($this->bread->getOption('page_fold') == 'flyer' ? 'checked' : '') ?>><label for="flyer"><?php esc_html_e('Flyer', 'bread') ?>&nbsp;&nbsp;&nbsp;</label>
                            <input class="mlg single-page-check" id="tri" type="radio" name="page_fold" value="tri" <?php echo ($this->bread->getOption('page_fold') == 'tri' ? 'checked' : '') ?>><label for="tri"><?php esc_html_e('Tri-Fold', 'bread') ?>&nbsp;&nbsp;&nbsp;</label>
                            <input class="mlg single-page-check" id="quad" type="radio" name="page_fold" value="quad" <?php echo ($this->bread->getOption('page_fold') == 'quad' ? 'checked' : '') ?>><label for="quad"><?php esc_html_e('Quad-Fold', 'bread') ?>&nbsp;&nbsp;&nbsp;</label>
                            <br />
                            <input class="mlg single-page" id="portrait" type="radio" name="page_orientation" value="P" <?php echo ($this->bread->getOption('page_orientation') == 'P' ? 'checked' : '') ?>><label class="single-page" for="portrait"><?php esc_html_e('Portrait', 'bread') ?>&nbsp;&nbsp;&nbsp;</label>
                            <input class="mlg single-page" id="landscape" type="radio" name="page_orientation" value="L" <?php echo ($this->bread->getOption('page_orientation') == 'L' ? 'checked' : '') ?>><label class="single-page" for="landscape"><?php esc_html_e('Landscape', 'bread') ?></label>
                        </div>
                        <div style="border:solid;flex:1;padding:2px 6px 6px 6px;line-height:1.5;"><?php esc_html_e('Booklets', 'bread') ?><br />
                            <input class="mlg booklet-check" id="half" type="radio" name="page_fold" value="half" <?php echo ($this->bread->getOption('page_fold') == 'half' ? 'checked' : '') ?>><label for="half"><?php esc_html_e('Half-Fold', 'bread') ?>&nbsp;&nbsp;&nbsp;</label>
                            <input class="mlg booklet-check" id="full" type="radio" name="page_fold" value="full" <?php echo ($this->bread->getOption('page_fold') == 'full' ? 'checked' : '') ?>><label for="full"><?php esc_html_e('Full Page', 'bread') ?></label>
                            <br />
                            <input class="mlg booklet" id="booklet_pages" type="checkbox" name="booklet_pages" value="1" <?php echo ($this->bread->getOption('booklet_pages') == '1' ? 'checked' : '') ?> /><label class="booklet" for="booklet_pages"><?php esc_html_e('Add extra pages for booklet', 'bread') ?></label>
                        </div>
                    </div>
                    <br />
                    <div>
                        <?php esc_html_e('Page Size:', 'bread') ?><br />
                        <input class="mlg booklet" id="5inch" type="radio" name="page_size" value="5inch" <?php echo ($this->bread->getOption('page_size') == '5inch' ? 'checked' : '') ?>><label for="5inch" class="booklet"><?php esc_html_e('5 inch', 'bread') ?>&nbsp;&nbsp;&nbsp;</label>
                        <input class="mlg" id="letter" type="radio" name="page_size" value="letter" <?php echo ($this->bread->getOption('page_size') == 'letter' ? 'checked' : '') ?>><label for="letter"><?php esc_html_e('Letter', 'bread') ?>&nbsp;&nbsp;&nbsp;</label>
                        <input class="mlg" id="legal" type="radio" name="page_size" value="legal" <?php echo ($this->bread->getOption('page_size') == 'legal' ? 'checked' : '') ?>><label for="legal"><?php esc_html_e('Legal', 'bread') ?>&nbsp;&nbsp;&nbsp;</label>
                        <input class="mlg" id="ledger" type="radio" name="page_size" value="ledger" <?php echo ($this->bread->getOption('page_size') == 'ledger' ? 'checked' : '') ?>><label for="ledger"><?php esc_html_e('Ledger', 'bread') ?>&nbsp;&nbsp;&nbsp;</label>
                        <input class="mlg" id="A4" type="radio" name="page_size" value="A4" <?php echo ($this->bread->getOption('page_size') == 'A4' ? 'checked' : '') ?>><label for="A4"><?php esc_html_e('A4', 'bread') ?>&nbsp;&nbsp;&nbsp;</label>
                        <input class="mlg booklet" id="A5" type="radio" name="page_size" value="A5" <?php echo ($this->bread->getOption('page_size') == 'A5' ? 'checked' : '') ?>><label for="A5" class="booklet"><?php esc_html_e('A5', 'bread') ?>&nbsp;&nbsp;&nbsp;</label>
                        <input class="mlg booklet A6" id="A6" type="radio" name="page_size" value="A6" <?php echo ($this->bread->getOption('page_size') == 'A6' ? 'checked' : '') ?>><label for="A6" class="booklet A6"><?php esc_html_e('A6', 'bread') ?>&nbsp;&nbsp;&nbsp;</label>
                        <div id="marginsdiv" style="border-top: 1px solid #EEE;">
                            <?php esc_html_e('Page Margin Top: ', 'bread') ?><input min="0" max="20" step="1" size="3" maxlength="3" type="number" class="bmlt-input-field" style="display:inline;" id="margin_top" name="margin_top" value="<?php echo esc_attr($this->bread->getOptionForDisplay('margin_top', '3')); ?>" />&nbsp;&nbsp;&nbsp;
                            <?php esc_html_e('Bottom: ', 'bread') ?><input min="0" max="20" step="1" size="3" maxlength="3" type="number" class="bmlt-input-field" style="display:inline;" id="margin_bottom" name="margin_bottom" value="<?php echo esc_attr($this->bread->getOptionForDisplay('margin_bottom', '3')); ?>" />&nbsp;&nbsp;&nbsp;
                            <?php esc_html_e('Left: ', 'bread') ?><input min="0" max="20" step="1" size="3" maxlength="3" type="number" class="bmlt-input-field" style="display:inline;" id="margin_left" name="margin_left" value="<?php echo esc_attr($this->bread->getOptionForDisplay('margin_left', '3')); ?>" />&nbsp;&nbsp;&nbsp;
                            <?php esc_html_e('Right: ', 'bread') ?><input min="0" max="20" step="1" size="3" maxlength="3" type="number" class="bmlt-input-field" style="display:inline;" id="margin_right" name="margin_right" value="<?php echo esc_attr($this->bread->getOptionForDisplay('margin_right', '3')); ?>" />&nbsp;&nbsp;&nbsp;
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
                            <?php esc_html_e('This section describes things on the page other than the contents. Headers, footers, page numbers.', 'bread') ?>
                            <br />
                            <?php esc_html_e('What options you see will be dependant on the layout selected.', 'bread') ?>
                        </p>
                    </div>
                </div>
                <h3 class="hndle"><?php esc_html_e('Page Decorations', 'bread') ?><span data-tooltip-content="#pagedeco-tooltip-content" class="my-tooltip"><span class="tooltipster-icon">(?)</span></span></h3>
                <div class="inside">
                    <div id="watermarkandheaderdiv" style="border-top: 1px solid #EEE;" class="single-page">
                        <?php esc_html_e('The page header is a title that goes across the entire page above the meetings.', 'bread') ?>
                        <br />
                        <label for="pageheader_fontsize"><?php esc_html_e('Font Size: ', 'bread') ?></label><input min="4" max="40" step=".1" size="3" maxlength="3" type="number" class="bmlt-input-field" style="display:inline;" id="pageheader_fontsize" name="pageheader_fontsize" value="<?php echo esc_attr($this->bread->getOption('pageheader_fontsize')); ?>" />
                        <label for="pageheader_textcolor" style="margin-left:10px;"><?php esc_html_e('Text Color:', 'bread') ?></label> <input style="display: inline-block !important; width: 70px; margin-right: 5px;" type='color' class="bmlt_color" id="pageheader_textcolor" name="pageheader_textcolor" value="<?php echo esc_attr($this->bread->getOption('pageheader_textcolor')); ?>" />
                        <label for="pageheader_backgroundcolor" style="margin-left:10px;"><?php esc_html_e('Background Color:', 'bread') ?></label> <input style="display: inline-block !important; width: 70px; margin-right: 5px;" type='color' id="pageheader_backgroundcolor" class="bmlt_color" name="pageheader_backgroundcolor" value="<?php echo esc_attr($this->bread->getOption('pageheader_backgroundcolor')); ?>" />
                        <?php esc_html_e('Header Margin Top: ', 'bread') ?><input min="0" max="20" step="1" size="3" maxlength="3" type="number" class="bmlt-input-field" style="display:inline;" id="margin_header" name="margin_header" value="<?php echo esc_attr($this->bread->getOption('margin_header')); ?>" />&nbsp;&nbsp;&nbsp;
                        <br><?php esc_html_e('Header Text: ', 'bread') ?><input size="100" type="text" id="pageheader_content" name="pageheader_content" value="<?php echo esc_attr($this->bread->getOptionForDisplay('pageheader_content', '')); ?>" />&nbsp;&nbsp;&nbsp;
                        <br><?php esc_html_e('Watermark: ', 'bread') ?><input size="100" type="text" id="watermark" name="watermark" autocomplete="off" value="<?php echo esc_url($this->bread->getOptionForDisplay('watermark', '')); ?>" />&nbsp;&nbsp;&nbsp;
                    </div>
                    <div class="myfooter_div booklet">
                        <label for="nonmeeting_footer"><?php esc_html_e('Custom Footer: ', 'bread') ?></label>
                        <input name="nonmeeting_footer" type="text" size="50" value="<?php echo esc_attr($this->bread->getOption('nonmeeting_footer')); ?>">
                        <br />
                        <label for="margin_footer"><?php esc_html_e('Margin Footer: ', 'bread') ?></label>
                        <input min="0" max="20" step="1" size="3" maxlength="3" type="number" class="bmlt-input-field" style="display:inline;" id="margin_footer" name="margin_footer" value="<?php echo esc_attr($this->bread->getOptionForDisplay('margin_footer', '5')); ?>" />

                    </div>
                    <div id="pagenodiv" style="border-top: 1px solid #EEE;" class="booklet">
                        <?php esc_html_e('Page Numbers Font Size: ', 'bread') ?><input min="1" max="20" step="1" size="3" maxlength="3" type="number" class="bmlt-input-field" style="display:inline;" id="pagenumbering_font_size" name="pagenumbering_font_size" value="<?php echo esc_attr($this->bread->getOptionForDisplay('pagenumbering_font_size', '9')); ?>" />
                    </div>
                    <div id="columngapdiv" style="border-top: 1px solid #EEE;" class="single-page">
                        <?php esc_html_e('Column Gap Width: ', 'bread') ?><input min="1" max="20" step="1" size="3" maxlength="3" type="number" class="bmlt-input-field" style="display:inline;" id="column_gap" name="column_gap" value="<?php echo esc_attr($this->bread->getOptionForDisplay('column_gap', '5')); ?>" />
                    </div>
                    <div id="columnseparatordiv" style="border-top: 1px solid #EEE;" class="single-page">
                        <input class="mlg" name="column_line" value="0" type="hidden">
                        <?php esc_html_e('Separator: ', 'bread') ?><input type="checkbox" name="column_line" value="1" <?php echo ($this->bread->getOption('column_line') == '1' ? 'checked' : '') ?> /></td>
                        <label for="col_color"><?php esc_html_e('Color:', 'bread') ?></label> <input style="display: inline-block !important; width: 70px; margin-right: 5px;" type='color' class="bmlt-color" id="col_color" name="col_color" value="<?php echo esc_html($this->bread->getOptionForDisplay('col_color', '#bfbfbf')); ?>" />
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="postbox-container">
        <div class="meta-box-sortables ui-sortable">
            <div class="postbox">
                <h3 class="hndle"><?php esc_html_e('Base Fonts and Colors', 'bread') ?></h3>
                <div class="inside">
                    <div id="basefontdiv" style="border-top: 1px solid #EEE;">
                        <input class="mlg" name="base_font" value="0" type="hidden">
                        <label for="base_font"><?php esc_html_e('Base Font: ', 'bread') ?></label>
                        <select id="base_font" name="base_font">
                            <option value="dejavusanscondensed" <?php echo $this->bread->getOption('base_font') == 'dejavusanscondensed' ? "selected=\"selected\"" : "" ?>><?php esc_html_e('DejaVu Sans Condensed', 'bread') ?></option>
                            <option value="courier" <?php echo $this->bread->getOption('base_font') == 'courier' ? "selected=\"selected\"" : "" ?>><?php esc_html_e('Courier', 'bread') ?></option>
                            <option value="times" <?php echo $this->bread->getOption('base_font') == 'times' ? "selected=\"selected\"" : "" ?>><?php esc_html_e('Times', 'bread') ?></option>
                            <option value="arial" <?php echo $this->bread->getOption('base_font') == 'arial' ? "selected=\"selected\"" : "" ?>><?php esc_html_e('Arial', 'bread') ?></option>
                        </select>
                        <input class="mlg" name="colorspace" value="0" type="hidden">
                        <label for="colorspace"><?php esc_html_e('Color space: ', 'bread') ?></label>
                        <select id="colorspace" name="colorspace">
                            <option value="0" <?php echo $this->bread->getOption('colorspace') == '0' ? "selected=\"selected\"" : "" ?>><?php esc_html_e('Unrestricted', 'bread') ?></option>
                            <option value="1" <?php echo $this->bread->getOption('colorspace') == '1' ? "selected=\"selected\"" : "" ?>><?php esc_html_e('Greyscale', 'bread') ?></option>
                            <option value="2" <?php echo $this->bread->getOption('colorspace') == '2' ? "selected=\"selected\"" : "" ?>><?php esc_html_e('RGB', 'bread') ?></option>
                            <option value="3" <?php echo $this->bread->getOption('colorspace') == '3' ? "selected=\"selected\"" : "" ?>><?php esc_html_e('CMYK', 'bread') ?></option>
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
                        <strong><?php esc_html_e('Enable PDF Protection.', 'bread') ?></strong>
                        <?php esc_html_e('Encrypts and sets the PDF document permissions for the PDF file.', 'bread') ?>
                        <br/>
                        <?php esc_html_e('Encrypted PDFs can be opened and printed.', 'bread') ?>
                        <br/>
                        <?php esc_html_e('Optional Password to allow editing in a PDF editor.', 'bread') ?>
                        <br/>
                        <?php esc_html_e('Note: Encrypted PDFs cannot be opened in MS Word at all.', 'bread') ?>
                    </div>
                </div>
                <h3 class="hndle"><?php esc_html_e('Password Protection', 'bread') ?><span data-tooltip-content="#pdfpassword-tooltip-content" class="my-tooltip"><span class="tooltipster-icon">(?)</span></span></h3>
                <div class="inside">
                    <div id="includeprotection" style="border-top: 1px solid #EEE;">
                        <input name="include_protection" value="0" type="hidden">
                        <input type="checkbox" name="include_protection" value="1" <?php echo ($this->bread->getOption('include_protection') == '1' ? 'checked' : '') ?>><?php esc_html_e('Enable Protection', 'bread') ?>
                        <div style="overflow: none; height: 0px;background: transparent;" data-description="dummyPanel for Chrome auto-fill issue">
                            <input type="text" style="height:0;width:0; background: transparent; color: transparent;border: none;" data-description="dummyUsername">
                            <input type="password" style="height:0;width:0;background: transparent; color: transparent;border: none;" data-description="dummyPassword">
                        </div>
                        <label for="protection_password"><?php esc_html_e('Password: ', 'bread') ?></label>
                        <input class="protection_pass" id="protection_password" type="password" name="protection_password" value="<?php echo esc_attr($this->bread->getOptionForDisplay('protection_password', '')); ?>" />
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>