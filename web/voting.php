<?php
require_once(__DIR__."/core.php");

if (!citizen::loggedIn()) {
  header('Location: index.php');
  exit();
}

$i18n = new i18n("dashboard");

$time = time();

if (!isset($_GET["id"])) {
  header("Location: dashboard.php");
  exit();
}

$id = (int)$_GET["id"];

$query = mysqli_query($con, "SELECT * FROM votings WHERE id = ".$id." AND status = 1");

if (!mysqli_num_rows($query)) {
  header("Location: dashboard.php");
  exit();
}

$row = mysqli_fetch_assoc($query);

if ($time < $row["datebegins"]) {
  $status = 0;
} elseif ($time < $row["dateends"]) {
  $status = 1;
} else {
  $status = 2;
}

$votestatus = voting::canVote($row["id"]);
$canvote = $votestatus == 1;

$md_header_row_before = md::backBtn("dashboard.php");
?>
<!DOCTYPE html>
<html>
<head>
  <title><?=$row["name"]?> – <?php echo $conf["appname"]; ?></title>
  <?php include(__DIR__."/includes/citizenhead.php"); ?>

  <style>
  #actions {
    margin-top: 16px;
    margin-left: 16px;
    margin-bottom: 16px;
    float: right;
  }
  </style>
</head>
<body class="mdl-color--green">
  <div class="mdl-layout mdl-js-layout mdl-layout--fixed-header mdl-layout--fixed-drawer">
    <?php require(__DIR__."/includes/citizenmdnav.php"); ?>
    <main class="mdl-layout__content">
      <div class="page-content">
        <div class="main mdl-shadow--4dp">
          <div id="actions">
            <a<?=($canvote === true ? " href=\"vote.php?id=$id\"" : "")?> class="mdl-button mdl-js-button mdl-button--raised mdl-button--accent mdl-js-ripple-effect" id="votebtn"<?=($canvote === true ? "" : " disabled")?>><?=$i18n->msg("vote")?></a>
          </div>
          <h2><?=$row["name"]?></h2>
          <p><?=$row["description"]?></p>
          <div class="info mdl-shadow--2dp"><?=$i18n->msg("votingstatus_".$status)?></div>
          <h4><?=$i18n->msg("dates")?></h4>
          <p><?=$i18n->msg("fromto", array(date("d/m/Y H:i", $row["datebegins"]), date("d/m/Y H:i", $row["dateends"])))?></p>
          <h4><?=$i18n->msg("results")?></h4>
          <?php
          $noresults = false;

          if ($status < 2) {
            $noresults = true;
          } else {
            $results = voting::defaultResults($row["id"]);
            $noresults = ($results === false ? true : false);
          }

          if ($noresults === true) {
            ?>
            <p><?=$i18n->msg("noresults")?></p>
            <?php
          } else {
            $count = json_decode($results["results"], true);

            $array = array();
            $colors = array();
            foreach ($count as $id => $amount) {
              $ballot_q = mysqli_query($con, "SELECT name, color FROM voting_ballots WHERE id = ".(int)$id);

              if (!mysqli_num_rows($ballot_q)) {
                die("The database is corrupted.");
              }

              $row_q = mysqli_fetch_assoc($ballot_q);

              if ($row["maxvotingballots"] == 1) {
                $array[] = "['".$row_q["name"]."', $amount]";
                $colors[] = "'#".$row_q["color"]."'";
              } else {
                $array[] = "['".$row_q["name"]."', $amount, '#".$row_q["color"]."']";
              }
            }
            ?>
            <p><a href="download.php?well=voting&id=<?=$row["id"]?>" class="mdl-button mdl-js-button mdl-js-ripple-effect mdl-button--accent mdl-button--raised" download>Download all ballots</a></p>
            <div id="chart" style="max-width: 100%!important; width: 500px; height: 300px;"></div>
            <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
            <?php
            if ($row["maxvotingballots"] == 1) {
              ?>
              <script type="text/javascript">
                google.charts.load('current', {'packages':['corechart']});
                google.charts.setOnLoadCallback(drawChart);
                function drawChart() {

                  var data = google.visualization.arrayToDataTable([
                    ['Ballot', 'Count'],<?=implode(",", $array);?>
                  ]);

                  var options = {
                    title: 'Vote count',
                    colors: [<?=implode(",", $colors)?>]
                  };

                  var chart = new google.visualization.PieChart(document.getElementById('chart'));

                  chart.draw(data, options);
                }
              </script>
              <?php
            } else {
              ?>
              <script type="text/javascript">
                google.charts.load('current', {'packages':['corechart', 'bar']});
                google.charts.setOnLoadCallback(drawChart);
                function drawChart() {
                  var data = google.visualization.arrayToDataTable([
                    ['Ballot', 'Count', { role: 'style' }],<?=implode(",", $array)?>
                  ]);

                  var options = {
                    chart: {
                      title: 'Vote count'
                    },
                    bars: 'vertical' // Required for Material Bar Charts.
                  };

                  var chart = new google.visualization.BarChart(document.getElementById('chart'));

                  chart.draw(data, options);
                }
              </script>
              <?php
            }
          }
          ?>
        </div>
      </div>
    </main>
  </div>
  <?php
  if ($canvote !== true) {
    ?>
    <div class="mdl-tooltip" for="votebtn" data-votestatus="<?=$votestatus?>"><?=$i18n->msg("votestatus_".abs($votestatus))?></div>
    <?php
  }

  md::msg(array("votestatus_0", "votestatus_1", "votestatus_2", "votestatus_3", "votestatus_4", "votestatus_5"));
  ?>
</body>
</html>
