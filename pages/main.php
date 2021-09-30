<?php
$thisPage = admin_url('admin.php?page=populator');
 ?>

<div class="container-fluid py-5 mx-0" style="background: linear-gradient(rgba(0,0,0,0.4),rgba(0,0,0,0.8)), url('https://www.differenzatra.it/wp-content/uploads/2020/04/Differenza-tra-webinar-e-meeting.jpg')">
    <div class="row">
      <div class="col">
        <h2 class="text-white ps-3">My Populator</h2>
      </div>
    </div>
</div>
<div class="container-fluid bg-light py-5">
  <div class="container">
    <div class="row">
      <div class="col">
        <form id="my-form" action="<?php print($thisPage) ?>" method="POST">
          <h4>Scegli un'operazione:</h4>
          <div class="row h-100 align-items-center">
            <div class="col-4 h-100">
              <select name="action" type="text" class="form-control h-100">
                <option value="add">Crea utenti demo</option>
                <option value="remove-demo-users">Elimina utenti demo</option>
                <option value="remove-old-users">Elimina utenti anno precedente</option>
              </select>
            </div>
            <div class="col-4 h-100">
              <input name="rows" type="text" class="form-control h-100" placeholder="Scegli una quantità" required>
            </div>
            <div class="col-4 h-100">
              <button type="submit" class="btn btn-primary h-100">Procedi</button>
            </div>
          </div>
        </form>
        <div class="row mt-2">
          <div class="col">
            <h5 class="my-2">Conteggio:</h5>
            <p class="text-secondary mb-2">Utenti demo anno corrente: <span class="text-primary"> <?php all_demo_users() ?> </span></p>
            <p class="text-secondary mb-2">Utenti anno precedente: <span class="text-primary"> <?php all_old_users() ?> </span></p>
          </div>
        </div>
        <hr>

        <?php

        $action = trim($_POST['action']);
        $rows = trim($_POST['rows']);
        $count = empty($rows) ? 0 : (int)$rows;

        if (empty($count)) {
          all_demo_users_view();
        } elseif (!empty($count) && $action == 'add') {
          populator_add_demo_users($count);
          all_demo_users_view();
        } elseif (!empty($count) && $action == 'remove-demo-users') {
          population_remove_demo_users($count);
          all_demo_users_view();
        } elseif (!empty($count) && $action == 'remove-old-users') {
          population_remove_old_users($count);
          all_demo_users_view();
        }

         ?>


      </div>
    </div>
  </div>
</div>
