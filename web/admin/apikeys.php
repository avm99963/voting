<?php
require_once(__DIR__."/../core.php");

if (!user::loggedIn()) {
  header('Location: login.php');
  exit();
}

$i18n = new i18n("adminvotings", 1);

$id = (int)$_GET["id"];

if (empty($id)) {
  header("Location: votings.php");
  exit();
}

$query = mysqli_query($con, "SELECT * FROM votings WHERE id = ".$id);

if (!mysqli_num_rows($query)) {
  die("This voting does not exist");
  exit();
}

$row = mysqli_fetch_assoc($query);

$md_header_row_before = md::backBtn("voting.php?id=".$id);
?>
<!DOCTYPE html>
<html>
<head>
  <title><?=$i18n->msg("apikeys")?> – <?=$row["name"]?> – <?php echo $conf["appname"]; ?></title>
  <?php include(__DIR__."/../includes/adminhead.php"); ?>

  <script src="../js/apikeys.js"></script>

  <style>
  .addapikey {
    position: fixed;
    bottom: 16px;
    right: 16px;
  }

  .ellipsis {
    width: 125px!important;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
  }

  .ellipsis.small {
    width: 75px!important;
  }

  @media (max-width: 750px) {
    .extra {
      display: none;
    }
  }

  @media (max-width: 360px) {
    .extraextra {
      display: none;
    }
  }
  </style>
</head>
<body class="mdl-color--green">
  <div class="mdl-layout mdl-js-layout mdl-layout--fixed-header mdl-layout--fixed-drawer">
    <?php require(__DIR__."/../includes/adminmdnav.php"); ?>
    <main class="mdl-layout__content">
      <?php if (user::role() < 2) { ?>
        <button class="addapikey mdl-button md-js-button mdl-button--fab mdl-js-ripple-effect mdl-button--accent"><i class="material-icons">vpn_key</i><span class="mdl-ripple"></span></button>
      <?php } ?>
      <div class="page-content">
        <div class="main mdl-shadow--4dp">
          <h2><?=$i18n->msg("apikeys")?> – <?=$row["name"]?></h2>
          <?php
          if (isset($_GET["keyid"]) && !empty((int)$_GET["keyid"])) {
            $query3 = mysqli_query($con, "SELECT keytext FROM apikeys WHERE id = ".(int)$_GET["keyid"]);

            if (mysqli_num_rows($query3)) {
              $apikey = mysqli_fetch_assoc($query3);

              ?>
              <div class="info mdl-shadow--2dp">You have successfully generated the following API key: <code><?=$apikey["keytext"]?></code></div>
              <?php
            }
          }

          $query2 = mysqli_query($con, "SELECT * FROM apikeys WHERE voting = ".$id);

          if (mysqli_num_rows($query2)) {
            ?>
            <p><?=$i18n->msg("apikeys_intro")?></p>
            <table class="mdl-data-table mdl-js-data-table mdl-shadow--2dp">
        			<thead>
        				<tr>
        					<th class='extra'><?=$i18n->msg("id")?></th>
        					<th class='mdl-data-table__cell--non-numeric'><?=$i18n->msg("apikey")?></th>
        					<th class='mdl-data-table__cell--non-numeric extra'><?=$i18n->msg("description")?></th>
        					<th class='mdl-data-table__cell--non-numeric extra'><?=$i18n->msg("creator")?></th>
                  <th class='mdl-data-table__cell--non-numeric extra'><?=$i18n->msg("uses")?></th>
                  <th class='mdl-data-table__cell--non-numeric extraextra'><?=$i18n->msg("status")?></th>
                  <th></th>
        				</tr>
        			</thead>
        			<tbody>
            		<?php
            		while ($apikey = mysqli_fetch_assoc($query2)) {
                  $name = user::userData("name", $apikey["userid"]);
            			echo "<tr><td class='extra'>".$apikey['id']."</td><td class='mdl-data-table__cell--non-numeric'><div class='ellipsis' title='".$apikey['keytext']."'>".$apikey['keytext']."</div></td><td class='mdl-data-table__cell--non-numeric extra'><div class='ellipsis' title='".$apikey['description']."'>".$apikey['description']."</div></td><td class='mdl-data-table__cell--non-numeric extra'><div class='ellipsis small' title='".$name."'>".$name."</div></td><td class='extra'>".$apikey["uses"]."</td><td class='mdl-data-table__cell--non-numeric extraextra'>".$i18n->msg("apikeystatus_".(int)$apikey["status"])."</td>";
                  if (user::role() < 2 && $apikey['status'] == 0) {
                    echo "<td class='mdl-data-table__cell--non-numeric'><a href=\"javascript:dynDialog.load('ajax/revokeapikey.php?id=".$apikey['id']."');\"><i class='material-icons icon'>delete_forever</i></a></td></tr>";
                  } else {
                    echo "<td class='mdl-data-table__cell--non-numeric'></td>";
                  }
            		}
            		?>
        			</tbody>
        		</table>
            <?php
          } else {
            ?>
            <p><i><?=$i18n->msg("noapikeys")?></i></p>
            <?php
          }
          ?>
        </div>
      </div>
    </main>
    <?php
    if (user::role() < 2) {
      ?>
      <dialog class="mdl-dialog" id="addapikey">
        <form action="newapikey.php" method="POST" autocomplete="off">
          <input type="hidden" name="voting" value="<?=$id?>">
          <h4 class="mdl-dialog__title"><?=$i18n->msg("addapikey_title")?></h4>
          <div class="mdl-dialog__content">
            <div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label">
              <textarea class="mdl-textfield__input" name="description" rows="3" id="description" autocomplete="off"></textarea>
              <label class="mdl-textfield__label" for="description"><?=$i18n->msg("description")?></label>
            </div>
            <br>
            <div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label">
              <input class="mdl-textfield__input" type="number" min="1" name="uses" id="uses" autocomplete="off">
              <label class="mdl-textfield__label" for="uses"><?=$i18n->msg("usespercode")?></label>
            </div>
          </div>
          <div class="mdl-dialog__actions">
            <button type="submit" class="mdl-button mdl-js-button mdl-js-ripple-effect mdl-button--accent"><?=$i18n->msg("add")?></button>
            <button onclick="event.preventDefault(); document.querySelector('#addapikey').close();" class="mdl-button mdl-js-button mdl-js-ripple-effect cancel"><?=$i18n->msg("cancel")?></button>
          </div>
        </form>
      </dialog>
      <?php
    }
    ?>
  </div>
  <?php
  md::msg(array("apikeyrevoked"));
  ?>

</body>
</html>
