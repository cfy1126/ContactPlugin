<?php

if (!defined('ABSPATH')) {
      die('You cannot be here');
}

add_shortcode('contact', 'show_contact_form');

add_action('rest_api_init', 'create_rest_endpoint');

add_action('init', 'create_submissions_page');

add_action('add_meta_boxes', 'create_meta_box');

add_filter('manage_submission_posts_columns', 'custom_submission_columns');

add_action('manage_submission_posts_custom_column', 'fill_submission_columns', 10, 2);

add_action('admin_init', 'setup_search');

add_action('wp_enqueue_scripts', 'enqueue_custom_scripts');

function enqueue_custom_scripts()
{

      // Enqueue custom css for plugin

      wp_enqueue_style('contact-form-plugin', MY_PLUGIN_URL . 'assets/css/contact-plugin.css');
}

function setup_search()
{

      // Only apply filter to submissions page

      // typenow 是 WordPress 中的一个全局变量。它用于存储当前正在编辑或查看的文章类型（post type）的标识符
      global $typenow;

      if ($typenow === 'submission') {

            add_filter('posts_search', 'submission_search_override', 10, 2);
      }
}

function submission_search_override($search, $query)
{
      // Override the submissions page search to include custom meta data

      global $wpdb;

      if ($query->is_main_query() && !empty($query->query['s'])) {
            $sql    = "
              or exists (
                  select * from {$wpdb->postmeta} where post_id={$wpdb->posts}.ID
                  and meta_key in ('name','email','phone','message')
                  and meta_value like %s
              )
          ";
            // esc_like() 函数用于对字符串进行转义，以便在 SQL 查询的 LIKE 子句中安全地使用。通过转义 % 和 _ 等特殊字符，可以防止 SQL 注入攻击，确保查询的安全性。  
            $like   = '%' . $wpdb->esc_like($query->query['s']) . '%';
            
            // 使用正则表达式 preg_replace 修改原始搜索查询，将自定义的 SQL 子查询插入到搜索条件中。
            $search = preg_replace(
                  "#\({$wpdb->posts}.post_title LIKE [^)]+\)\K#",
                  $wpdb->prepare($sql, $like),
                  $search
            );
      }

      return $search;
}

function fill_submission_columns($column, $post_id)
{
      // Return meta data for individual posts on table

      switch ($column) {

            case 'name':
                  echo esc_html(get_post_meta($post_id, 'name', true));
                  break;

            case 'email':
                  echo esc_html(get_post_meta($post_id, 'email', true));
                  break;

            case 'phone':
                  echo esc_html(get_post_meta($post_id, 'phone', true));
                  break;

            case 'message':
                  echo esc_html(get_post_meta($post_id, 'message', true));
                  break;
      }
}

function custom_submission_columns($columns)
{
      // Edit the columns for the submission table

      // __() 函数用于获取翻译后的字符串。
      // 'contact-plugin' 是一个文本域，用于将这些字符串与 contact-plugin 插件的翻译文件关联起来，以便在不同语言环境下显示正确的翻译。
      // 对应插件定义 Text Domain: contact-plugin

      $columns = array(

            'cb' => $columns['cb'],
            'name' => __('Name', 'contact-plugin'),
            'email' => __('Email', 'contact-plugin'),
            'phone' => __('Phone', 'contact-plugin'),
            'message' => __('Message', 'contact-plugin'),
            'date' => 'Date',

      );

      return $columns;
}

// **'custom_contact_form'**：元框的 ID。
// **'Submission'**：元框的标题。
// **'display_submission'**：回调函数，用于显示元框内容。
// **'submission'**：自定义文章类型的标识符，表示元框将显示在 'submission' 类型的文章编辑页面上。
function create_meta_box()
{
      // Create custom meta box to display submission

      add_meta_box('custom_contact_form', 'Submission', 'display_submission', 'submission');
}

function display_submission()
{
      // Display individual submission data on it's page

      // $postmetas = get_post_meta( get_the_ID() );

      // echo '<ul>';

      // foreach($postmetas as $key => $value)
      // {

      //       echo '<li><strong>' . $key . ':</strong> ' . $value[0] . '</li>';

      // }

      // echo '</ul>';

      // **esc_html**：用于转义 HTML 标签，防止 XSS 攻击。
      // 输入：'<script>alert("XSS Attack!");</script>'
      // 输出：&lt;script&gt;alert(&quot;XSS Attack!&quot;);&lt;/script&gt;


      echo '<ul>';

      echo '<li><strong>Name:</strong><br /> ' . esc_html(get_post_meta(get_the_ID(), 'name', true)) . '</li>';
      echo '<li><strong>Email:</strong><br /> ' . esc_html(get_post_meta(get_the_ID(), 'email', true)) . '</li>';
      echo '<li><strong>Phone:</strong><br /> ' . esc_html(get_post_meta(get_the_ID(), 'phone', true)) . '</li>';
      echo '<li><strong>Message:</strong><br /> ' . esc_html(get_post_meta(get_the_ID(), 'message', true)) . '</li>';

      echo '</ul>';
}

