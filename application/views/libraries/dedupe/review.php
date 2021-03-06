<script>
var libraryid = <?=$library['libraryid']?>;
$(function() {
	$(document)
		.on('click', '.duplicate table td.selectable', function() {
			$(this).closest('tr').find('td').removeClass('selected');
			$(this).addClass('selected');
		})
		.on('click', '.duplicate [data-action=dupe-save]', function(event) {
			event.preventDefault();
			var dupe = $(this).closest('.duplicate');
			$.actionDupe({action: 'save', left: dupe.data('referenceid'), right: dupe.data('referenceid2')});
			dupe.slideUp('slow', function() {
				dupe.remove();
			});
		})
		.on('click', '.duplicate [data-action=dupe-delete]', function(event) {
			event.preventDefault();
			var dupe = $(this).closest('.duplicate');
			$.actionDupe({action: 'delete', left: dupe.data('referenceid'), right: dupe.data('referenceid2')});
			dupe.slideUp('slow', function() {
				dupe.remove();
			});
		})
		.on('click', '.duplicate [data-action=dupe-break]', function(event) {
			event.preventDefault();
			var dupe = $(this).closest('.duplicate');
			$.actionDupe({action: 'break', left: dupe.data('referenceid'), right: dupe.data('referenceid2')});
			dupe.slideUp('slow', function() {
				dupe.remove();
			});
		});

	/**
	* Send a command to the de-duplicator
	* @param hash data The hash to send via POST
	* @param bool refresh Whether to redraw the form on success
	*/
	$.actionDupe = function(data, refresh) {
		$.ajax({
			url: '<?=SITE_ROOT?>api/libraries/dupeaction',
			data: data,
			type: 'POST',
			dataType: 'json',
			success: function(json) {
				if (json.header.status == 'ok') {
					if (refresh)
						$('#dupes-outer').load($('#dupes-outer').data('url') + ' #dupes-inner');
				} else if (json.header.error) {
					alert(json.header.error);
				} else
					alert('An unknown error occured');
			},
			error: function(e, err) {
				alert('An error occured: ' + err);
			}
		});
	};
});
</script>
<style>
.duplicate table {
	width: 100%;
}
.duplicate table td.selectable {
	padding: 10px;
	word-break: break-all;
}
.duplicate table td.selected {
	color: #468847 !important;
	background: #dff0d8 !important;
}
</style>
<legend>
	De-duplication review
	<div class="btn-group pull-right">
		<a class="btn dropdown-toggle" data-toggle="dropdown" href="#">
			<i class="icon-cog"></i> <span class="caret"></span>
		</a>
		<ul class="dropdown-menu">
			<li><a href="<?=SITE_ROOT?>libraries/export/<?=$library['libraryid']?>"><i class="icon-cloud-download"></i> Export references</a></li>
			<li class="divider"></li>
			<li><a href="<?=SITE_ROOT?>libraries/share/<?=$library['libraryid']?>"><i class="icon-share-alt"></i> Share library</a></li>
			<li class="divider"></li>
			<li><a href="<?=SITE_ROOT?>libraries/dedupe/<?=$library['libraryid']?>/force"><i class="icon-resize-small"></i> Force reprocessing</a></li>
			<li><a href="<?=SITE_ROOT?>libraries/finish/<?=$library['libraryid']?>/force"><i class="icon-remove"></i> Cancel de-duplication</a></li>
		</ul>
	</div>
</legend>

<div id="dupes-outer" data-url="<?=SITE_ROOT?>libraries/dedupe/<?=$library['libraryid']?>"><div id="dupes-inner">

<div class="infobox-container">
	<div class="infobox infobox-green infobox-medium infobox-dark">
		<div class="infobox-icon">
			<i class="icon-tag"></i>
		</div>

		<div class="infobox-data">
			<div class="infobox-content">References</div>
			<div class="infobox-content"><?=$this->Format->Number($this->Reference->Count(array('libraryid' => $library['libraryid'])))?></div>
		</div>
	</div>

	<div class="infobox infobox-blue infobox-medium infobox-dark">
		<div class="infobox-icon">
			<i class="icon-resize-small"></i>
		</div>

		<div class="infobox-data">
			<div class="infobox-content">Duplicates</div>
			<div class="infobox-content"><?=$markeddupes = $this->Format->Number($this->Reference->Count(array('libraryid' => $library['libraryid'], 'status' => 'dupe')))?></div>
		</div>
	</div>

	<div class="infobox infobox-grey infobox-medium infobox-dark">
		<div class="infobox-icon">
			<i class="icon-trash"></i>
		</div>

		<div class="infobox-data">
			<div class="infobox-content">Deleted</div>
			<div class="infobox-content"><?=$this->Format->Number($this->Reference->Count(array('libraryid' => $library['libraryid'], 'status' => 'deleted')))?></div>
		</div>
	</div>
</div>

