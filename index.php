<?php
include_once('requestFilter.php');

$anrufbeantworter = array();
$i = 1;
while(array_key_exists('vm'.$i, $_request))
{
	$_request['vm'.$i] = trim($_request['vm'.$i]);
	if(!$_request['vm'.$i]) continue;
	$anrufbeantworter[$i - 1] = $_request['vm'.$i];
	$i++;
}
?><!DOCTYPE html>
<html lang="de">
  <head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Anrufübersicht</title>

    <link href="assets/bootstrap/css/bootstrap.min.css" rel="stylesheet" />
	<link href="assets/css/style.css" rel="stylesheet" />
	<link href="assets/css/uebersicht.css" rel="stylesheet" />
	<link href="assets/css/anrufbeantworter.css" rel="stylesheet" />
  </head>
  <body>
	<nav class="navbar navbar-inverse navbar-fixed-top">
		<div class="container">
			<div class="navbar-header">
				<button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
					<span class="sr-only">Menü</span>
					<span class="icon-bar"></span>
					<span class="icon-bar"></span>
					<span class="icon-bar"></span>
				</button>
			</div>
			<div id="navbar" class="collapse navbar-collapse">
				<ul class="nav navbar-nav">
					<li class="active"><a id="menue-uebersicht" href="#">Anrufübersicht</a></li>
					<li><a id="menue-anrufbeantworter" href="#">Anrufbeantworter</a></li>
				</ul>
			</div>
		</div>
	</nav>

	<div id="anrufuebersicht" class="container">
		<div class="row">
			<div id="uebersicht" class="col-md-6">
				<div class="panel panel-default">
					<div class="panel-heading">Anrufe</div>
					<div class="panel-body">
						<ul id="anrufliste" class="panel-list">
						</ul>
					</div>
				</div>
			</div>
			<div id="telefonbuch" class="col-md-6">
				<div class="panel panel-default">
					<div class="panel-heading">Namen suchen<button id="telefonbucheintragHinzufuegenButton" class="btn btn-xs btn-default" title="Telefonbucheintrag hinzufügen" data-toggle="modal" data-target="#telefonbucheintragHinzufuegen"><span class="glyphicon glyphicon-plus"></span></button><input id="suche" style="float: right" type="text"></input></div>
					<div class="panel-body">
						<ul id="namensliste" class="panel-list">
						</ul>
					</div>
				</div>
			</div>
		</div>
	</div>
	
	<div id="anrufbeantworter" class="container">
		<div class="audio"><audio id="anrufbeantworterPlayer" controls></audio></div>
		<?php if(count($anrufbeantworter) > 0) { ?>
			<ul class="nav nav-tabs nav-justified">
				<?php
					for($i = 0; $i < count($anrufbeantworter); $i++)
					{
						print '<li data-index="'.$i.'" role="presentation"'.($i == 0 ? ' class="active"' : '').'><a href="#vm'.$i.'" aria-controls="vm'.$i.'" role="tab" class="anrufbeantworter-tab-link" data-toggle="tab"><div class="anrufbeantworter-tab-caption">'.$anrufbeantworter[$i].'</div> <span class="badge" id="vm-badge'.$i.'"></span></a></li>';
					}
				?>
			</ul>
			<div class="tab-content">
				<?php
					for($i = 0; $i < count($anrufbeantworter); $i++)
					{
						print '<div role="tabpanel" data-name="'.$anrufbeantworter[$i].'" class="tab-pane anrufbeantworter-tab'.($i == 0 ? ' active' : '').'" id="vm'.$i.'"><ul></ul></div>';
					}
				?>
			</div>
		<?php } ?>
	</div>
	
	<div class="modal fade" id="telefonbucheintragHinzufuegen" tabindex="-1" role="dialog" aria-labelledby="telefonbucheintragHinzufuegenLabel" aria-hidden="true">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
					<h4 class="modal-title" id="telefonbucheintragHinzufuegenLabel"></h4>
				</div>
				<div class="modal-body">
					<div class="modal-error alert alert-danger" role="alert" id="telefonbucheintragHinzufuegenFehler">&nbsp;</div>
					<form class="form-horizontal">
						<div class="form-group">
							<label for="inputTelefonbucheintragHinzufuegenName" class="col-sm-2 control-label">Name</label>
							<div class="col-sm-10">
								<input type="text" class="form-control" id="inputTelefonbucheintragHinzufuegenName" placeholder="Name">
							</div>
						</div>
						<div class="form-group">
							<label for="inputTelefonbucheintragHinzufuegenNummer" class="col-sm-2 control-label">Nummer</label>
							<div class="col-sm-10">
								<input type="text" class="form-control" id="inputTelefonbucheintragHinzufuegenNummer" placeholder="Telefonnummer">
							</div>
						</div>
					</form>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-default" data-dismiss="modal">Schließen</button>
					<button type="button" id="telefonbucheintragHinzufuegenSpeichern" class="btn btn-primary">Speichern</button>
				</div>
			</div>
		</div>
	</div>
	
	<div class="modal fade" id="telefonbucheintragHinzufuegen2" tabindex="-1" role="dialog" aria-labelledby="telefonbucheintragHinzufuegen2Label" aria-hidden="true">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
					<h4 class="modal-title" id="telefonbucheintragHinzufuegen2Label"></h4>
				</div>
				<div class="modal-body">
					<div class="modal-error alert alert-danger" role="alert" id="telefonbucheintragHinzufuegen2Fehler">&nbsp;</div>
					<form class="form-horizontal">
						<div class="form-group">
							<label for="inputTelefonbucheintragHinzufuegen2Name" class="col-sm-2 control-label">Name</label>
							<div class="col-sm-10">
								<input type="text" class="form-control" id="inputTelefonbucheintragHinzufuegen2Name" placeholder="Name">
							</div>
						</div>
						<div class="form-group">
							<label for="inputTelefonbucheintragHinzufuegen2Nummer" class="col-sm-2 control-label">Nummer</label>
							<div class="col-sm-10">
								<p class="form-control-static" id="inputTelefonbucheintragHinzufuegen2Nummer"></p>
							</div>
						</div>
					</form>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-default" data-dismiss="modal">Schließen</button>
					<button type="button" id="telefonbucheintragHinzufuegen2Speichern" class="btn btn-primary">Speichern</button>
				</div>
			</div>
		</div>
	</div>
	
	<div class="modal fade" id="telefonbucheintragBearbeiten" tabindex="-1" role="dialog" aria-labelledby="telefonbucheintragBearbeitenLabel" aria-hidden="true">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
					<h4 class="modal-title" id="telefonbucheintragBearbeitenLabel"></h4>
				</div>
				<div class="modal-body">
					<div class="modal-error alert alert-danger" role="alert" id="telefonbucheintragBearbeitenFehler">&nbsp;</div>
					<form class="form-horizontal">
						<div class="form-group">
							<label for="inputTelefonbucheintragBearbeitenName" class="col-sm-2 control-label">Name</label>
							<div class="col-sm-10">
								<p class="form-control-static" id="inputTelefonbucheintragBearbeitenName"></p>
							</div>
						</div>
						<div class="form-group">
							<label for="inputTelefonbucheintragBearbeitenNummer" class="col-sm-2 control-label">Nummer</label>
							<div class="col-sm-10">
								<input type="text" class="form-control" id="inputTelefonbucheintragBearbeitenNummer" placeholder="Telefonnummer">
							</div>
						</div>
					</form>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-default" data-dismiss="modal">Schließen</button>
					<button type="button" id="telefonbucheintragBearbeitenSpeichern" class="btn btn-primary">Speichern</button>
				</div>
			</div>
		</div>
	</div>
	
	<div class="modal fade" id="telefonbucheintragBearbeiten2" tabindex="-1" role="dialog" aria-labelledby="telefonbucheintragBearbeiten2Label" aria-hidden="true">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
					<h4 class="modal-title" id="telefonbucheintragBearbeiten2Label"></h4>
				</div>
				<div class="modal-body">
					<div class="modal-error alert alert-danger" role="alert" id="telefonbucheintragBearbeiten2Fehler">&nbsp;</div>
					<form class="form-horizontal">
						<div class="form-group">
							<label for="inputTelefonbucheintragBearbeiten2Name" class="col-sm-2 control-label">Name</label>
							<div class="col-sm-10">
								<input type="text" class="form-control" id="inputTelefonbucheintragBearbeiten2Name" placeholder="Name"></p>
							</div>
						</div>
						<div class="form-group">
							<label for="inputTelefonbucheintragBearbeiten2Nummer" class="col-sm-2 control-label">Nummer</label>
							<div class="col-sm-10">
								<p class="form-control-static" id="inputTelefonbucheintragBearbeiten2Nummer"></p>
							</div>
						</div>
					</form>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-default" data-dismiss="modal">Schließen</button>
					<button type="button" id="telefonbucheintragBearbeiten2Speichern" class="btn btn-primary">Speichern</button>
				</div>
			</div>
		</div>
	</div>
	
	<script src="assets/js/jquery-2.1.4.min.js"></script>
    <script src="assets/bootstrap/js/bootstrap.min.js"></script>
	<script src="assets/js/uebersicht.js"></script>
	<script src="assets/js/anrufbeantworter.js"></script>
	<script>
		$('#menue-uebersicht').click(function() {
			if($('#anrufbeantworter').is(':visible')) {
				$('#anrufbeantworter').fadeOut(300, function() {
					$('#anrufuebersicht').fadeIn();
					$('#menue-anrufbeantworter').parent().removeClass('active');
					$('#menue-uebersicht').parent().addClass('active');
				});
			}
		});
		
		$('#menue-anrufbeantworter').click(function() {
			if($('#anrufuebersicht').is(':visible')) {
				$('#anrufuebersicht').fadeOut(300, function() {
					$('#anrufbeantworter').fadeIn();
					$('#menue-uebersicht').parent().removeClass('active');
					$('#menue-anrufbeantworter').parent().addClass('active');
				});
			}
		});
	</script>
  </body>
</html>