function create_submissions_page()
{

      // Create the submissions post type to store form submissions


      // **public**：设置为 true，表示此自定义文章类型是公开的。
      // **has_archive**：设置为 true，表示此自定义文章类型有存档页面。
      // **menu_position**：设置为 30，表示此自定义文章类型在管理菜单中的位置。
      // **publicly_queryable**：设置为 false，表示此自定义文章类型不能通过 URL 直接访问。
      // **labels**：定义了此自定义文章类型在管理界面中的显示标签。
      // **supports**：设置为 false，表示此自定义文章类型不支持任何功能。
      // **capability_type**：设置为 'post'，表示使用与文章相同的权限。
      // **capabilities**：定义了具体的权限，create_posts 设置为 false，表示不允许创建新文章。
      // **map_meta_cap**：设置为 true，表示使用默认的权限映射。

      $args = [

            'public' => true,
            'has_archive' => false,
            'menu_position' => 30,
            'publicly_queryable' => false,
            'labels' => [

                  'name' => 'Submissions',
                  'singular_name' => 'Submission',
                  'edit_item' => 'View Submission'

            ],
            'supports' => false,
            'capability_type' => 'post',
            'capabilities' => array(
                  'create_posts' => false,
            ),
            'map_meta_cap' => true
      ];

      register_post_type('submission', $args);
}

function show_contact_form()
{
      include MY_PLUGIN_PATH . '/includes/templates/contact-form.php';
}

function create_rest_endpoint()
{

      // Create endpoint for front end to connect to WordPress securely to post form data
      register_rest_route('v1/contact-form', 'submit', array(

            'methods' => 'POST',
            'callback' => 'handle_enquiry'

      ));
}


function handle_enquiry($data)
{
      // Handle the form data that is posted

      // Get all parameters from form
      $params = $data->get_params();

      // Set fields from the form
      $field_name = sanitize_text_field($params['name']);
      $field_email = sanitize_email($params['email']);
      $field_phone = sanitize_text_field($params['phone']);
      $field_message = sanitize_textarea_field($params['message']);


      // Check if nonce is valid, if not, respond back with error
      if (!wp_verify_nonce($params['_wpnonce'], 'wp_rest')) {

            return new WP_Rest_Response('Message not sent', 422);
      }

      // Remove unneeded data from paramaters
      unset($params['_wpnonce']);
      unset($params['_wp_http_referer']);

      // Send the email message
      $headers = [];

      $admin_email = get_bloginfo('admin_email');
      $admin_name = get_bloginfo('name');

      // Set recipient email
      $recipient_email = get_plugin_options('contact_plugin_recipients');

      if (!$recipient_email) {
            // Make all lower case and trim out white space
            $recipient_email = strtolower(trim($recipient_email));
      } else {

            // Set admin email as recipient email if no option has been set
            $recipient_email = $admin_email;
      }


      $headers[] = "From: {$admin_name} <{$admin_email}>";
      $headers[] = "Reply-to: {$field_name} <{$field_email}>";
      $headers[] = "Content-Type: text/html";

      $subject = "New enquiry from {$field_name}";

      $message = '';
      $message = "<h1>Message has been sent from {$field_name}</h1>";


      $postarr = [

            'post_title' => $params['name'],
            'post_type' => 'submission',
            'post_status' => 'publish'

      ];

      $post_id = wp_insert_post($postarr);

      // Loop through each field posted and sanitize it
      foreach ($params as $label => $value) {

            switch ($label) {

                  case 'message':

                        $value = sanitize_textarea_field($value);
                        break;

                  case 'email':

                        $value = sanitize_email($value);
                        break;

                  default:

                        $value = sanitize_text_field($value);
            }

            add_post_meta($post_id, sanitize_text_field($label), $value);

            $message .= '<strong>' . sanitize_text_field(ucfirst($label)) . ':</strong> ' . $value . '<br />';
      }


      wp_mail($recipient_email, $subject, $message, $headers);

      $confirmation_message = "The message was sent successfully!!";

      if (get_plugin_options('contact_plugin_message')) {

            $confirmation_message = get_plugin_options('contact_plugin_message');

            $confirmation_message = str_replace('{name}', $field_name, $confirmation_message);
      }

      return new WP_Rest_Response($confirmation_message, 200);
}
