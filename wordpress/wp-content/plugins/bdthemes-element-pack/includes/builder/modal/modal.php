<div class="bdt-modal-overlay" id="bdthemes-templates-builder-modal" style="display: none">
    <div id="bdthemes-templates-builder-modal-wrapper">
        <div class="bdt-template-modal-header">
            <div class="bdt-modal-logo-wrap">
                <span class="bdt-logo-text">New Template</span>
            </div>
            <div class="bdt-modal-close-button">
                <a href="javascript:void(0)">
                    <i class="eicon-editor-close"></i>
                </a>
            </div>
        </div>
        <div class="bdt-template-modal-main-wrap">
            <div class="bdt-modal-content-wrap">
                <h3 class="bdt-modal-title">Templates Help You <span>Work Efficiently</span>
                </h3>
                <div class="bdt-modal-desc">Use templates to create the
                    different pieces of your site, and reuse them
                    with one click whenever needed.
                </div>
            </div>
            <div class="bdt-modal-form-wrap">
                <form class="bdt-modal-form" method="post">
                    <input type="hidden" name="template_id" value="" class="template_id" />
                    <div class="bdt-form-title">Choose Template Type</div>
                    <label for="template_type">Select the type of template you want to work on</label>
                    <select name="template_type" id="template_type">
                        <option value="">select</option>
                        <?php

                        $templates = \ElementPack\Includes\Builder\Builder_Template_Helper::templateForSelectDropdown();
                        $separator = \ElementPack\Includes\Builder\Builder_Template_Helper::separator();

                        // print_r($separator);


                        // It is single
                        if (count($templates) == 1) {
                            $templateKey = array_key_last($templates);
                            $template    = $templates[$templateKey];
                            foreach ($template as $key => $item) :
                                $selectValue = "{$templateKey}{$separator}{$key}";
                        ?>
                                <option value="<?php echo esc_attr($selectValue) ?>"><?php echo esc_attr($item) ?></option>
                                <?php
                            endforeach;
                        }

                        if (count($templates) > 1) {
                            foreach ($templates as $keys => $items) :
                                $label = ucwords(str_replace(['-', '_'], [' '], $keys));
                                if (is_array($items)) {
                                ?>
                                    <optgroup label="<?php echo wp_kses_post($label); ?>">
                                        <?php
                                        foreach ($items as $key => $item) :
                                            $itemValue = "{$keys}{$separator}{$key}"
                                        ?>
                                            <option value="<?php echo esc_attr($itemValue) ?>"><?php echo esc_attr($item) ?></option>
                                        <?php
                                        endforeach;
                                        ?>
                                    </optgroup>
                        <?php
                                }
                            endforeach;
                        }
                        ?>
                    </select>
                    <label for="fname">Name your template</label>
                    <input type="text" name="template_name" id="template_name" placeholder="Enter template name">
                    <select name="template_status" id="template_status">
                        <option value="">select</option>
                        <option value="1">Active</option>
                        <option value="0">Inactive</option>
                    </select>
                    <input class="bdt-modal-submit-btn" type="submit" value="Create Template">
                </form>
            </div>
        </div>
    </div>
</div>
</div>

<style>
    .input-error {
        border: 1px solid red !important;
    }
</style>