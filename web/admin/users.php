<?php
require_once(__DIR__."/../core.php");

if (!user::loggedIn()) {
  header('Location: login.php');
  exit();
}

$i18n = new i18n("adminusers", 1);

$md_header_row_more = '<div class="mdl-layout-spacer"></div>
      <div class="mdl-textfield mdl-js-textfield mdl-textfield--expandable
                  mdl-textfield--floating-label mdl-textfield--align-right">
        <label class="mdl-button mdl-js-button mdl-button--icon"
               for="usuario">
          <i class="material-icons">search</i>
        </label>
        <div class="mdl-textfield__expandable-holder">
          <input class="mdl-textfield__input" type="text" name="usuario"
                 id="usuario">
        </div>
      </div>';
?>
<!DOCTYPE html>
<html>
<head>
  <title><?=$i18n->msg("users")?> – <?php echo $conf["appname"]; ?></title>
  <?php include(__DIR__."/../includes/adminhead.php"); ?>

  <link rel="stylesheet" href="../lib/mdl-ext/mdl-ext.min.css">
  <script src="../lib/mdl-ext/mdl-ext.min.js"></script>

  <script src="../js/users.js"></script>

  <script src="//ajax.googleapis.com/ajax/libs/jquery/1.10.1/jquery.min.js"></script>
  <link rel="stylesheet" href="../lib/datatables/dataTables.material.min.css">
  <script src="../lib/datatables/jquery.dataTables.min.js"></script>
  <script src="../lib/datatables/dataTables.material.min.js"></script>

  <script>
  window.addEventListener("load", function() {
    var datatable = $('.datatable').DataTable({
      paging:   false,
      ordering: false,
      info:     false,
      searching:true
    });

    document.querySelector("#usuario").addEventListener("input", function(evt) {
      this.search(evt.target.value);
      this.draw(true);
    }.bind(datatable));
  });
  </script>

  <style>
  .adduser {
    position: fixed;
    bottom: 16px;
    right: 16px;
  }

  .importcsv {
    position:fixed;
    bottom: 80px;
    right: 25px;
  }

  @media (max-width: 655px) {
    .extra {
      display: none;
    }
  }

  /* Hide datable's search box */
  .dataTables_wrapper .mdl-grid:first-child {
    display: none;
  }

  .dt-table {
    padding: 0!important;
  }

  .dt-table .mdl-cell {
    margin: 0!important;
  }

  #usuario {
    position: relative;
  }
  </style>
