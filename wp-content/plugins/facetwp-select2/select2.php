<?php

class FacetWP_Facet_Select2
{

    function __construct() {
        $this->label = __( 'Select2', 'fwp' );

        add_filter( 'facetwp_store_unfiltered_post_ids', array( $this, 'store_unfiltered_post_ids' ) );
    }


    /**
     * Load the available choices
     */
    function load_values( $params ) {

        // Inherit load_values() from the dropdown facet type
        return FWP()->helper->facet_types['dropdown']->load_values( $params );
    }


    /**
     * Generate the facet HTML
     */
    function render( $params ) {

        $output = '';
        $facet = $params['facet'];
        $values = (array) $params['values'];
        $selected_values = (array) $params['selected_values'];

        if ( isset( $facet['hierarchical'] ) && 'yes' == $facet['hierarchical'] ) {
            $values = FWP()->helper->sort_taxonomy_values( $params['values'], $facet['orderby'] );
        }

        $multiple = '';
        if ( isset( $facet['multiple'] ) && 'yes' == $facet['multiple'] ) {
            $multiple = ' multiple';
        }

        $label_any = empty( $facet['label_any'] ) ? __( 'Any', 'fwp' ) : $facet['label_any'];
        $label_any = facetwp_i18n( $label_any );

        $output .= '<select class="facetwp-select2"' . $multiple . ' placeholder="' . esc_attr( $label_any ) . '">';

        if ( empty( $multiple ) ) {
            $output .= '<option value="">' . esc_attr( $label_any ) . '</option>';
        }

        foreach ( $values as $result ) {
            $selected = in_array( $result['facet_value'], $selected_values ) ? ' selected' : '';

            $display_value = '';
            for ( $i = 0; $i < (int) $result['depth']; $i++ ) {
                $display_value .= '&nbsp;&nbsp;';
            }

            // Determine whether to show counts
            $display_value .= $result['facet_display_value'];
            $show_counts = apply_filters( 'facetwp_facet_select2_show_counts', true );
/*
            if ( $show_counts=='no' ) {
                $display_value .= ' (' . $result['counter'] . ')';
            }
*/

            $output .= '<option value="' . $result['facet_value'] . '"' . $selected . '>' . $display_value . '</option>';
        }

        $output .= '</select>';
        return $output;
    }


    /**
     * Filter the query based on selected values
     */
    function filter_posts( $params ) {

        // Inherit filter_posts() from the dropdown facet type
        $params['facet']['operator'] = 'and';
        return FWP()->helper->facet_types['checkboxes']->filter_posts( $params );
    }


    /**
     * Output any admin scripts
     */
    function admin_scripts() {
?>
<script>
(function($) {
    wp.hooks.addAction('facetwp/load/select2', function($this, obj) {
        $this.find('.facet-source').val(obj.source);
        $this.find('.facet-label-any').val(obj.label_any);
        $this.find('.facet-multiple').val(obj.multiple);
        $this.find('.facet-parent-term').val(obj.parent_term);
        $this.find('.facet-orderby').val(obj.orderby);
        $this.find('.facet-hierarchical').val(obj.hierarchical);
        $this.find('.facet-count').val(obj.count);
    });

    wp.hooks.addFilter('facetwp/save/select2', function($this, obj) {
        obj['source'] = $this.find('.facet-source').val();
        obj['label_any'] = $this.find('.facet-label-any').val();
        obj['multiple'] = $this.find('.facet-multiple').val();
        obj['parent_term'] = $this.find('.facet-parent-term').val();
        obj['orderby'] = $this.find('.facet-orderby').val();
        obj['hierarchical'] = $this.find('.facet-hierarchical').val();
        obj['count'] = $this.find('.facet-count').val();
        return obj;
    });
})(jQuery);
</script>
<?php
    }


