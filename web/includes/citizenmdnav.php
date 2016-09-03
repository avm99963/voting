<?php
require_once(__DIR__."/../core.php");
if (citizen::loggedIn()) {
?>
<header class="mdl-layout__header mdl-layout__header--scroll">
  <div class="mdl-layout__header-row">
    <!-- Title -->
    <?php if (isset($md_header_row_before)) { echo $md_header_row_before; } ?>
    <span class="mdl-layout-title"><?=$i18n->msg("citizendashboard")?></span>
    <?php if (isset($md_header_row_more)) { echo $md_header_row_more; } ?>
  </div>
  <?php if (isset($md_header_more)) { echo $md_header_more; } ?>
</header>
<div class="mdl-layout__drawer">
  <span class="mdl-layout-title"><?=$conf["appname"]?></span>
  <nav class="mdl-navigation">
    <a class="mdl-navigation__link" href="dashboard.php"><i class="material-icons">account_balance</i> <?=$i18n->msg("votings")?></a>
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
