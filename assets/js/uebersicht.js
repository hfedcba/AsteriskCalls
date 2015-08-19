// {{{ Anrufübersicht
	function anrufeLaden() {
		$.post('ajax.php',
			{
				method: 'getCalls'
			}, function(data, status) {
				if(status !== 'success') return;
				data = JSON.parse(data);
				var anrufliste = $('#anrufliste');
				anrufliste.empty();
				data.forEach(function(anruf) {
					if(anruf.FromName.length > 30) anruf.FromName = anruf.FromName.substring(0, 27) + '...';
					if(anruf.ToName.length > 30) anruf.ToName = anruf.ToName.substring(0, 27) + '...';
					anrufliste.append('<li>\
							<div class="first-col">' + anruf.Date + '</div>\
							<div class="second-col">' + anruf.Time + '</div>\
							<div class="third-col">' + ((anruf.FromName == anruf.FromNumber) ? '<a href="#" data-toggle="modal" data-target="#telefonbucheintragHinzufuegen2" data-nummer="' + anruf.FromNumber + '" class="unknown-number">' + anruf.FromNumber + '</a>' : '<a href="#" title="' + anruf.FromNumber + '" data-toggle="modal" data-target="#telefonbucheintragBearbeiten2" data-name="' + anruf.FromName + '" data-nummer="' + anruf.FromNumber + '" class="known-number">' + anruf.FromName + '</a>') + '</div>\
							<div class="fourth-col">' + ((anruf.ToName == anruf.ToNumber) ? '<a href="#" data-toggle="modal" data-target="#telefonbucheintragHinzufuegen2" data-nummer="' + anruf.ToNumber + '" class="unknown-number">' + anruf.ToNumber + '</a>' : '<a href="#" title="' + anruf.ToNumber + '" data-toggle="modal" data-target="#telefonbucheintragBearbeiten2" data-name="' + anruf.FromName + '" data-nummer="' + anruf.FromNumber + '" class="known-number">' + anruf.ToName + '</a>') + '</div>\
							<div class="fifth-col">' + anruf.Duration + 's</div>\
						</li>');
				});
				
		});
	}
	
	$(document).ready(function() {
		anrufeLaden();
	});
// }}}

