<?php

namespace AU\ViewsCounter;

$entity = elgg_extract('entity', $vars);

if ($entity->add_views_counter) {
	$add_views_counter_values = unserialize($entity->add_views_counter);
} else {
	$add_views_counter_values = array();
}


if ($entity->add_views_logger) {
	$add_views_logger_values = unserialize($entity->add_views_logger);
} else {
	$add_views_logger_values = array();
}


// Get the valid types that the views counter may be added on
$valid_types = get_valid_types_for_views_counter();
?>

<div class="elgg-field">
	<label class="elgg-field-label"><?php echo elgg_echo('views_counter:add_views_counter'); ?></label>
	<table class="elgg-table-alt">
		<thead>
			<tr>
				<th><?= elgg_echo('views_counter:entity_type') ?></th>
				<th><?= elgg_echo('views_counter:enable_counter') ?></th>
				<th><?= elgg_echo('views_counter:enable_logger') ?></th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ($valid_types as $entity_type) { ?>
				<tr>
					<td>
						<?php
						if (in_array($entity_type, ['user', 'group'])) {
							echo elgg_echo("item:$entity_type");
						} else {
							echo elgg_echo("item:object:$entity_type");
						}
						?>
					</td>

					<td>
						<?php
						echo elgg_view('input/checkbox', [
							'default' => false,
							'value' => $entity_type,
							'name' => 'params[add_views_counter][]',
							'checked' => in_array($entity_type, $add_views_counter_values),
						]);
						?>
					</td>

					<td>
						<?php
						echo elgg_view('input/checkbox', [
							'default' => false,
							'value' => $entity_type,
							'name' => 'params[add_views_logger][]',
							'checked' => in_array($entity_type, $add_views_logger_values),
						]);
						?>
					</td>
				</tr>
				<?php
			}
			?>
		</tbody>
	</table>
</div>

<?php
echo elgg_view_field([
	'#type' => 'radio',
	'#label' => elgg_echo('views_counter:display_views_counter'),
	'name' => 'params[display_views_counter]',
	'value' => $entity->display_views_counter ? $entity->display_views_counter : 'yes',
	'options' => array(
		elgg_echo('option:yes') => 'yes',
		elgg_echo('views_counter:no') => 'no',
	),
]);