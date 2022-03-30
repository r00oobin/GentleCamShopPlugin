<?php
/**
 * Plugin Name: WooCommerce GentleCam Plugin
 * Plugin URI: https://bfklimited.com
 * Version: 1.0
 * Author: Robin Furrer
 * Author URI: mail(bfklimited@gmail.com)
 */

add_action('woocommerce_thankyou', 'sendBasket', 10, 1);


function sendBasket($order_id)
{
    if (!$order_id)
        return;
    // Allow code execution only once
    if (!get_post_meta($order_id, '_thankyou_action_done', true)) {
        addCoinsToUser($order_id);
    }
}

function addCoinsToUser($order_id) {
    $order = new WC_Order( $order_id );
    // Get and Loop Over Order Items
    $coins = [];
    $user_info = get_userdata($order->user_id);
    $username = $user_info->user_login;
    foreach ( $order->get_items() as $item_id => $item ) {
        $product = $item->get_product();
        $coins[] = $product->get_attribute( 'coins' );
        addCoinsDB($username, $product->get_attribute( 'coins' ));
    }

    $order->update_meta_data('_thankyou_action_done', true);
    $order->save();
}

function addCoinsDB($username, $coins) {

    $host = "127.0.0.1";


    $pass = "";

    $conn = new \mysqli($host, $user, $pass, 'gentlecam_db');

    $sql = "SELECT * FROM user WHERE username = '$username'";
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        // output data of each row
        while($row = $result->fetch_assoc()) {
            $stmt = $conn->prepare("UPDATE user SET coins=? WHERE username=?");
            $stmt->bind_param("is", $totalCoins, $username);
            $totalCoins = $coins + $row['coins'];
            $stmt->execute();
        }
    }
    $conn->close();
}