// {{{ Telefonbuch
	function telefonbucheintraegeLaden() {
		if($('#suche').val().length == 0) return;
		$.post('ajax.php',
			{
				method: 'searchName',
				name: $('#suche').val()
			}, function(data, status) {
				if(status !== 'success') return;
				data = JSON.parse(data);
				var namensliste = $('#namensliste');
				namensliste.empty();
				data.forEach(function(eintrag) {
					if(eintrag.Name.length > 35) eintrag.Name = eintrag.Name.substring(0, 32) + '...';
					namensliste.append('<li><div class="first-col"><span class="glyphicon glyphicon-user" aria-hidden="true"></span>' + eintrag.Name + '</div><div class="second-col"><span class="glyphicon glyphicon-earphone" aria-hidden="true"></span>' + eintrag.Number + '</div><div class="pull-right"><button class="btn-telefonbucheintrag-bearbeiten" data-toggle="modal" data-target="#telefonbucheintragBearbeiten" data-name="' + eintrag.Name + '" data-nummer="' + eintrag.Number + '"><span class="glyphicon glyphicon-pencil" aria-hidden="true"></span></button><button class="btn-telefonbucheintrag-loeschen" data-name="' + eintrag.Name + '"><span class="glyphicon glyphicon-trash" aria-hidden="true"></span></button></div></li>');
				});
				$('.btn-telefonbucheintrag-loeschen').click(function() {
					var name = $(this).data('name');
					if(!name || name.length == 0) return;
					$.post('ajax.php',
						{
							method: 'deleteName',
							name: name
						}, function(data, status) {
							result = JSON.parse(data);
							if(status == 'success')
							{
								anrufeLaden();
								telefonbucheintraegeLaden();
							}
					});
				});
		});
	}

	// {{{ Telefonbucheintrag hinzufügen
	$('#telefonbucheintragHinzufuegen').on('show.bs.modal', function(event) {
		var button = $(event.relatedTarget);
		var modal = $(this);
		modal.find('.modal-title').text('Telefonbucheintrag hinzufügen');
		$('#telefonbucheintragHinzufuegenFehler').hide();
		$('#inputTelefonbucheintragHinzufuegenName').val('');
		$('#inputTelefonbucheintragHinzufuegenNummer').val('');
		var inputNameParent = $('#inputTelefonbucheintragHinzufuegenName').parent().parent();
		inputNameParent.removeClass('has-error');
		inputNameParent.removeClass('has-success');
		var inputNummerParent = $('#inputTelefonbucheintragHinzufuegenNummer').parent().parent();
		inputNummerParent.removeClass('has-error');
		inputNummerParent.removeClass('has-success');
	});

	$('#telefonbucheintragHinzufuegen').on('shown.bs.modal', function () {
		$('#inputTelefonbucheintragHinzufuegenName').focus()
	});

	function telefonbucheintragHinzufuegenSpeichern() {
		var name = $('#inputTelefonbucheintragHinzufuegenName').val().trim();
		var nummer = $('#inputTelefonbucheintragHinzufuegenNummer').val().trim();
		if(!name)
		{
			$('#telefonbucheintragHinzufuegenFehler').text('Bitte geben Sie einen Namen ein.');
			$('#telefonbucheintragHinzufuegenFehler').show();
			return;
		}
		if(!nummer)
		{
			$('#telefonbucheintragHinzufuegenFehler').text('Bitte geben Sie eine Telefonnummer ein.');
			$('#telefonbucheintragHinzufuegenFehler').show();
			return;
		}
		$.post('ajax.php',
			{
				method: 'addName',
				name: name,
				nummer: nummer
			}, function(data, status) {
				result = JSON.parse(data);
				if(status == 'success')
				{
					$('#telefonbucheintragHinzufuegen').modal('hide');
					telefonbucheintraegeLaden();
					anrufeLaden();
				}
		});
	}

	$('#telefonbucheintragHinzufuegenSpeichern').click(function() {
		telefonbucheintragHinzufuegenSpeichern();
	});

	$('#telefonbucheintragHinzufuegen').keypress(function(event) {
		if(event.keyCode == 13) telefonbucheintragHinzufuegenSpeichern();
	});

	$('#inputTelefonbucheintragHinzufuegenName').blur(function() {
		var name = $('#inputTelefonbucheintragHinzufuegenName').val().trim();
		var parent = $('#inputTelefonbucheintragHinzufuegenName').parent().parent();
		if(!name || name.length == 0)
		{
			parent.removeClass('has-success');
			parent.addClass('has-error');
		}
		else
		{
			parent.removeClass('has-error');
			parent.addClass('has-success');
		}
	});

	$('#inputTelefonbucheintragHinzufuegenNummer').blur(function() {
		var nummer = $('#inputTelefonbucheintragHinzufuegenNummer').val().trim();
		var parent = $('#inputTelefonbucheintragHinzufuegenNummer').parent().parent();
		if(!nummer || nummer.length == 0)
		{
			parent.removeClass('has-success');
			parent.addClass('has-error');
		}
		else
		{
			parent.removeClass('has-error');
			parent.addClass('has-success');
		}
	});
	// }}}
	
	// {{{ Telefonbucheintrag hinzufügen 2
	$('#telefonbucheintragHinzufuegen2').on('show.bs.modal', function(event) {
		var button = $(event.relatedTarget);
		var nummer = button.data('nummer');
		var modal = $(this);
		modal.find('.modal-title').text('Telefonbucheintrag hinzufügen');
		$('#telefonbucheintragHinzufuegen2Fehler').hide();
		$('#inputTelefonbucheintragHinzufuegen2Name').val('');
		$('#inputTelefonbucheintragHinzufuegen2Nummer').text(nummer);
		var inputNameParent = $('#inputTelefonbucheintragHinzufuegen2Name').parent().parent();
		inputNameParent.removeClass('has-error');
		inputNameParent.removeClass('has-success');
		var inputNummerParent = $('#inputTelefonbucheintragHinzufuegen2Nummer').parent().parent();
		inputNummerParent.removeClass('has-error');
		inputNummerParent.removeClass('has-success');
	});

	$('#telefonbucheintragHinzufuegen2').on('shown.bs.modal', function () {
		$('#inputTelefonbucheintragHinzufuegen2Name').focus()
	});

	function telefonbucheintragHinzufuegen2Speichern() {
		var name = $('#inputTelefonbucheintragHinzufuegen2Name').val().trim();
		var nummer = $('#inputTelefonbucheintragHinzufuegen2Nummer').text().trim();
		if(!name)
		{
			$('#telefonbucheintragHinzufuegen2Fehler').text('Bitte geben Sie einen Namen ein.');
			$('#telefonbucheintragHinzufuegen2Fehler').show();
			return;
		}
		if(!nummer)
		{
			$('#telefonbucheintragHinzufuegen2Fehler').text('Bitte geben Sie eine Telefonnummer ein.');
			$('#telefonbucheintragHinzufuegen2Fehler').show();
			return;
		}
		$.post('ajax.php',
			{
				method: 'addName',
				name: name,
				nummer: nummer
			}, function(data, status) {
				result = JSON.parse(data);
				if(status == 'success')
				{
					$('#telefonbucheintragHinzufuegen2').modal('hide');
					anrufeLaden();
					telefonbucheintraegeLaden();
				}
		});
	}

	$('#telefonbucheintragHinzufuegen2Speichern').click(function() {
		telefonbucheintragHinzufuegen2Speichern();
	});

	$('#telefonbucheintragHinzufuegen2').keypress(function(event) {
		if(event.keyCode == 13) telefonbucheintragHinzufuegen2Speichern();
	});

	$('#inputTelefonbucheintragHinzufuegen2Name').blur(function() {
		var name = $('#inputTelefonbucheintragHinzufuegen2Name').val().trim();
		var parent = $('#inputTelefonbucheintragHinzufuegen2Name').parent().parent();
		if(!name || name.length == 0)
		{
			parent.removeClass('has-success');
			parent.addClass('has-error');
		}
		else
		{
			parent.removeClass('has-error');
			parent.addClass('has-success');
		}
	});
	// }}}

	// {{{ Telefonbucheintrag bearbeiten
	$('#telefonbucheintragBearbeiten').on('show.bs.modal', function(event) {
		var button = $(event.relatedTarget);
		var name = button.data('name');
		var nummer = button.data('nummer');
		var modal = $(this);
		modal.find('.modal-title').text('Telefonbucheintrag bearbeiten');
		$('#telefonbucheintragBearbeitenFehler').hide();
		$('#inputTelefonbucheintragBearbeitenName').text(name);
		$('#inputTelefonbucheintragBearbeitenNummer').val(nummer);
		var inputNummerParent = $('#inputTelefonbucheintragBearbeitenNummer').parent().parent();
		inputNummerParent.removeClass('has-error');
		inputNummerParent.removeClass('has-success');
	});

	$('#telefonbucheintragBearbeiten').on('shown.bs.modal', function () {
		$('#inputTelefonbucheintragBearbeitenNummer').focus()
	});

	function telefonbucheintragBearbeitenSpeichern() {
		var name = $('#inputTelefonbucheintragBearbeitenName').text().trim();
		var nummer = $('#inputTelefonbucheintragBearbeitenNummer').val().trim();
		if(!name)
		{
			$('#telefonbucheintragBearbeitenFehler').text('Bitte geben Sie einen Namen ein.');
			$('#telefonbucheintragBearbeitenFehler').show();
			return;
		}
		if(!nummer)
		{
			$('#telefonbucheintragBearbeitenFehler').text('Bitte geben Sie eine Telefonnummer ein.');
			$('#telefonbucheintragBearbeitenFehler').show();
			return;
		}
		$.post('ajax.php',
			{
				method: 'editName',
				name: name,
				nummer: nummer
			}, function(data, status) {
				result = JSON.parse(data);
				if(status == 'success')
				{
					$('#telefonbucheintragBearbeiten').modal('hide');
					telefonbucheintraegeLaden();
					anrufeLaden();
				}
		});
	}

	$('#telefonbucheintragBearbeitenSpeichern').click(function() {
		telefonbucheintragBearbeitenSpeichern();
	});

	$('#telefonbucheintragBearbeiten').keypress(function(event) {
		if(event.keyCode == 13) telefonbucheintragBearbeitenSpeichern();
	});

	$('#inputTelefonbucheintragBearbeitenNummer').blur(function() {
		var nummer = $('#inputTelefonbucheintragBearbeitenNummer').val().trim();
		var parent = $('#inputTelefonbucheintragBearbeitenNummer').parent().parent();
		if(!nummer || nummer.length == 0)
		{
			parent.removeClass('has-success');
			parent.addClass('has-error');
		}
		else
		{
			parent.removeClass('has-error');
			parent.addClass('has-success');
		}
	});
	// }}}
	
	// {{{ Telefonbucheintrag bearbeiten 2
	$('#telefonbucheintragBearbeiten2').on('show.bs.modal', function(event) {
		var button = $(event.relatedTarget);
		var name = button.data('name');
		var nummer = button.data('nummer');
		var modal = $(this);
		modal.find('.modal-title').text('Telefonbucheintrag bearbeiten');
		$('#telefonbucheintragBearbeiten2Fehler').hide();
		$('#inputTelefonbucheintragBearbeiten2Name').val(name);
		$('#inputTelefonbucheintragBearbeiten2Nummer').text(nummer);
		var inputNameParent = $('#inputTelefonbucheintragBearbeiten2Name').parent().parent();
		inputNameParent.removeClass('has-error');
		inputNameParent.removeClass('has-success');
		var inputNummerParent = $('#inputTelefonbucheintragBearbeiten2Nummer').parent().parent();
		inputNummerParent.removeClass('has-error');
		inputNummerParent.removeClass('has-success');
	});

	$('#telefonbucheintragBearbeiten2').on('shown.bs.modal', function () {
		$('#inputTelefonbucheintragBearbeiten2Name').focus()
	});

	function telefonbucheintragBearbeiten2Speichern() {
		var name = $('#inputTelefonbucheintragBearbeiten2Name').val().trim();
		var nummer = $('#inputTelefonbucheintragBearbeiten2Nummer').text().trim();
		if(!name)
		{
			$('#telefonbucheintragBearbeiten2Fehler').text('Bitte geben Sie einen Namen ein.');
			$('#telefonbucheintragBearbeiten2Fehler').show();
			return;
		}
		if(!nummer)
		{
			$('#telefonbucheintragBearbeiten2Fehler').text('Bitte geben Sie eine Telefonnummer ein.');
			$('#telefonbucheintragBearbeiten2Fehler').show();
			return;
		}
		$.post('ajax.php',
			{
				method: 'editNumber',
				name: name,
				nummer: nummer
			}, function(data, status) {
				result = JSON.parse(data);
				if(status == 'success')
				{
					$('#telefonbucheintragBearbeiten2').modal('hide');
					anrufeLaden();
					telefonbucheintraegeLaden();
				}
		});
	}

	$('#telefonbucheintragBearbeiten2Speichern').click(function() {
		telefonbucheintragBearbeiten2Speichern();
	});

	$('#telefonbucheintragBearbeiten2').keypress(function(event) {
		if(event.keyCode == 13) telefonbucheintragBearbeiten2Speichern();
	});

	$('#inputTelefonbucheintragBearbeiten2Name').blur(function() {
		var name = $('#inputTelefonbucheintragBearbeiten2Name').val().trim();
		var parent = $('#inputTelefonbucheintragBearbeiten2Name').parent().parent();
		if(!name || name.length == 0)
		{
			parent.removeClass('has-success');
			parent.addClass('has-error');
		}
		else
		{
			parent.removeClass('has-error');
			parent.addClass('has-success');
		}
	});
	// }}}

	$(document).ready(function() {
		$('#suche').keyup(function() {
			telefonbucheintraegeLaden();
		});
	});
// }}}

setInterval(function() {
	if($('#anrufuebersicht').is(':visible')) anrufeLaden();
}, 30000);