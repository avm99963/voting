<?php
require_once(__DIR__."/../core.php");

if (!user::loggedIn()) {
  header('Location: login.php');
  exit();
}

$i18n = new i18n("admincensus", 1);

if (!isset($_GET["name"])) {
  header("Location: census.php");
  exit();
}

$md_header_row_more = '<div class="mdl-layout-spacer"></div>
      <div class="mdl-textfield mdl-js-textfield mdl-textfield--expandable
                  mdl-textfield--floating-label mdl-textfield--align-right">
        <form action="searchcitizen.php" method="GET">
          <label class="mdl-button mdl-js-button mdl-button--icon"
                 for="name">
            <i class="material-icons">search</i>
          </label>
          <div class="mdl-textfield__expandable-holder">
            <input class="mdl-textfield__input" type="text" name="name"
                   id="name" value="'.htmlspecialchars($_GET["name"]).'">
          </div>
        </form>
      </div>';

$md_header_row_before = md::backBtn("census.php");
?>
<!DOCTYPE html>
<html>
<head>
  <title><?=$i18n->msg("searchtitle")?> – <?php echo $conf["appname"]; ?></title>
  <?php include(__DIR__."/../includes/adminhead.php"); ?>

  <style>
  .voting {
    border-left: solid 1px gray;
  }
  </style>

  <script src="../bower_components/qr-js/qr.min.js"></script>
</head>
<body class="mdl-color--green">
  <div class="mdl-layout mdl-js-layout mdl-layout--fixed-header mdl-layout--fixed-drawer">
    <?php require(__DIR__."/../includes/adminmdnav.php"); ?>
    <main class="mdl-layout__content">
      <div class="page-content">
        <div class="main mdl-shadow--4dp">
          <h2><?=$i18n->msg("searchtitle2", array(htmlspecialchars($_GET["name"])))?></h2>
          <?php
          $name = mysqli_real_escape_string($con, $_GET["name"]);
          if (strlen($name) < 3 || strlen($_GET["name"]) < 3) {
            die($i18n->msg("searchtooshort"));
          }
          $query = mysqli_query($con, "SELECT * FROM generatedcodes WHERE name LIKE '%".$name."%' OR dni LIKE '%".$name."%'");
          if (mysqli_num_rows($query)) {
            echo "<p>".$i18n->msg("searchresults", array(mysqli_num_rows($query)))."</p>";
            while ($row = mysqli_fetch_assoc($query)) {
              ?>
              <a class="votingcontainer" href="javascript:dynDialog.load('ajax/citizen.php?id=<?=$row["id"]?>&q=<?=htmlspecialchars($_GET["name"])?>')">
                <div class="voting mdl-js-ripple-effect">
                  <div class="text">
                    <span class="title"><?=$row["name"]?></span><br>
                    <span class="description"><?=$row["dni"]?>, <?=date("d/m/Y", $row["birthday"])?></span>
                  </div>
                  <span class="mdl-ripple">
                </div>
              </a>
              <?php
            }
          } else {
            echo "<p>".$i18n->msg("nosearchresults")."</p>";
          }
          ?>
        </div>
      </div>
    </main>
  </div>
  <?php
  md::msg(array("citizenadded", "citizennew", "citizendelete", "empty", "usernametaken", "activecode"));
  ?>
</body>
</html>