<? if ($library['debug'] == 'active') { ?>
<hr/>

<div class="infobox-container">
	<div class="infobox infobox-green infobox-medium infobox-dark">
		<div class="infobox-icon">
			<i class="icon-ok-sign"></i>
		</div>

		<div class="infobox-data">
			<div class="infobox-content">Marked as OK</div>
			<div class="infobox-content">
				<?=$this->Format->Number($this->Reference->Count(array('libraryid' => $library['libraryid'], 'label' => 'OK')))?>
			</div>
		</div>
	</div>
	<div class="infobox infobox-blue infobox-medium infobox-dark">
		<div class="infobox-icon">
			<i class="icon-remove-sign"></i>
		</div>

		<div class="infobox-data">
			<div class="infobox-content">Marked as DUPE</div>
			<div class="infobox-content"><?=$debugdupes = $this->Format->Number($this->Reference->Count(array('libraryid' => $library['libraryid'], 'label' => 'DUPE')))?></div>
		</div>
	</div>
	<div class="infobox infobox-orange infobox-large infobox-dark" data-tip="Should be Dupe, Marked as OK">
		<div class="infobox-icon">
			<i class="icon-warning-sign"></i>
		</div>
		<div class="infobox-data">
			<div class="infobox-content">False Negatives</div>
			<div class="infobox-content"><?=$this->Format->Number($this->Reference->Count(array('libraryid' => $library['libraryid'], 'label' => 'DUPE', 'status' => 'active')))?></div>
		</div>
	</div>
	<div class="infobox infobox-red infobox-large infobox-dark" data-tip="Should be OK, Marked as Dupe">
		<div class="infobox-icon">
			<i class="icon-exclamation-sign"></i>
		</div>
		<div class="infobox-data">
			<div class="infobox-content">False Positives</div>
			<div class="infobox-content"><?=$this->Format->Number($this->Reference->Count(array('libraryid' => $library['libraryid'], 'label' => 'OK', 'status' => 'dupe')))?></div>
		</div>
	</div>
</div>
<div class="infobox-container">
	<div class="infobox infobox-green infobox-large infobox-dark">
		<div class="infobox-icon">
			<i class="icon-trophy"></i>
		</div>
		<div class="infobox-data">
			<div class="infobox-content">Overall success</div>
			<div class="infobox-content"><?=$this->Format->Percent($markeddupes / $debugdupes)?>%</div>
		</div>
	</div>
</div>
<? } ?>

<hr/>

<? foreach ($dupes as $ref) {
	if ($ref['data'])
		$ref = array_merge($ref, json_decode($ref['data'], TRUE));
	$alts = json_decode($ref['altdata'], TRUE);

	$altrefs = array();
	foreach($alts as $key => $vals) 
		foreach ($vals as $refid => $data)
			$altrefs[$refid] = 1;
?>
<div class="duplicate" data-referenceid="<?=$ref['referenceid']?>" data-referenceid2="<?=implode(',', array_keys($altrefs))?>">
	<legend>
		<?=$ref['title']?>
		<div class="btn-group pull-right">
			<a class="btn dropdown-toggle" data-toggle="dropdown" href="#">
				<i class="icon-tag"></i> <span class="caret"></span>
			</a>
			<ul class="dropdown-menu">
				<li><a href="<?=SITE_ROOT?>references/edit/<?=$ref['referenceid']?>"><i class="icon-pencil"></i> Edit reference </a></li>
				<li class="divider"></li>
				<li><a href="<?=SITE_ROOT?>references/delete/<?=$ref['referenceid']?>"><i class="icon-trash"></i> Delete reference</a></li>
			</ul>
		</div>
	</legend>
	<div class="row-fluid pad-top">
		<table class="table table-bordered table-striped table-hover table-dupes">
			<thead>
				<th>Field</th>
				<th><a href="<?=SITE_ROOT?>references/view/<?=$ref['referenceid']?>">Reference #<?=$ref['referenceid']?></a></th>
				<? foreach ($altrefs as $altrefid => $junk) { ?>
				<th><a href="<?=SITE_ROOT?>references/view/<?=$altrefid?>">Reference #<?=$altrefid?></a></th>
				<? } ?>
			</thead>
			<? foreach ($alts as $field => $val) { ?>
			<tr>
				<th><?=$field?></th>
				<td class="selectable selected"><div><?=$this->Reference->Flatten($ref[$field], "<br/>")?></div></td>
				<? foreach ($altrefs as $altrefid => $junk) { ?>
				<? if (isset($alts[$field][$altrefid])) { ?>
					<td class="selectable"><div><?=$this->Reference->Flatten($alts[$field][$altrefid])?></div></td>
				<? } else { ?>
					<td>&nbsp;</td>
				<? } ?>
				<? } ?>
			</tr>
			<? } ?>
		</table>
		<div class="pull-center pad-bottom">
			<div class="btn-group">
				<a class="btn" href="#" data-action="dupe-save"><i class="icon-ok"></i> Save</a>
				<a class="btn dropdown-toggle" data-toggle="dropdown"><span class="caret"></span></a>
				<ul class="dropdown-menu pull-reset">
					<li><a href="#" data-action="dupe-save"><i class="icon-ok"></i> Save</a></li>
					<li><a href="#" data-action="dupe-break"><i class="icon-remove"></i> Mark as non-duplicates</a></li>
					<li><a href="#" data-action="dupe-delete"><i class="icon-trash"></i> Delete both references</a></li>
				</ul>
			</div>
		</div>
	</div>
</div>
<? } ?>
</div></div>
<div class="alert alert-info">
	<h3><i class="icon-smile"></i> End of duplicate list</h3>
	<p>There are now more duplicates to review - yey!</p>
	<div class="pull-center">
		<a href="<?=SITE_ROOT?>libraries/finish/<?=$library['libraryid']?>" class="btn"><i class="icon-tags"></i> View <?=$library['title']?></a>
	</div>
</div>