    /**
     * Output any front-end scripts
     */
    function front_scripts() {
?>
<script>
(function($) {
    wp.hooks.addAction('facetwp/refresh/select2', function($this, facet_name) {
        var val = $this.find('.facetwp-select2').val();
        if (val) {
            val = $.isArray(val) ? val : [val];
        }
        else {
            val = [];
        }
        FWP.facets[facet_name] = val;
    });

    wp.hooks.addFilter('facetwp/selections/select2', function(output, params) {
        var labels = [];
        $.each(params.selected_values, function(idx, val) {
            var label = params.el.find('.facetwp-select2 option[value="' + val + '"]').text();
            labels.push(label);
        });
        return labels.join(' / ');
    });

    wp.hooks.addAction('facetwp/ready', function() {
        $(document).on('change', '.facetwp-select2', function() {
            var $facet = $(this).closest('.facetwp-facet');
            if ('' != $facet.find(':selected').val()) {
                FWP.static_facet = $facet.attr('data-name');
            }
            FWP.autoload();
        });
    });

    $(document).on('facetwp-loaded', function() {
        $('.facetwp-select2').each(function() {
            var $this = $(this);

            $this.select2({
                width: '100%',
                theme: 'filter',
                minimumInputLength: 1,
                allowClear: true,
                placeholder: $this.attr('placeholder'),
                language: {
		            noResults: function() {return "No works by this artist"; },
		            inputTooShort: function(args) {
				      // args.minimum is the minimum required length
				      // args.input is the user-typed text
				      return 'Start typing an artists name';
				    }
		        },
		       // current: 'bob',
            });
        });
    });
})(jQuery);
</script>
<?php
    }


    /**
     * Output admin settings HTML
     */
    function settings_html() {
?>
        <tr>
            <td>
                <?php _e( 'Default label', 'fwp' ); ?>:
                <div class="facetwp-tooltip">
                    <span class="icon-question">?</span>
                    <div class="facetwp-tooltip-content">
                        Customize the first option label (default: "Any")
                    </div>
                </div>
            </td>
            <td>
                <input type="text" class="facet-label-any" value="<?php _e( 'Any', 'fwp' ); ?>" />
            </td>
        </tr>
        <tr>
            <td>
                <?php _e( 'Multi-select?', 'fwp' ); ?>:
            </td>
            <td>
                <select class="facet-multiple">
                    <option value="no"><?php _e( 'No', 'fwp' ); ?></option>
                    <option value="yes"><?php _e( 'Yes', 'fwp' ); ?></option>
                </select>
            </td>
        </tr>
        <tr>
            <td>
                <?php _e( 'Parent term', 'fwp' ); ?>:
                <div class="facetwp-tooltip">
                    <span class="icon-question">?</span>
                    <div class="facetwp-tooltip-content">
                        If <strong>Data source</strong> is a taxonomy, enter the
                        parent term's ID if you want to show child terms.
                        Otherwise, leave blank.
                    </div>
                </div>
            </td>
            <td>
                <input type="text" class="facet-parent-term" value="" />
            </td>
        </tr>
        <tr>
            <td><?php _e( 'Sort by', 'fwp' ); ?>:</td>
            <td>
                <select class="facet-orderby">
                    <option value="count"><?php _e( 'Facet Count', 'fwp' ); ?></option>
                    <option value="display_value"><?php _e( 'Display Value', 'fwp' ); ?></option>
                    <option value="raw_value"><?php _e( 'Raw Value', 'fwp' ); ?></option>
                </select>
            </td>
        </tr>
        <tr>
            <td>
                <?php _e( 'Hierarchical', 'fwp' ); ?>:
                <div class="facetwp-tooltip">
                    <span class="icon-question">?</span>
                    <div class="facetwp-tooltip-content"><?php _e( 'Is this a hierarchical taxonomy?', 'fwp' ); ?></div>
                </div>
            </td>
            <td>
                <select class="facet-hierarchical">
                    <option value="no"><?php _e( 'No', 'fwp' ); ?></option>
                    <option value="yes"><?php _e( 'Yes', 'fwp' ); ?></option>
                </select>
            </td>
        </tr>
        <tr>
            <td>
                <?php _e( 'Count', 'fwp' ); ?>:
                <div class="facetwp-tooltip">
                    <span class="icon-question">?</span>
                    <div class="facetwp-tooltip-content"><?php _e( 'The maximum number of facet choices to show', 'fwp' ); ?></div>
                </div>
            </td>
            <td><input type="text" class="facet-count" value="10" /></td>
        </tr>
<?php
    }


    /**
     * Store unfiltered post IDs if a select2 facet exists
     */
    function store_unfiltered_post_ids( $boolean ) {
        if ( FWP()->helper->facet_setting_exists( 'type', 'select2' ) ) {
            return true;
        }

        return $boolean;
    }
}
