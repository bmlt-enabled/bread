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
                    </span>
                </div>
                <h3 class="hndle">Page Layout<span data-tooltip-content="#pagelayout-tooltip-content" class="my-tooltip"><span class="tooltipster-icon">(?)</span></span></h3>
                <div class="inside">
                    <input name="bread_version" value=<?php echo esc_html(BREAD_VERSION); ?> type="hidden">
                    <div style="display:flex;">
                        <div style="border:solid;flex:1;margin-right:10px;padding:2px 6px 6px 6px;line-height:1.5;">Single Page<br />
                            <input class="mlg single-page-check" id="flyer" type="radio" name="page_fold" value="flyer" <?php echo ($this->bread->getOption('page_fold') == 'flyer' ? 'checked' : '') ?>><label for="flyer">Flyer&nbsp;&nbsp;&nbsp;</label>
                            <input class="mlg single-page-check" id="tri" type="radio" name="page_fold" value="tri" <?php echo ($this->bread->getOption('page_fold') == 'tri' ? 'checked' : '') ?>><label for="tri">Tri-Fold&nbsp;&nbsp;&nbsp;</label>
                            <input class="mlg single-page-check" id="quad" type="radio" name="page_fold" value="quad" <?php echo ($this->bread->getOption('page_fold') == 'quad' ? 'checked' : '') ?>><label for="quad">Quad-Fold&nbsp;&nbsp;&nbsp;</label>
                            <br />
                            <input class="mlg single-page" id="portrait" type="radio" name="page_orientation" value="P" <?php echo ($this->bread->getOption('page_orientation') == 'P' ? 'checked' : '') ?>><label class="single-page" for="portrait">Portrait&nbsp;&nbsp;&nbsp;</label>
                            <input class="mlg single-page" id="landscape" type="radio" name="page_orientation" value="L" <?php echo ($this->bread->getOption('page_orientation') == 'L' ? 'checked' : '') ?>><label class="single-page" for="landscape">Landscape</label>
                        </div>
                        <div style="border:solid;flex:1;padding:2px 6px 6px 6px;line-height:1.5;">Booklets<br />
                            <input class="mlg booklet-check" id="half" type="radio" name="page_fold" value="half" <?php echo ($this->bread->getOption('page_fold') == 'half' ? 'checked' : '') ?>><label for="half">Half-Fold&nbsp;&nbsp;&nbsp;</label>
                            <input class="mlg booklet-check" id="full" type="radio" name="page_fold" value="full" <?php echo ($this->bread->getOption('page_fold') == 'full' ? 'checked' : '') ?>><label for="full">Full Page</label>
                            <br />
                            <input class="mlg booklet" id="booklet_pages" type="checkbox" name="booklet_pages" value="1" <?php echo ($this->bread->getOption('booklet_pages') == '1' ? 'checked' : '') ?> /><label class="booklet" for="booklet_pages">Add extra pages for booklet</label>
                        </div>
                    </div>
                    <br />
                    <div>
                        Page Size:<br />
                        <input class="mlg booklet" id="5inch" type="radio" name="page_size" value="5inch" <?php echo ($this->bread->getOption('page_size') == '5inch' ? 'checked' : '') ?>><label for="5inch" class="booklet">5 inch&nbsp;&nbsp;&nbsp;</label>
                        <input class="mlg single-page" id="pocket" type="radio" name="page_size" value="pocket" <?php echo ($this->bread->getOption('page_size') == 'pocket' ? 'checked' : '') ?>><label for="pocket" class="single-page">Pocket&nbsp;&nbsp;&nbsp;</label>
                        <input class="mlg" id="letter" type="radio" name="page_size" value="letter" <?php echo ($this->bread->getOption('page_size') == 'letter' ? 'checked' : '') ?>><label for="letter">Letter&nbsp;&nbsp;&nbsp;</label>
                        <input class="mlg" id="legal" type="radio" name="page_size" value="legal" <?php echo ($this->bread->getOption('page_size') == 'legal' ? 'checked' : '') ?>><label for="legal">Legal&nbsp;&nbsp;&nbsp;</label>
                        <input class="mlg" id="ledger" type="radio" name="page_size" value="ledger" <?php echo ($this->bread->getOption('page_size') == 'ledger' ? 'checked' : '') ?>><label for="ledger">Ledger&nbsp;&nbsp;&nbsp;</label>
                        <input class="mlg" id="A4" type="radio" name="page_size" value="A4" <?php echo ($this->bread->getOption('page_size') == 'A4' ? 'checked' : '') ?>><label for="A4">A4&nbsp;&nbsp;&nbsp;</label>
                        <input class="mlg booklet" id="A5" type="radio" name="page_size" value="A5" <?php echo ($this->bread->getOption('page_size') == 'A5' ? 'checked' : '') ?>><label for="A5" class="booklet">A5&nbsp;&nbsp;&nbsp;</label>
                        <input class="mlg booklet A6" id="A6" type="radio" name="page_size" value="A6" <?php echo ($this->bread->getOption('page_size') == 'A6' ? 'checked' : '') ?>><label for="A6" class="booklet A6">A6&nbsp;&nbsp;&nbsp;</label>
                        <div id="marginsdiv" style="border-top: 1px solid #EEE;">
                            Page Margin Top: <input min="0" max="20" step="1" size="3" maxlength="3" type="number" class="bmlt-input-field" style="display:inline;" id="margin_top" name="margin_top" value="<?php echo esc_attr($this->bread->getOptionForDisplay('margin_top', '3')); ?>" />&nbsp;&nbsp;&nbsp;
                            Bottom: <input min="0" max="20" step="1" size="3" maxlength="3" type="number" class="bmlt-input-field" style="display:inline;" id="margin_bottom" name="margin_bottom" value="<?php echo esc_attr($this->bread->getOptionForDisplay('margin_bottom', '3')); ?>" />&nbsp;&nbsp;&nbsp;
                            Left: <input min="0" max="20" step="1" size="3" maxlength="3" type="number" class="bmlt-input-field" style="display:inline;" id="margin_left" name="margin_left" value="<?php echo esc_attr($this->bread->getOptionForDisplay('margin_left', '3')); ?>" />&nbsp;&nbsp;&nbsp;
                            Right: <input min="0" max="20" step="1" size="3" maxlength="3" type="number" class="bmlt-input-field" style="display:inline;" id="margin_right" name="margin_right" value="<?php echo esc_attr($this->bread->getOptionForDisplay('margin_right', '3')); ?>" />&nbsp;&nbsp;&nbsp;
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
                            Describes things on the page other than the contents. Headers, footers, page numbers.
                            <br />What options you see will be dependant on the layout selected.
                        </p>
                    </div>
                </div>
                <h3 class="hndle">Page Decorations<span data-tooltip-content="#pagedeco-tooltip-content" class="my-tooltip"><span class="tooltipster-icon">(?)</span></span></h3>
                <div class="inside">
                    <div id="watermarkandheaderdiv" style="border-top: 1px solid #EEE;" class="single-page">
                        The page header is a title that goes across the entire page above the meetings.<br />
                        <label for="pageheader_fontsize">Font Size: </label><input min="4" max="40" step=".1" size="3" maxlength="3" type="number" class="bmlt-input-field" style="display:inline;" id="pageheader_fontsize" name="pageheader_fontsize" value="<?php echo esc_attr($this->bread->getOption('pageheader_fontsize')); ?>" />
                        <label for="pageheader_textcolor" style="margin-left:10px;">Text Color:</label> <input style="display: inline-block !important; width: 70px; margin-right: 5px;" type='color' class="bmlt_color" id="pageheader_textcolor" name="pageheader_textcolor" value="<?php echo esc_attr($this->bread->getOption('pageheader_textcolor')); ?>" />
                        <label for="pageheader_backgroundcolor" style="margin-left:10px;">Background Color:</label> <input style="display: inline-block !important; width: 70px; margin-right: 5px;" type='color' id="pageheader_backgroundcolor" class="bmlt_color" name="pageheader_backgroundcolor" value="<?php echo esc_attr($this->bread->getOption('pageheader_backgroundcolor')); ?>" />
                        Header Margin Top: <input min="0" max="20" step="1" size="3" maxlength="3" type="number" class="bmlt-input-field" style="display:inline;" id="margin_header" name="margin_header" value="<?php echo esc_attr($this->bread->getOption('margin_header')); ?>" />&nbsp;&nbsp;&nbsp;
                        <br>Header Text: <input size="100" type="text" id="pageheader_content" name="pageheader_content" value="<?php echo esc_attr($this->bread->getOptionForDisplay('pageheader_content', '')); ?>" />&nbsp;&nbsp;&nbsp;
                        <br>Watermark: <input size="100" type="text" id="watermark" name="watermark" autocomplete="off" value="<?php echo esc_url($this->bread->getOptionForDisplay('watermark', '')); ?>" />&nbsp;&nbsp;&nbsp;
                    </div>
                    <div class="myfooter_div booklet">
                        <label for="nonmeeting_footer">Custom Footer: </label>
                        <input name="nonmeeting_footer" type="text" size="50" value="<?php echo esc_attr($this->bread->getOption('nonmeeting_footer')); ?>">
                        <br />
                        <label for="margin_footer">Margin Footer: </label>
                        <input min="0" max="20" step="1" size="3" maxlength="3" type="number" class="bmlt-input-field" style="display:inline;" id="margin_footer" name="margin_footer" value="<?php echo esc_attr($this->bread->getOptionForDisplay('margin_footer', '5')); ?>" />

                    </div>
                    <div id="pagenodiv" style="border-top: 1px solid #EEE;" class="booklet">
                        Page Numbers Font Size: <input min="1" max="20" step="1" size="3" maxlength="3" type="number" class="bmlt-input-field" style="display:inline;" id="pagenumbering_font_size" name="pagenumbering_font_size" value="<?php echo esc_attr($this->bread->getOptionForDisplay('pagenumbering_font_size', '9')); ?>" />
                    </div>
                    <div id="columngapdiv" style="border-top: 1px solid #EEE;" class="single-page">
                        Column Gap Width: <input min="1" max="20" step="1" size="3" maxlength="3" type="number" class="bmlt-input-field" style="display:inline;" id="column_gap" name="column_gap" value="<?php echo esc_attr($this->bread->getOptionForDisplay('column_gap', '5')); ?>" />
                    </div>
                    <div id="columnseparatordiv" style="border-top: 1px solid #EEE;" class="single-page">
                        <input class="mlg" name="column_line" value="0" type="hidden">
                        Separator: <input type="checkbox" name="column_line" value="1" <?php echo ($this->bread->getOption('column_line') == '1' ? 'checked' : '') ?> /></td>
                        <label for="col_color">Color:</label> <input style="display: inline-block !important; width: 70px; margin-right: 5px;" type='color' class="bmlt-color" id="col_color" name="col_color" value="<?php echo esc_html($this->bread->getOptionForDisplay('col_color', '#bfbfbf')); ?>" />
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="postbox-container">
        <div class="meta-box-sortables ui-sortable">
            <div class="postbox">
                <h3 class="hndle">Base Fonts and Colors</h3>
                <div class="inside">
                    <div id="basefontdiv" style="border-top: 1px solid #EEE;">
                        <input class="mlg" name="base_font" value="0" type="hidden">
                        <label for="base_font">Base Font: </label>
                        <select id="base_font" name="base_font">
                            <option value="dejavusanscondensed" <?php echo $this->bread->getOption('base_font') == 'dejavusanscondensed' ? "selected=\"selected\"" : "" ?>>DejaVu Sans Condensed</option>
                            <option value="courier" <?php echo $this->bread->getOption('base_font') == 'courier' ? "selected=\"selected\"" : "" ?>>Courier</option>
                            <option value="times" <?php echo $this->bread->getOption('base_font') == 'times' ? "selected=\"selected\"" : "" ?>>Times</option>
                            <option value="arial" <?php echo $this->bread->getOption('base_font') == 'arial' ? "selected=\"selected\"" : "" ?>>Arial</option>
                        </select>
                        <input class="mlg" name="colorspace" value="0" type="hidden">
                        <label for="colorspace">Color space: </label>
                        <select id="colorspace" name="colorspace">
                            <option value="0" <?php echo $this->bread->getOption('colorspace') == '0' ? "selected=\"selected\"" : "" ?>>Unrestricted</option>
                            <option value="1" <?php echo $this->bread->getOption('colorspace') == '1' ? "selected=\"selected\"" : "" ?>>Greyscale</option>
                            <option value="2" <?php echo $this->bread->getOption('colorspace') == '2' ? "selected=\"selected\"" : "" ?>>RGB</option>
                            <option value="3" <?php echo $this->bread->getOption('colorspace') == '3' ? "selected=\"selected\"" : "" ?>>CMYK</option>
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
                        <p>Enable <strong>PDF Protection</strong>.</p>
                        <p>Encrypts and sets the PDF document permissions for the PDF file.</p>
                        <p>PDF can be opened and printed.
                        <p>Optional Password to allow editing in a PDF editor.
                        <p>Note: PDF is encrypted and cannot be opened in MS Word at all.</p>
                    </div>
                </div>
                <h3 class="hndle">Password Protection<span data-tooltip-content="#pdfpassword-tooltip-content" class="my-tooltip"><span class="tooltipster-icon">(?)</span></span></h3>
                <div class="inside">
                    <div id="includeprotection" style="border-top: 1px solid #EEE;">
                        <input name="include_protection" value="0" type="hidden">
                        <input type="checkbox" name="include_protection" value="1" <?php echo ($this->bread->getOption('include_protection') == '1' ? 'checked' : '') ?>>Enable Protection
                        <div style="overflow: none; height: 0px;background: transparent;" data-description="dummyPanel for Chrome auto-fill issue">
                            <input type="text" style="height:0;width:0; background: transparent; color: transparent;border: none;" data-description="dummyUsername">
                            <input type="password" style="height:0;width:0;background: transparent; color: transparent;border: none;" data-description="dummyPassword">
                        </div>
                        <label for="protection_password">Password: </label>
                        <input class="protection_pass" id="protection_password" type="password" name="protection_password" value="<?php echo esc_attr($this->bread->getOptionForDisplay('protection_password', '')); ?>" />
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
