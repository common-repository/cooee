<?php

/**
 * Description: Engage with each user uniquely for retention & revenue boost, powered by AI
 *
 * @package letscooee
 * @version 0.0.1
 * @file
 * Description: Engage with each user uniquely for retention & revenue boost, powered by AI
 */

/*
 * Plugin Name:Cooee
 * Plugin URI: www.letscooee.com
 * Description: Engage with each user uniquely for retention & revenue boost, powered by AI
 * Version: 0.0.1
 * Author: Cooee
 * Author URI: https://www.linkedin.com/company/letscooee/mycompany/
 * Licence: GPLv2
 * Test Domain: letscooee
 */

if (in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {
    if (!class_exists('Cooee')) {
        /*
         * Localisation
         */
        load_plugin_textdomain('cooee', false, dirname(plugin_basename(__FILE__)) . '/');

        class Cooee {
            public function __construct() {
                add_action('admin_init', [&$this, 'cooee_init']);
                add_action('admin_menu', [&$this, 'cooee_menu']);
                add_action('wp_head', [&$this, 'initilizeWebSDK']);
                /*
                    add_action('woocommerce_add_to_cart', array(&$this, 'send_add_to_cart_event'), 10, 6);
                    add_action('woocommerce_remove_cart_item', array(&$this, 'send_remove_from_cart_event'), 10, 2);
                    add_action('woocommerce_after_single_product_summary', array(&$this, 'send_view_item_event'));
                    add_action('woocommerce_thankyou', array(&$this, 'send_purchase_event'), 10, 1);
                    add_action('woocommerce_before_checkout_billing_form', array(&$this, 'begin_checkout'), 10, 1);
                    // region TODO Need Testing
                    add_action('user_register', array(&$this, 'register_user'), 10, 2);
                    add_action('profile_update', array($this, 'update_profile'), 10, 3);
                    add_action('wp_login', array(&$this, 'user_log_in'), 10, 2);
                add_action('wp_logout', array(&$this, 'user_log_out'), 10, 1);*/
                // endregion

            } //end __construct()


            public function cooee_init() {
                register_setting('cooee-settings-group', 'cooee-app-id');
                add_settings_section('connect-account', 'Connect Account', '', 'cooee-plugin');
                add_settings_field(
                    'cooee-app-id',
                    'Enter Cooee App Id:',
                    [
                        &$this,
                        'app_id_callback',
                    ],
                    'cooee-plugin',
                    'connect-account'
                );
            } //end cooee_init()


            public function app_id_callback() {
                $cooee_app_id = esc_attr(get_option('cooee-app-id'));
                echo "<input type='text' name='cooee-app-id' value='' />";
            } //end app_id_callback()


            public function cooee_menu() {
                add_options_page(
                    'Cooee',
                    'Cooee',
                    'manage_options',
                    'cooee-plugin',
                    [
                        &$this,
                        'cooee_options_page',
                    ]
                );
            } //end cooee_menu()


            public function cooee_options_page() {
                ?>
                <div class="wrap">
                    <h2>Cooee</h2>
                    <form action="options.php" method="POST">
                        <?php settings_fields('cooee-settings-group'); ?>
                        <?php do_settings_sections('cooee-plugin'); ?>
                        <?php submit_button(); ?>
                    </form>
                </div>
                <?php

            } //end cooee_options_page()


            public function initilizeWebSDK() {
                $cooee_app_id = esc_attr(get_option('cooee-app-id'));
                $path         = 'https://cdn.jsdelivr.net/npm/@letscooee/web-sdk@latest/dist/sdk.min.js';
                if (!empty($cooee_app_id)) {
                    wp_enqueue_script(
                        'sdk.min.js',
                        $path,
                        array(),
                        true
                    );
                    ?>
                    <script>
                        window.CooeeSDK = window.CooeeSDK || {
                            events: [],
                            profile: [],
                            account: []
                        };
                        CooeeSDK.account.push({
                            'appID': "<?php echo esc_attr(get_option('cooee-app-id')); ?>",
                        });
                    </script>
                    <?php
                }
            } //end initilizeWebSDK()


            public function send_add_to_cart_event( $cart_item_key, $product_id, $quantity, $variation_id, $variation, $cart_item_data) {
                $product        = wc_get_product($product_id);
                $product_name   = $product->get_title();
                $categories     = wp_get_post_terms($product_id, 'product_cat');
                $first_category = [];
                if ($categories && !is_wp_error($categories)) {
                    $first_category = array_shift($categories);
                }

                $add_to_cart = [
                    'item' => [
                        'id'       => $product_id,
                        'name'     => $product_name,
                        'category' => [
                            'id'   => $first_category->term_id,
                            'name' => $first_category->name,
                        ],
                    ],
                ];
                ?>
                <script>
                    window.CooeeSDK = window.CooeeSDK || {
                        events: [],
                        profile: [],
                        account: []
                    };
                    CooeeSDK.events.push(['Add To Cart', <?php echo json_encode($add_to_cart); ?>]);
                </script>
                <?php

            } //end send_add_to_cart_event()


            public function send_remove_from_cart_event( $cart_item_key, $cart) {
                $product_id     = $cart->cart_contents[$cart_item_key]['product_id'];
                $product        = wc_get_product($product_id);
                $product_name   = $product->get_title();
                $categories     = wp_get_post_terms($product_id, 'product_cat');
                $first_category = [];
                if ($categories && !is_wp_error($categories)) {
                    $first_category = array_shift($categories);
                }

                $remove_from_cart = [
                    'item' => [
                        'id'       => $product_id,
                        'name'     => $product_name,
                        'category' => [
                            'id'   => $first_category->term_id,
                            'name' => $first_category->name,
                        ],
                    ],
                ];
                exit;
                ?>
                <script>
                    console.log('Sent Remove cart event');
                    window.CooeeSDK = window.CooeeSDK || {
                        events: [],
                        profile: [],
                        account: []
                    };
                    CooeeSDK.events.push(['Remove From Cart', <?php echo json_encode($remove_from_cart); ?>]);
                </script>
                <?php

            } //end send_remove_from_cart_event()


            public function begin_checkout( $cc) {
                echo '<pre>';
                print_r(array_keys($cc));
                echo '</pre>';
                ?>
                <script>
                    CooeeSDK.events.push(['Begin Checkout', {}]);
                </script>
                <?php

            } //end begin_checkout()


            public function send_view_item_event() {
                global $post;
                $post_type      = $post->post_type;
                $product_id     = get_the_ID();
                $product        = new WC_Product($product_id);
                $product_name   = $product->get_title();
                $categories     = wp_get_post_terms($product_id, 'product_cat');
                $first_category = [];

                if ($categories && !is_wp_error($categories)) {
                    $first_category = array_shift($categories);
                }

                $view_item = [
                    'item' => [
                        'id'       => $product_id,
                        'name'     => $product_name,
                        'category' => [
                            'id'   => $first_category->term_id,
                            'name' => $first_category->name,
                        ],
                    ],
                ];

                if ('product' === $post_type) {
                    ?>
                    <script>
                        CooeeSDK.events.push(['View Item', <?php echo json_encode($view_item); ?>]);
                    </script>
                    <?php
                }
            } //end send_view_item_event()


            public function send_purchase_event( $order_id) {
                $order       = new WC_Order($order_id);
                $order_items = $order->get_items();
                $currency    = get_woocommerce_currency();

                $get_all_coupons  = $order->get_coupons();
                $get_first_coupon = array_shift($get_all_coupons);

                $item_ids = array_keys($order_items);
                $items    = [];

                foreach ($item_ids as $item_id) {
                    $product_data   = $order_items[$item_id]->get_data();
                    $product_id     = $product_data['product_id'];
                    $product        = wc_get_product($product_id);
                    $product_name   = $product->get_title();
                    $categories     = wp_get_post_terms($product_id, 'product_cat');
                    $quantity       = $product_data['quantity'];
                    $first_category = [];
                    if ($categories && !is_wp_error($categories)) {
                        $first_category = array_shift($categories);
                    }

                    $item = [
                        'id'       => $product_id,
                        'name'     => $product_name,
                        'category' => [
                            'id'   => $first_category->term_id,
                            'name' => $first_category->name,
                        ],
                        'quantity' => $quantity,
                    ];

                    array_push($items, $item);
                } //end foreach

                $purchase = [
                    'transactionID' => $order->get_transaction_id() ? $order->get_transaction_id() : 'cash on delivery',
                    'amount'        => [
                        'value'    => $order->get_total(),
                        'currency' => $currency,
                    ],
                    'items'         => $items,
                ];

                if ($get_first_coupon) {
                    $purchase['coupon'] = [
                        'id'   => $get_first_coupon->get_id(),
                        'code' => $get_first_coupon->get_code(),
                    ];
                }

                ?>
                <script type="text/javascript">
                    CooeeSDK.events.push(['Purchase', <?php echo json_encode($purchase); ?>]);
                </script>
                <?php

            } //end send_purchase_event()


            public function register_user( $user_id, array $userdata = null) {
                echo 'Hello';
                echo '<script type="text/javascript"> console.log("Check");</script>';

                $user = get_userdata($user_id);

                if ($user) {
                    $user_info = [
                        'name'  => $user->user_firstname . ' ' . $user->user_lastname,
                        'email' => $user->user_email,
                    ]
                    ?>
                    <script type="text/javascript">
                        CooeeSDK.profile.push({
                            <?php echo json_encode($user_info); ?>
                        });
                        CooeeSDK.events.push(['Sign Up', {
                            method: 'Email'
                        }]);
                    </script>
                    <?php
                }
            } //end register_user()


            public function update_profile( $user_id, $old_user_data, $userdata) {
                $user = get_userdata($user_id);

                if ($user) {
                    $user_info = [
                        'name'  => $user->user_firstname . ' ' . $user->user_lastname,
                        'email' => $user->user_email,
                    ]
                    ?>
                    <script type="text/javascript">
                        CooeeSDK.profile.push({
                            <?php echo json_encode($user_info); ?>
                        });
                        CooeeSDK.events.push(['Sign Up', {
                            method: 'Email'
                        }]);
                    </script>
                    <?php
                }
            } //end update_profile()


            public function user_log_in( $user_login, $user) {
                echo 'scsdcs';
                if ($user) {
                    ?>
                    <script type="text/javascript">
                        CooeeSDK.events.push(['Log In', {
                            method: 'Email'
                        }]);
                    </script>
                    <?php
                }
            } //end user_log_in()


            public function user_log_out( $user_id) {
                ?>
                <script type="text/javascript">
                    CooeeSDK.events.push(['Log Out', {}]);
                </script>
                <?php

            } //end user_log_out()

        } //end class

        $GLOBALS['cooee'] = new Cooee();
    } //end if
}//end if
