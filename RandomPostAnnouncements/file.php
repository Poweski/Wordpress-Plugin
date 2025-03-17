<?php
/**
 * Plugin Name: Random Post Announcements
 * Description: Shows a random announcemet at the beginning of each post
 * Version: 1.0
 * Author: Jan Poweski
 */


class Random_Post_Announcements {
   public function __construct() {
      add_action('admin_menu', [$this, 'add_admin_menu']);
      add_action('admin_init', [$this, 'register_settings']);
      add_filter('the_content', [$this, 'display_announcement']);
      add_shortcode('random_announcement', [$this, 'shortcode_announcement']);
      add_action('init', [$this, 'random_post_announcements_styles']);
   }

   public function random_post_announcements_styles() {
      wp_register_style('random-post-announcements-styles',
      plugins_url('/css/style.css', __FILE__));
      wp_enqueue_style('random-post-announcements-styles');
   }

   public function add_admin_menu() {
      add_options_page('Aannouncements', 'Announcements', 'manage_options', 'random_post_announcements', [$this, 'settings_page']);
   }

   public function register_settings() {
      register_setting('random_post_announcements_group', 'random_post_announcements');
   }

   public function settings_page() {
      $announcements = get_option('random_post_announcements', []);
      if (!is_array($announcements)) {
          $announcements = [];
      }

      if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['new_announcement'])) {
         $new_announcement = sanitize_text_field($_POST['new_announcement']);
         if (!empty($new_announcement)) {
            $announcements[] = $new_announcement;
            update_option('random_post_announcements', $announcements);
         }
      }

      if (isset($_GET['delete'])) {
         $index = (int) $_GET['delete'];
         if (isset($announcements[$index])) {
            unset($announcements[$index]);
            update_option('random_post_announcements', array_values($announcements));
         }
      }
      ?>
      <div class="wrap">
         <h1>Announcements settings</h1>
         <form method="post">
            <table class="form-table">
               <tr valign="top">
                  <th scope="row">New announcement (in HTML)</th>
                  <td>
                     <textarea name="new_announcement" rows="5" cols="50"></textarea>
                  </td>
               </tr>
            </table>
            <?php submit_button('Add the announcement'); ?>
         </form>

         <h2>List of announcements</h2>
         <table class="wp-list-table widefat fixed striped">
            <thead>
               <tr>
                  <th>ID</th>
                  <th>Content</th>
                  <th>Actions</th>
               </tr>
            </thead>
            <tbody>
               <?php foreach ($announcements as $index => $announcement) : ?>
                  <tr>
                     <td><?php echo esc_html($index); ?></td>
                     <td><?php echo esc_html($announcement); ?></td>
                     <td>
                        <a href="?page=random_post_announcements&delete=<?php echo esc_attr($index); ?>" class="button button-secondary">Delete</a>
                     </td>
                  </tr>
                <?php endforeach; ?>
            </tbody>
         </table>
      </div>
      <?php
   }

   public function get_random_announcement() {
      $announcements = get_option('random_post_announcements', []);
      $announcements = array_filter($announcements);
      return !empty($announcements) ? $announcements[array_rand($announcements)] : '';
   }

   public function display_announcement($content) {
      if (is_single()) {
         $announcement = $this->get_random_announcement();
         if (!empty($announcement)) {
            $announcement_html = '<div class="announcement">' . wpautop($announcement) . '</div>';
            return $announcement_html . $content;
         }
      }
      return $content;
   }

   public function shortcode_announcement() {
      return $this->get_random_announcement();
   }
}

new Random_Post_Announcements();
