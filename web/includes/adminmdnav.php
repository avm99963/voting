<?php
require_once(__DIR__."/../core.php");
if (user::loggedIn()) {
?>
<header class="mdl-layout__header mdl-layout__header--scroll">
  <div class="mdl-layout__header-row">
    <!-- Title -->
    <?php if (isset($md_header_row_before)) { echo $md_header_row_before; } ?>
    <span class="mdl-layout-title"><?=$i18n->msg("dashboard")?></span>
    <?php if (isset($md_header_row_more)) { echo $md_header_row_more; } ?>
  </div>
  <?php if (isset($md_header_more)) { echo $md_header_more; } ?>
</header>
<div class="mdl-layout__drawer">
  <span class="mdl-layout-title"><?=$conf["appname"]?></span>
  <nav class="mdl-navigation">
    <a class="mdl-navigation__link" href="index.php"><i class="material-icons">dashboard</i> <?=$i18n->msg("dashboard")?></a>
    <a class="mdl-navigation__link" href="users.php"><i class="material-icons">group</i> <?=$i18n->msg("users")?></a>
    <a class="mdl-navigation__link" href="votings.php"><i class="material-icons">account_balance</i> <?=$i18n->msg("votings")?></a>
    <a class="mdl-navigation__link" href="census.php"><i class="material-icons">contacts</i> <?=$i18n->msg("census")?></a>
    <a class="mdl-navigation__link" href="results.php"><i class="material-icons">pie_chart</i> <?=$i18n->msg("results")?></a>
    <!--<a class="mdl-navigation__link" href="print.php"><i class="material-icons">print</i> Imprimir horarios</a>
    <a class="mdl-navigation__link" href="configuracion.php"><i class="material-icons">settings</i> Configuración</a>-->
    <a class="mdl-navigation__link" class="mdl-navigation__link" href="logout.php"><i class="material-icons">power_settings_new</i> <?=$i18n->msg("logout")?></a>
  </nav>
</div>
<?php
}
else
{
	header('HTTP/1.0 404 Not Found');
}
?>
