<?php
class Custom_PDF_Settings_Tab {
    
    // class constructor for settings tab
    public function __construct() {
        $this->id = 'custom_pdf_settings';//sets the id property
        $this->label = __('PDF Invoice');//It sets the label property
        
        //Callback function to be called when this filter is applied, and 50 is the priority of the tab.
        add_filter('woocommerce_settings_tabs_array', array($this, 'add_settings_tab'), 50);
        add_action('woocommerce_settings_' . $this->id, array($this, 'output_settings'));
        add_action('woocommerce_settings_save_' . $this->id, array($this, 'save_settings'));

        // Register settings and callbacks for saving
        add_action('admin_init', array($this, 'register_settings'));
    }
    //adds the custom tab to the list of tabs that appear on the WooCommerce settings page.
    public function add_settings_tab($settings_tabs) {
        $settings_tabs[$this->id] = $this->label;
        return $settings_tabs;
    }
    //function that outputs the pdf settings field in the tab renderign the appropriate fields
    public function output_settings() {
        ?>
        <h2><?php echo esc_html($this->label); ?></h2>
        <table class="form-table">
            <p class="description">The "Custom PDF Settings" plugin allows Woocommerce store owners to tailor the appearance of their PDF invoices. Customize invoice appearance such as the logo, additional lines of text, and footer content here.</p>
            <tr valign="top">
                <th scope="row">Logo URL:</th>
                <!-- renders the logo field using the logo field function -->
                <td><?php $this->render_logo_field(); ?></td>
            </tr>
            <tr valign="top">
                <th scope="row">Additional Lines 1:</th>
                <!-- renders the additional lines field using the additional lines function -->
                <td><?php $this->render_lines_field1(); ?></td>
            </tr>
            <tr valign="top">
                <th scope="row">Additional Lines 2:</th>
                <!-- renders the additional lines field using the additional lines function -->
                <td><?php $this->render_lines_field2(); ?></td>
            </tr>
            <tr valign="top">
                <th scope="row">Footer Text:</th>
                <!-- renders the footer text field using the footer function -->
                <td><?php $this->render_footer_text_field(); ?></td>
            </tr>
        </table>
        <?php
    }

    public function save_settings() {
        if (isset($_POST['save'])) {
            // Save the settings to the WP options database
            update_option('custom_pdf_logo', sanitize_text_field($_POST['custom_pdf_logo']));
            update_option('custom_pdf_lines1', sanitize_text_field($_POST['custom_pdf_lines1']));
            update_option('custom_pdf_lines2', sanitize_text_field($_POST['custom_pdf_lines2']));
            update_option('custom_pdf_footer_text', sanitize_text_field($_POST['custom_pdf_footer_text']));
        }
    }

    public function register_settings() {
        // Register the settings section that groups related settings together.
        add_settings_section(
            'custom_pdf_section',
            'Custom PDF Settings',
            '__return_false',
            'woocommerce'
        );

        // Register the Logo URL field within the settings section
        add_settings_field(
            'custom_pdf_logo',
            'Logo URL',
            array($this, 'render_logo_field'),
            'woocommerce',
            'custom_pdf_section'
        );

        // Register the Additional Lines field 1 within the settings section
        add_settings_field(
            'custom_pdf_lines1',
            'Additional Lines',
            array($this, 'render_lines_field1'),
            'woocommerce',
            'custom_pdf_section'
        );

        // Register the Additional Lines field 2 within the settings section
        add_settings_field(
            'custom_pdf_lines2',
            'Additional Lines',
            array($this, 'render_lines_field2'),
            'woocommerce',
            'custom_pdf_section'
        );

        // Register the Footer Text field within the settings section
        add_settings_field(
            'custom_pdf_footer_text',
            'Footer Text',
            array($this, 'render_footer_text_field'),
            'woocommerce',
            'custom_pdf_section'
        );
        //define the structure and behavior of the custom settings section ensuring the data is saved and sanitized correctly
        register_setting('woocommerce', 'custom_pdf_logo', 'esc_url_raw');
        register_setting('woocommerce', 'custom_pdf_lines1', 'sanitize_text_field');
        register_setting('woocommerce', 'custom_pdf_lines2', 'sanitize_text_field');
        register_setting('woocommerce', 'custom_pdf_footer_text', 'sanitize_text_field');
    }
    //function to render the fields
    public function render_logo_field() {
        $custom_logo = esc_attr(get_option('custom_pdf_logo'));
        echo '<input type="text" name="custom_pdf_logo" value="' . esc_attr($custom_logo) . '" />';
        echo '<p class="description">Add a link to your logo here (recommended 470 by 110 pixels)</p>';
        
    }
    //function to render the fields
    public function render_lines_field1() {
        $custom_lines1 = esc_attr(get_option('custom_pdf_lines1'));
        echo '<input type="text" name="custom_pdf_lines1" value="' . esc_attr($custom_lines1) . '" />';
        echo '<p class="description">Additional text displayed above the store address field.</p>';
    }
    //function to render the fields
    public function render_lines_field2() {
        $custom_lines2 = esc_attr(get_option('custom_pdf_lines2'));
        echo '<input type="text" name="custom_pdf_lines2" value="' . esc_attr($custom_lines2) . '" />';
        echo '<p class="description">Additional text displayed below the store address field.</p>';
    }

    //function to render the fields
    public function render_footer_text_field() {
        $custom_footer_text = esc_attr(get_option('custom_pdf_footer_text'));
        echo '<input type="text" name="custom_pdf_footer_text" value="' . esc_attr($custom_footer_text) . '" />';
        echo '<p class="description">Invoice footer text.</p>';
    }
}
//call the function to render it all
new Custom_PDF_Settings_Tab();