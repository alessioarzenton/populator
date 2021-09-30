<?php
/*
Plugin Name: Populator
Author: Nextre
*/

define('POPULATOR_PAGES_DIR', __DIR__ . "/pages");
define('POPULATOR_INC_DIR', __DIR__ . "/inc");
define('POPULATOR_META_YEAR', 'populator.year');

/**
 * Function that handles the main page of the plugin.
 */
function populator_main_page()
{
    require_once(POPULATOR_PAGES_DIR . "/main.php");
}

/**
 * Function that handles all menu voices of the plugin.
 */
function populator_menu()
{
    add_menu_page('Populator', 'Populator', 'edit_others_posts', 'populator', 'populator_main_page');
}
add_action('admin_menu', 'populator_menu');


/*
Genera un utente random, ritorna un array
 */
function populator_create_random_user($chars = 10)
{
    $username = "demo-";

    for ($i=1; $i <= $chars; $i++) {

      $rand = rand(65,122);

      if (($rand > 90) && ($rand < 97)) {
        $i--;
        continue;
      }

      $username .= chr($rand);

    }

    return [
        'username' => $username,
        'password' => md5($username),
        'email' => "{$username}@fakedomain.fake"
    ];
}

/*
Aggiunge un numero indicato di utenti demo e ne visualizza il contenuto
 */
function populator_add_demo_users($count)
{
    $randomUsers = array();
    for($i = 1; $i <= $count; $i++)
    {
        $thisRandomUser = populator_create_random_user();
        $username = $thisRandomUser['username'];
        if (!array_key_exists($username, $randomUsers))
        {
            $randomUsers[$username] = $thisRandomUser;
        }
    }

    foreach($randomUsers as $user)
    {
      $result = wp_create_user($user['username'], $user['email'], $user['password']);

      if (!$result) {
        $message = sprintf("Non è stato possibile registrare l'utente %s", $user['username']);
      }

      echo $message;

    }

}

/*
Elimina un numero indicato di utenti demo
 */
function population_remove_demo_users($count, $prefix = "demo-")
{
    global $wpdb;
    $tableName = $wpdb->prefix . "users";

    $sql = "SELECT * FROM {$tableName} WHERE user_login LIKE CONCAT('%s','%') LIMIT %d ";

    $query = $wpdb->prepare($sql,$prefix,$count);
    $resCount = $wpdb->query($query);

    if( !$resCount ) {
      echo '<div class="alert alert-warning">Non ci sono utenti demo!</div>';
    } else {
      echo '<div class="alert alert-success">Utenti demo eliminati: ' . $resCount . ' </div>';
    }

    $args = array(
      'number' => $resCount,
      'exclude' => [1],
      'search' => 'demo-*',
      'search_column' => ['user_login']
    );

    $user_query = new WP_User_Query($args);
    $users = $user_query->get_results();

    foreach ($users as $user) {
      wp_delete_user($user->ID);
    }

}


/*
Visualizza il numero totale di utenti demo
*/
function all_demo_users($prefix = "demo-")
{
  global $wpdb;
  $tableName = $wpdb->prefix . "users";

  $sql = "SELECT * FROM {$tableName} WHERE user_login LIKE CONCAT('%s','%')";

  $query = $wpdb->prepare($sql,$prefix);
  $resCount = $wpdb->query($query);

  echo $resCount;
}


/*
Visualizza il numero totale di utenti demo
*/
function all_demo_users_view()
{

  $servername = "localhost";
  $username = "root";
  $password = "";
  $dbname = "mypersonaltest";


  $conn = new mysqli($servername, $username, $password, $dbname);

  if ($conn->connect_error) {
    die("Connessione fallita: " . $conn->connect_error);
  }

  $sql = "SELECT ID, user_login, user_registered FROM wp_users WHERE user_login LIKE 'demo%' ";
  $result = $conn->query($sql);

  if ($result > 0) {
    echo "<h5 class='text-danger'>Utenti demo</h5><table class='table table-dark table-striped'><tr class='table-dark'><th>ID</th><th>Nome</th><th>Data registrazione</th></tr>";

    while($row = $result->fetch_assoc()) {
      echo "<tr class='table-dark'><td class='table-dark'>".$row["ID"]."</td><td class='table-dark'>".$row["user_login"]."</td><td class='table-dark'>".explode(" ",$row["user_registered"])[0]."</td></tr>";
    }

    echo "</table>";
  } else {
    echo "0 Utenti";
  }

$conn->close();

}


/*
Visualizza il numero di utenti dell'anno scorso
*/
function all_old_users()
{
  global $wpdb;
  $tableName = $wpdb->prefix . "users";

  $yearNow = date('Y');
  $yearOld = $yearNow - 1;

  $sql = "SELECT * FROM {$tableName} WHERE user_registered LIKE '{$yearOld}-%' ";

  $query = $wpdb->prepare($sql);
  $resCount = $wpdb->query($query);

  if (!$resCount) {
    echo 0;
  } else {
    echo $resCount;
  }

}


/*
Elimina un numero di utenti dell'anno scorso
 */
function population_remove_old_users($count)
{
  try {
    global $wpdb;
    $tableName = $wpdb->prefix . "users";

    $yearNow = date('Y');
    $yearOld = $yearNow - 1;

    $sql = "SELECT * FROM {$tableName} WHERE user_registered LIKE '{$yearOld}-%' LIMIT %d ";

    $query = $wpdb->prepare($sql,$count);
    $resCount = $wpdb->query($query);

    if( !$resCount ) {
      echo '<div class="alert alert-warning">Non ci sono gli utenti selezionati</div>';
    } else {
      echo '<div class="alert alert-success">Utenti anno precedente eliminati: ' . $resCount . ' </div>';
    }
  } catch (Exception $e) {
    user_error('Operazione negata', E_USER_ERROR);
  }

  $yearOldString = strlen($yearOld);

  $args = array(
    'number' => $resCount,
    'exclude' => [1],
    'meta_query' => array(
        [
            'key' => POPULATOR_META_YEAR,
            'value' => $yearOld,
            'compare' => '<='
        ]
    )
  );

  $user_query = new WP_User_Query($args);
  $users = $user_query->get_results();

  foreach ($users as $user) {
    wp_delete_user($user->ID);
  }

}


function populator_add_user_meta($meta)
{
    $meta[POPULATOR_META_YEAR] = date("Y");
    return $meta;
}
add_filter('insert_user_meta', 'populator_add_user_meta');



/**
 * Inclusion of plugin style sheets.
 */
function populator_stylesheets()
{
    $cssUrl = plugin_dir_url(__FILE__) . "inc/style.min.css";
    wp_enqueue_style('pop-style', $cssUrl);
}
add_action('admin_enqueue_scripts', 'populator_stylesheets');

/**
 * Inclusion of plugin scripts.
 */
function populator_scripts()
{
    $jsUrl = plugin_dir_url(__FILE__) . "inc/script.min.js";
    $popperUrl = plugin_dir_url(__FILE__) . "inc/popper.min.js";
    wp_enqueue_script('pop-script', $jsUrl);
    wp_enqueue_script('pop-popper', $popperUrl);
}
add_action('admin_enqueue_scripts', 'populator_scripts');
