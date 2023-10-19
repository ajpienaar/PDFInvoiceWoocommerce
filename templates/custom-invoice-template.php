<?php
/*
Template Name: Custom PDF Invoice Template
*/
//includes order and custom field data to populate the template
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Invoice</title>
</head>
<body>
    <!--logo populated from custom logo URL field and adjusted to fit the template-->
    <div style="text-align: center;">
        <img class="logo-img" src="<?php echo esc_attr(get_option('custom_pdf_logo')); ?>" alt="Company Logo" width="164" height="40">
    </div>
    <div style="margin-top: 100px;"></div>
    <table style="width: 100%;">
        <tr>
            <td style="vertical-align: top; width: 50%;">
                <table class="order-details" style="font-size: 10px;width: 100%;">
                    <!-- Populate from the custom fields and WooCommerce store details -->
                    <tr>
                        <td>
                            <p><?php echo esc_attr(get_option('custom_pdf_lines1')); ?></p>
                            <p><?php echo esc_html(get_option('woocommerce_store_address')); ?></p>
                            <p><?php echo esc_html(get_option('woocommerce_store_city')); ?></p>
                            <p><?php echo esc_html(get_option('woocommerce_store_postcode')); ?></p>
                            <p><?php echo esc_attr(get_option('custom_pdf_lines2')); ?></p>
                        </td>
                    </tr>
                </table>
            </td>
            <td style="text-align: center; width: 50%;">
                <table class="qr-details" style="width: 100%;">
                    <tr>
                        <td>
                            <div style="margin-top: 25px;"></div>
                            <img class="qr-img" src="<?php echo esc_url($barcode_image_path); ?>" alt="QR Code" width="70" height="70">
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
    <div style="margin-top: 50px;"></div>
    <table style="width: 100%;">
        <tr>
            <td style="vertical-align: top; width: 50%;">
                <div style="margin-top: 50px;"></div>
                <table class="invoice-details" style="width: 100%;">
                    <tr>
                        <!--use the $order variable passed from tcpdf to populate the order number-->
                        <div style="margin-top: 100px;"></div>
                        <th><h2>Invoice# <?php echo esc_html($order->get_id()); ?></h2>
                        </th>
                    </tr>
                    <tr>
                        <td>
                            <hr>
                            <!--populated from the woocommerce order details-->
                                <p style="font-size: 11px;"><?php echo htmlspecialchars($order->get_formatted_billing_full_name()); ?></p>
                                <?php $billing_address = $order->get_address('billing');?>
                                <?php if (!empty($billing_address)) : ?>
                                <p style="font-size: 11px;"><?php echo htmlspecialchars($billing_address['company']); ?></p>
                                <p style="font-size: 11px;"><?php echo htmlspecialchars($billing_address['address_1']); ?></p>
                                <p style="font-size: 11px;"><?php echo htmlspecialchars($billing_address['city']); ?></p>
                                <p style="font-size: 11px;"><?php echo htmlspecialchars($billing_address['postcode']); ?></p>
                                <?php else : ?>
                                <p>No billing address available.</p>
                                <?php endif; ?>
                        </td>
                    </tr>
                </table>
            </td>
            <td style="vertical-align: top; width: 50%;">
                <div style="margin-top: 50px;"></div>
                <table class="subscription-details" style="width: 100%;">
                    <tr>
                        <!--includes the payment status-->
                        <?php $order_status = $order->get_status();?>
                        <?php $payment_status = ($order_status === 'completed') ? 'Paid' : 'Pending';?>
                        <div style="margin-top: 100px;"></div>
                        <th><h2>Order: <span style="color: green;"><?php echo $payment_status; ?></span></h2>
                        </th>
                    </tr>
                    <tr>
                        <td>
                            <hr>
                            <?php $payment_method = $order->get_payment_method();
                            $order_date = $order->get_date_created();
                            $payment_date = $order_date->date_i18n('F j, Y @ H:i');?>
                            <p style="font-size: 11px;"><strong>Payment via:</strong> <?php if ($payment_method === 'credit_card') {
                                echo 'Credit Card';
                            } elseif ($payment_method === 'paypal') {
                                echo 'PayPal';
                            } elseif ($payment_method === 'bacs') {
                                echo 'Direct Bank Transfer';
                            } elseif ($payment_method === 'cod') {
                                echo 'Cash on Delivery';
                            } else {
                                echo 'Other';
                            }?></p>
                            <p style="font-size: 11px;"><strong>Payment on:</strong> <?php echo $payment_date ?></p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
    <div style="margin-top: 50px;"></div>
    <article>
        <h2>Order Details</h2>
        <hr>
        <div style="margin-top: 2px;"></div>
        <table class="invoice" style="font-size: 10px;border: 0.5px solid #000; border-collapse: collapse; padding: 7px;">
            <thead>
                <tr>
                    <th style="background-color: lightgray; padding: 5px; font-weight: bold; border: 0.5px solid #000;"><span>Item</span></th>
                    <th style="background-color: lightgray; padding: 5px; font-weight: bold; border: 0.5px solid #000;"><span>Cost</span></th>
                    <th style="background-color: lightgray; padding: 5px; font-weight: bold; border: 0.5px solid #000;"><span>Qty</span></th>
                    <th style="background-color: lightgray; padding: 5px; font-weight: bold; border: 0.5px solid #000;"><span>Total</span></th>
                </tr>
            </thead>
            <tbody>
                <!--iterate through the woocommerce order items and list them in teh table-->
            <?php if (!empty($order->get_items())) : ?>
                <?php foreach ($order->get_items() as $item_id => $item) : ?>
                    <?php
                    $product = $item->get_product();
                    $item_name = $item->get_name();
                    $item_price = wc_price($product->get_price());
                    $item_quantity = $item->get_quantity();
                    $item_total = wc_price($item->get_total());
                    ?>
                    <tr>
                        <td style="border: 0.5px solid #000;"><?php echo htmlspecialchars($item_name); ?></td>
                        <td style="border: 0.5px solid #000;"><?php echo $item_price; ?></td>
                        <td style="border: 0.5px solid #000;"><?php echo $item_quantity; ?></td>
                        <td style="border: 0.5px solid #000;"><?php echo $item_total; ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
        <!--displays the shipping fields and additional fields tables if appropriate-->
        <?php if (!empty($order->get_fees()) || !empty($order->get_shipping_methods())) : ?>
            <div style="margin-top: 50px;"></div>
            <h2>Additional Fees</h2>
            <hr>
            <div style="margin-top: 2px;"></div>
            <table class="fees-shipping" style="font-size: 10px;border: 1px solid #000; border-collapse: collapse; padding: 7px;">
                <thead>
                    <tr>
                        <th style="background-color: lightgray; padding: 5px; font-weight: bold; border: 0.5px solid #000;"><span>Item</span></th>
                        <th style="background-color: lightgray; padding: 5px; font-weight: bold; border: 0.5px solid #000;"><span>Cost</span></th>
                        <th style="background-color: lightgray; padding: 5px; font-weight: bold; border: 0.5px solid #000;"><span>Total</span></th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Get additional fees from WooCommerce -->
                    <?php $fees = $order->get_fees(); ?>
                    <?php if (!empty($fees)) : ?>
                        <!--iterate through the fees and list them in the table-->
                        <?php foreach ($fees as $fee) : ?>
                            <tr>
                                <td style="border: 0.5px solid #000;"><?php echo htmlspecialchars($fee['name']); ?></td>
                                <td style="border: 0.5px solid #000;"><?php echo wc_price($fee['total']); ?></td>
                                <td style="border: 0.5px solid #000;"><?php echo wc_price($fee['total']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    <!-- Get shipping method info from WooCommerce for the order -->
                <?php $shipping_methods = $order->get_shipping_methods();?>
                <?php if (!empty($shipping_methods)) : ?>
                    <!--iterate through the shipping methods applicable-->
                    <?php foreach ($shipping_methods as $shipping_method) : ?>
                        <tr>
                            <td style="border: 0.5px solid #000;"><?php echo htmlspecialchars($shipping_method['name']); ?></td>
                            <td style="border: 0.5px solid #000;"><?php echo wc_price($shipping_method['cost']); ?></td>
                            <td style="border: 0.5px solid #000;"><?php echo wc_price($shipping_method['cost']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        <?php endif; ?>
        <!--display total fees and shipping gets the data from woocommerce and only listed if applicable-->
        <div style="text-align: right;">
            <p style="font-size: 11px;"><strong>Items Subtotal:</strong> <?php echo wc_price($order->get_subtotal()); ?></p>
            <?php if (!empty($fees)) : ?>
            <p style="font-size: 11px;"><strong>Fees:</strong> <?php echo wc_price($fee['total']); ?></p>
            <?php endif; ?>
            <?php if (!empty($order->get_shipping_methods())) : ?>
            <p style="font-size: 11px;"><strong>Shipping:</strong> <?php echo wc_price($shipping_method['cost']); ?></p>
            <?php endif; ?>
            <div style="margin-bottom: 2px;text-align: center;">
                <hr style="width: 50%;">
            </div>
            <h3 style="padding-top: 0px;font-weight: bold;">Order Total: <?php echo wc_price($order->get_total()); ?></h3>
        </div>
    </article>
</body>
</html>