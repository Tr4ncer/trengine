<div class="project_description_header">
	<div>
		<div class="project_description_img">
		<?php
		if (!empty($projectInfo['img'])) {
			Core_Loader::classLoader("Exec_Image");
			?>
			<a href="<?php echo $projectInfo['img']; ?>">
				<?php echo Exec_Image::resize($projectInfo['img'], 128, 128); ?>
			</a>
			<?php
		} else {
			?>
			<img alt="" src="templates/default/project/<?php echo $projectInfo['language']; ?>.png" />
			<?php
		}
		?>
		</div>
		<div class="project_text, description">
			<span><?php echo "<b>" . LANGUAGE_TYPE . "</b>: " . $projectInfo['language']; ?></span>
			<br />
			<br /><?php echo "<b>" . RECORDED_DATE . "</b>: " . $projectInfo['date']; ?>
			<br /><?php echo "<b>" . PERCENT_COMPLETE . "</b>: " . $projectInfo['progress']; ?>%
			<br /><?php echo "<b>" . OFFICIAL_WEBSITE . "</b>: " . (!empty($projectInfo['website']) ? $projectInfo['website'] : TR_ENGINE_URL); ?>
		</div>
	</div>
</div>