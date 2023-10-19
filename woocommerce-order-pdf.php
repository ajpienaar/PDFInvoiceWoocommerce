<?php
/*
Plugin Name: PDF Invoice for Woocommerce
Description: This is a lightweight WooCommerce extension that creates a "Download Invoice" button in the customers account page in the "orders list" section and generates a basic customizable PDF invoice that is automatically attached to the "order complete" emails sent to customers. In addition it generates a dynamic QR Code that is included on the invoice containing basic order information. 
Author: Jacques
Version: 1.0
*/
    // Load the tcpdf library and dependencies
    require_once plugin_dir_path(__FILE__) . 'vendor/tcpdf/tcpdf.php';
    require_once plugin_dir_path(__FILE__) . 'vendor/phpqrcode/phpqrcode.php';
    require_once plugin_dir_path(__FILE__) . 'settings-page.php';
    
    // Hook into order creation, account and email actions and filters
    add_action('woocommerce_order_status_completed', 'generate_pdf_invoice', 10, 1);
    add_filter('woocommerce_my_account_my_orders_actions', 'add_custom_download_link_to_account', 10, 2);
    add_filter('woocommerce_email_attachments', 'attach_pdf_to_completed_order_email', 10, 3);

    function generate_pdf_invoice($order_id) {
    // Create a new PDF instance and set other PDF-related settings
    $pdf = new CustomTCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

    // Set document information (replace with your own details)
    $pdf->SetCreator(PDF_CREATOR);
    $pdf->SetTitle('Invoice: ' . $order_id);
    $pdf->SetSubject('Order Invoice for: ' . $order_id);
    $pdf->SetKeywords('Invoice, Order');

    // Set default header and footer data (you can customize this)
    $pdf->SetHeaderData('', 0, '', '', array(0, 0, 0), array(0, 0, 0));
    $pdf->setFooterData(array(0, 0, 0), array(0, 0, 0));

    // Set default monospaced font
    $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

    // Disable header and footer lines
    $pdf->SetPrintHeader(false);
    $pdf->SetPrintFooter(true);

    // Set margins
    $pdf->SetMargins(10, 5, 10);

    // Sets the cell height
    $pdf->setCellHeightRatio(0.8);

    // Add a page
    $pdf->AddPage();
    
    // Pass the order data to the template
    $order = wc_get_order($order_id);

    // Create an array to store the barcode data 
    $orderData = array(
        'billing_name' => $order->get_formatted_billing_full_name(),
        'invoice_number'=> $order_id,
        'order_status' => $order->get_status(),
        'order_date' => $order->get_date_created()->format('Y-m-d H:i:s'),
        'order_total' => $order->get_total(),
        'payment_method' => $order->get_payment_method_title()
    );
    //encode order data to json    
    $barcodeText= json_encode($orderData);
    
    // Get the WordPress upload directory
    $upload_dir = wp_upload_dir();

    // Specify the directory where the PDF is to be saved
    $pdf_directory = $upload_dir['basedir'] . '/invoices/';

    // Create the directory if it doesn't exist
    if (!file_exists($pdf_directory)) {
        mkdir($pdf_directory, 0755, true);
    }

    // Generate a unique filename for the barcode image
    $barcode_filename = 'barcode_' . $order_id . '.png';
    
    // Combine directory and filename to create the full path for the barcode image
    $barcode_image_path = $pdf_directory . $barcode_filename;
    
    // Generate the QR code using TCPDF
    QRcode::png($barcodeText, $barcode_image_path, 'M', 2.5, 2); 
    
    // Include the custom invoice template
    ob_start();

    // Get the root directory of WordPress
    $wp_root = ABSPATH;

    // Get the path to the plugin's root directory
    $plugin_root = plugin_dir_path(__FILE__);

    // Define the relative path to the template file within the plugin
    $template_relative_path = 'templates/custom-invoice-template.php';

    // Construct the absolute path to the template file
    $template_absolute_path = $plugin_root . $template_relative_path;

    // Include the template file
    include $template_absolute_path;

    // Get the content of the included template
    $invoice_template = ob_get_clean();

    // Generate the PDF content with the template and barcode
    $pdf->writeHTML($invoice_template, true, false, false, false, '');

    // Output the PDF content to a variable
    $pdf_content = $pdf->Output('', 'S');

    // Generate a unique filename for the PDF
    $pdf_filename = 'invoice_' . $order_id . '.pdf';

    // Combine directory and filename to create the full path for the PDF
    $pdf_filepath = $pdf_directory . $pdf_filename;

    // Save the PDF to the specified directory
    $saved = file_put_contents($pdf_filepath, $pdf_content);
}

    // Add the "Download Invoice" button to the order actions list
    function add_custom_download_link_to_account($actions, $order) {
    // Check if a corresponding PDF invoice exists
    $pdf_filename = 'invoice_' . $order->get_id() . '.pdf';
    $pdf_filepath = wp_upload_dir()['basedir'] . '/invoices/' . $pdf_filename;

    if (file_exists($pdf_filepath)) {
        // Add the download link to the actions list only if there is a file associated
        $actions['download_invoice'] = array(
            'url'  => esc_url(site_url('/wp-content/uploads/invoices/' . $pdf_filename)),
            'name' => __('Download Invoice', 'text-domain'),
        );
    }

    return $actions;
}

    // Attach the PDF to the completed order email that is sent by woocommerce
    function attach_pdf_to_completed_order_email($attachments, $email_id, $order) {
    if ($email_id == 'customer_completed_order') {
        $pdf_filename = 'invoice_' . $order->get_id() . '.pdf';
        $pdf_filepath = wp_upload_dir()['basedir'] . '/invoices/' . $pdf_filename;

        if (file_exists($pdf_filepath)) {
            $attachments[] = $pdf_filepath;
        }
    }

    return $attachments;
}

    class CustomTCPDF extends TCPDF {

    public function __construct() {
        parent::__construct();
    }

    public function Footer() {
        // Set the position of the footer (adjust the values as needed)
        $this->SetY(-15);

        // Set the font for the footer text (adjust as needed)
        $this->SetFont('dejavusans', '', 8);

        // Retrieve the footer text from the database using get_option
        $currentYear = date('Y') . ' ';
        $footerText = $currentYear. get_option('custom_pdf_footer_text');

        // Output the footer text
        $this->Cell(0, 10, $footerText, 0, false, 'C', 0, '', 0, false, 'T', 'M');
    }
}
?>