</head>
<body class="mdl-color--green">
  <div class="mdl-layout mdl-js-layout mdl-layout--fixed-header mdl-layout--fixed-drawer">
    <?php require(__DIR__."/../includes/adminmdnav.php"); ?>
    <main class="mdl-layout__content">
      <?php if (user::role() < user::$maxrole) { ?>
        <button class="adduser mdl-button md-js-button mdl-button--fab mdl-js-ripple-effect mdl-button--accent"><i class="material-icons">person_add</i><span class="mdl-ripple"></span></button>
      <?php } if (user::role() == 0) { ?>
        <button class="importcsv mdl-button mdl-js-button mdl-button--fab mdl-button--mini-fab mdl-color--grey-200"><i class="material-icons">file_upload</i></button>
      <?php } ?>
      <div class="page-content">
        <div class="main mdl-shadow--4dp">
          <h2><?=$i18n->msg("users")?></h2>
            <table class="mdl-data-table mdl-js-data-table mdl-shadow--2dp datatable">
        			<thead>
        				<tr>
        					<th class='extra'><?=$i18n->msg("id")?></th>
        					<th class='mdl-data-table__cell--non-numeric'><?=$i18n->msg("username")?></th>
        					<th class='mdl-data-table__cell--non-numeric extra'><?=$i18n->msg("realname")?></th>
        					<th class='mdl-data-table__cell--non-numeric extra'><?=$i18n->msg("type")?></th>
                  <th></th>
        				</tr>
        			</thead>
        			<tbody>
            		<?php
            		$query = mysqli_query($con, "SELECT * FROM users ORDER BY id ASC") or die("<div class='alert-danger'>".mysqli_error()."</div>");
            		while ($row = mysqli_fetch_assoc($query)) {
            			echo "<tr><td class='extra'>".$row['id']."</td><td class='mdl-data-table__cell--non-numeric'>".$row['username']."</td><td class='mdl-data-table__cell--non-numeric extra'>".$row['name']."</td><td class='mdl-data-table__cell--non-numeric extra'>".$i18n->msg("type_".(int)$row["type"])."</td>";
                  if (user::canEditUser($row["type"])) {
                    echo "<td class='mdl-data-table__cell--non-numeric'><a href='user.php?id=".$row['id']."'><i class='material-icons icon'>pageview</i></a> <a href=\"javascript:dynDialog.load('ajax/edituser.php?id=".$row['id']."');\"><i class='material-icons icon'>edit</i></a> <a href=\"javascript:dynDialog.load('ajax/deleteuser.php?id=".$row['id']."')\"><i class='material-icons icon'>delete</i></a></td></tr>";
                  } else {
                    echo "<td class='mdl-data-table__cell--non-numeric'></td>";
                  }
            		}
            		?>
        			</tbody>
        		</table>
          </div>
        </div>
      </div>
    </main>
  </div>
  <?php
  if (user::role() < user::$maxrole) {
    ?>
    <dialog class="mdl-dialog" id="adduser">
      <form action="newuser.php" method="POST" autocomplete="off">
        <h4 class="mdl-dialog__title"><?=$i18n->msg("add_title")?></h4>
        <div class="mdl-dialog__content">
          <div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label">
            <input class="mdl-textfield__input" type="text" name="username" id="username" autocomplete="off">
            <label class="mdl-textfield__label" for="username"><?=$i18n->msg("username")?></label>
          </div>
          <br>
          <div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label">
            <input class="mdl-textfield__input" type="password" name="password" id="password" autocomplete="off">
            <label class="mdl-textfield__label" for="password"><?=$i18n->msg("password")?></label>
          </div>
          <br>
          <div class="mdlext-selectfield mdlext-js-selectfield mdlext-selectfield--floating-label">
            <select name="type" id="type" class="mdlext-selectfield__select">
              <option value=""></option>
              <?php
              for ($i = user::minUserAddRole(); $i <= user::$maxrole; $i++) {
                echo '<option value="'.$i.'">'.$i18n->msg("type_".$i).'</option>';
              }
              ?>
            </select>
            <label for="type" class="mdlext-selectfield__label"><?=$i18n->msg("type")?></label>
          </div>
          <br>
          <div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label">
            <input class="mdl-textfield__input" type="text" name="realname" id="realname" autocomplete="off">
            <label class="mdl-textfield__label" for="realname"><?=$i18n->msg("realname")?></label>
          </div>
        </div>
        <div class="mdl-dialog__actions">
          <button type="submit" class="mdl-button mdl-js-button mdl-js-ripple-effect mdl-button--accent"><?=$i18n->msg("add")?></button>
          <button onclick="event.preventDefault(); document.querySelector('#adduser').close();" class="mdl-button mdl-js-button mdl-js-ripple-effect cancel"><?=$i18n->msg("cancel")?></button>
        </div>
      </form>
    </dialog>
    <?php
  }
  if (user::role() == 0) {
    ?>
    <dialog class="mdl-dialog" id="importcsv">
      <form action="csv.php" method="POST" enctype="multipart/form-data">
        <h4 class="mdl-dialog__title">Importar CSV</h4>
        <div class="mdl-dialog__content">
          <p>Selecciona debajo el archivo CSV:</p>
          <p><input type="file" name="file" accept=".csv"></p>
        </div>
        <div class="mdl-dialog__actions">
          <button type="submit" class="mdl-button mdl-js-button mdl-js-ripple-effect mdl-button--accent">Importar</button>
          <button onclick="event.preventDefault(); document.querySelector('#importcsv').close();" class="mdl-button mdl-js-button mdl-js-ripple-effect cancel">Cancelar</button>
        </div>
      </form>
    </dialog>
    <?php
  }

  md::msg(array("useradded", "usernew", "userdelete", "empty", "usernametaken"));
  ?>
</body>
</html>
