var anrufbeantworterNamen  = [];
var anrufbeantworter = {};

function sprachnachrichtenLaden(forceLoad) {
	if(anrufbeantworterNamen.length == 0) return;
	$.post('ajax.php',
		{
			method: 'newVoicemailCounts',
			namen: JSON.stringify(anrufbeantworterNamen)
		}, function(data, status) {
			result = JSON.parse(data);
			if(status == 'success')
			{
				for(var i = 0, keys = Object.keys(result), l = keys.length; i < l; i++) {
					var key = keys[i];
					anrufbeantworter[key].neueAnrufeAlt = anrufbeantworter[key].neueAnrufe;
					anrufbeantworter[key].neueAnrufe = result[key];
					if(anrufbeantworter[key].neueAnrufeAlt != anrufbeantworter[key].neueAnrufe) {
						var badge = $('#vm-badge' + i);
						badge.text(anrufbeantworter[key].neueAnrufe);
						if(anrufbeantworter[key].neueAnrufe == 0) badge.fadeOut();
						else badge.fadeIn();
					}
				}
				
				var aktiverReiter = $('#anrufbeantworter ul.nav li.active');
				var aktiverTab = $('#vm' + aktiverReiter.data('index'));
				var aktuellerAnrufbeantworter = aktiverTab.data('name');
				if(forceLoad || anrufbeantworter[aktuellerAnrufbeantworter].neueAnrufeAlt != anrufbeantworter[aktuellerAnrufbeantworter].neueAnrufe) {
					$.post('ajax.php',
						{
							method: 'getVoicemails',
							name: aktuellerAnrufbeantworter
						}, function(data, status) {
							result = JSON.parse(data);
							if(status == 'success')
							{
								var aktiveListe = aktiverTab.children('ul');
								aktiveListe.empty();
								if(result.length == 0) {
									aktiverTab.append('<div class="keine-sprachnachricht"><h3>Keine Sprachnachrichten</h3></div>');
								} else {
									$('.keine-sprachnachricht').remove();
									result.forEach(function(eintrag) {
										if(eintrag.CallerName.length > 35) eintrag.CallerName = eintrag.CallerName.substring(0, 32) + '...';
										aktiveListe.append('<li class="sprachnachricht-element" data-id="' + eintrag.ID + '" data-quelle="' + eintrag.FullPath + '"><div class="first-col' + (eintrag.New === '1' ? ' ungehoert' : '') + '">' + eintrag.Date + '</div><div class="second-col' + (eintrag.New === '1' ? ' ungehoert' : '') + '">' + eintrag.Time + '</div><div class="third-col' + (eintrag.New === '1' ? ' ungehoert' : '') + '">' + eintrag.CallerName + '</div><div class="pull-right">' + (eintrag.New === '1' ? '<button class="btn-sprachnachricht-gehoert" data-state="ungehoert" title="Als gehört markieren" data-id="' + eintrag.ID + '"><span class="glyphicon glyphicon-envelope" aria-hidden="true"></span></button>' : '<button class="btn-sprachnachricht-gehoert" data-state="gehoert" title="Als ungehört markieren" data-id="' + eintrag.ID + '"><span class="glyphicon glyphicon-file" aria-hidden="true"></span></button>') + '<button class="btn-sprachnachricht-loeschen" title="Löschen" data-id="' + eintrag.ID + '"><span class="glyphicon glyphicon-trash" aria-hidden="true"></span></button></div></li>');
									});
									$('.btn-sprachnachricht-loeschen').click(function(event) {
										var id = $(this).data('id');
										if(!id || id.length == 0) return;
										$.post('ajax.php',
											{
												method: 'deleteVoicemail',
												id: id
											}, function(data, status) {
												result = JSON.parse(data);
												if(status == 'success')
												{
													sprachnachrichtenLaden(true);
												}
										});
										event.stopPropagation();
									});
									$('.btn-sprachnachricht-gehoert').click(function(event) {
										var id = $(this).data('id');
										if(!id || id.length == 0) return;
										var button = $(this);
										var element = $(this).parent().parent();
										if(button.data('state') == 'ungehoert') {
											$.post('ajax.php',
												{
													method: 'markHeard',
													id: id
												}, function(data, status) {
													result = JSON.parse(data);
													if(status == 'success')
													{
														button.data('state', 'gehoert');
														button.attr('title', 'Als ungehört markieren');
														button.children('span').removeClass('glyphicon-envelope').addClass('glyphicon-file');
														element.children('div').removeClass('ungehoert');
														var badge = $('#vm-badge' + anrufbeantworter[aktuellerAnrufbeantworter].index);
														var count = badge.text() - 1;
														badge.text(count);
														if(count <= 0) badge.fadeOut();
													}
											});
										} else {
											$.post('ajax.php',
												{
													method: 'markUnheard',
													id: id
												}, function(data, status) {
													result = JSON.parse(data);
													if(status == 'success')
													{
														button.data('state', 'ungehoert');
														button.attr('title', 'Als gehört markieren');
														button.children('span').removeClass('glyphicon-file').addClass('glyphicon-envelope');
														element.children('div').addClass('ungehoert');
														var badge = $('#vm-badge' + anrufbeantworter[aktuellerAnrufbeantworter].index);
														var count = badge.text() - 0 + 1;
														badge.text(count);
														if(count > 0) badge.fadeIn();
													}
											});
										}
										event.stopPropagation();
									});
									$('.sprachnachricht-element').click(function() {
										var element = $(this);
										$('#anrufbeantworterPlayer').trigger('pause').empty().append('<source src="' + element.data('quelle') + '" type="audio/mpeg" />').trigger('load').trigger('play');
										$.post('ajax.php',
											{
												method: 'markHeard',
												id: element.data('id')
											}, function(data, status) {
												result = JSON.parse(data);
												if(status == 'success')
												{
													element.children('div').removeClass('ungehoert');
													var button = element.find('.btn-sprachnachricht-gehoert');
													button.data('state', 'gehoert');
													button.attr('title', 'Als ungehört markieren');
													button.children('span').removeClass('glyphicon-envelope').addClass('glyphicon-file');
													var badge = $('#vm-badge' + anrufbeantworter[aktuellerAnrufbeantworter].index);
													var count = badge.text() - 1;
													badge.text(count);
													if(count <= 0) badge.fadeOut();
												}
										});
									});
								}
							}
					});
				}
			}
	});
}

$(document).ready(function() {
	var index = 0;
	$('.anrufbeantworter-tab').each(function() {
		anrufbeantworterNamen.push($(this).data('name'));
		anrufbeantworter[$(this).data('name')] = {
			neueAnrufeAlt: -1,
			neueAnrufe: -1,
			index: index++
		};
	});
	sprachnachrichtenLaden();
});

$('.anrufbeantworter-tab-link').on('show.bs.tab', function() {
	sprachnachrichtenLaden(true);
});

setInterval(function() {
	if($('#anrufbeantworter').is(':visible')) sprachnachrichtenLaden();
}, 30000);