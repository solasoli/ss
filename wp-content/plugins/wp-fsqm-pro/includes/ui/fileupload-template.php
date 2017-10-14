<!-- The template to display files available for upload -->
<script class="template-upload" id="<?php echo $this->generate_id_from_name( $name ) . '_tmpl_upload'; ?>" type="text/x-tmpl">
{% for (var i=0, file; file=o.files[i]; i++) { %}
	<tr class="template-upload fade">
		<td>
			<span class="preview"></span>
		</td>
		<td>
			<p class="name">{%=file.name%}</p>
			<strong class="error"></strong>
		</td>
		<td class="fileupload_list_pb">
			<p class="size"><?php echo $labels['processing_singular']; ?></p>
			<div class="progress"></div>
		</td>
		<td colspan="<?php echo ( $settings['can_delete'] == true && false == $settings['minimal_ui'] ? '3' : '1' );  ?>">
			{% if (!i && !o.options.autoUpload) { %}
				<button class="start secondary-button small" disabled><?php echo $labels['start_singular']; ?></button>
			{% } %}
			{% if (!i) { %}
				<button class="cancel secondary-button small"><?php echo $labels['cancel_singular']; ?></button>
			{% } %}
		</td>
	</tr>
{% } %}
</script>
<!-- The template to display files available for download -->
<script class="template-download" id="<?php echo $this->generate_id_from_name( $name ) . '_tmpl_download'; ?>" type="text/x-tmpl">
{%
	window.ipt_fsqm_upload_count_global;
	if ( window.ipt_fsqm_upload_count_global == undefined ) {
		window.ipt_fsqm_upload_count_global = 0;
	}
%}
{% for (var i=0, file; file=o.files[i]; i++) { %}
{% var toggler_check_id = window.ipt_fsqm_upload_count_global++; %}
	<tr class="template-download fade">
		<td class="preview_td" colspan="2">
			<span class="preview">
				{% if (file.thumbnailUrl) { %}
					<a href="{%=file.url%}" title="{%=file.name%}" download="{%=file.name%}"<?php if ( $settings['preview_media'] == true ) : ?> data-gallery<?php endif; ?>><img src="{%=file.thumbnailUrl%}" /></a>
				{% } else if ( file.validAudio ) { %}
					<?php if ( $settings['preview_media'] == true ) : ?>
					<audio controls="controls">
						<source src="{%=file.url%}" type="{%=file.type%}" />
						<?php _e( 'Your browser does not support audio element.', 'ipt_fsqm' ); ?>
					</audio>
					<?php endif; ?>
				{% } else if ( file.validVideo ) { %}
					<?php if ( $settings['preview_media'] == true ) : ?>
					<video controls="controls" height="100" width="200">
						<source src="{%=file.url%}" type="{%=file.type%}" />
						<?php _e( 'Your browser does not support video element.', 'ipt_fsqm' ); ?>
					</video>
					<?php endif; ?>
				{% } %}
			</span>
		</td>
		<td>
			<p class="name">
				<a href="{%=file.url%}" title="{%=file.name%}" download="{%=file.name%}" {%=file.thumbnailUrl?'<?php if ( $settings['preview_media'] == true ) : ?>data-gallery<?php endif; ?>':''%}>{%=file.name%}</a>
			</p>
			{% if (file.error) { %}
				<div><span class="error"><?php echo $labels['error_singular']; ?></span> {%=file.error%}</div>
			{% } %}
			<input type="hidden" data-sayt-exclude name="<?php echo $name_id; ?>" value="{%=file.id%}" />
		</td>
		<td>
			<span class="size">{%=o.formatFileSize(file.size)%}</span>
		</td>
		<?php if ( $settings['can_delete'] ) : ?>
		<td class="delete_button">
			<button class="delete secondary-button small" data-type="{%=file.deleteType%}" data-url="{%=file.deleteUrl%}"{% if (file.deleteWithCredentials) { %} data-xhr-fields='{"withCredentials":true}'{% } %}><?php echo $labels['delete_singular']; ?></button>
		</td>
		<?php if ( false == $settings['minimal_ui'] ) : ?>
			<td class="delete_toggle">
				<div class="ipt_uif_label_column">
					<input type="checkbox" name="delete" value="1" class="toggle ipt_uif_checkbox" id="<?php echo $toggler_id; ?>_files_{%=toggler_check_id%}" />
					<label data-labelcon="&#xe18e;" for="<?php echo $toggler_id; ?>_files_{%=toggler_check_id%}"></label>
				</div>
			</td>
		<?php endif; ?>
		<?php endif; ?>
	</tr>
{% } %}
</script>
