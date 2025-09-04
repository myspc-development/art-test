<?php
/**
 * Role Matrix table markup and toolbar.
 *
 * Expected variables:
 * - $users: array of arrays with 'id' and 'name'.
 * - $roles: associative array of role => label.
 * - $matrix: 2d array [user_id][role] => bool.
 */
?>
<div class="ap-role-matrix">
  <div class="ap-role-toolbar">
    <label class="screen-reader-text" for="ap-role-filter"><?php esc_html_e('Filter users or permissions', 'artpulse'); ?></label>
    <input type="text" id="ap-role-filter" placeholder="<?php esc_attr_e('Filter users or permissions', 'artpulse'); ?>">
    <button type="button" id="ap-role-save" disabled><?php esc_html_e('Save', 'artpulse'); ?></button>
    <span class="ap-unsaved-chip" id="ap-unsaved-chip" hidden><?php esc_html_e('Unsaved changes', 'artpulse'); ?></span>
  </div>
  <div class="ap-role-table-wrap">
    <table class="ap-table" aria-label="<?php esc_attr_e('Role matrix', 'artpulse'); ?>">
      <thead>
        <tr>
          <th scope="col"></th>
<?php if (!empty($roles)) : ?>
<?php foreach ($roles as $role => $label) : ?>
          <th scope="col" id="ap-col-<?php echo esc_attr($role); ?>">
            <span><?php echo esc_html($label); ?></span>
            <button type="button" class="ap-col-toggle" data-col="<?php echo esc_attr($role); ?>" aria-label="<?php printf( esc_attr__( 'Toggle %1$s column', 'artpulse' ), esc_attr( $label ) ); ?>">&#x21C5;</button>
          </th>
<?php endforeach; ?>
<?php endif; ?>
        </tr>
      </thead>
      <tbody>
<?php if (!empty($users)) : ?>
<?php foreach ($users as $user) : ?>
        <tr data-user-id="<?php echo esc_attr($user['id']); ?>">
          <th scope="row" id="ap-row-<?php echo esc_attr($user['id']); ?>">
            <span><?php echo esc_html($user['name']); ?></span>
            <button type="button" class="ap-row-toggle" data-row="<?php echo esc_attr($user['id']); ?>" aria-label="<?php printf( esc_attr__( 'Toggle %1$s row', 'artpulse' ), esc_attr( $user['name'] ) ); ?>">&#x21C4;</button>
          </th>
<?php foreach ($roles as $role => $label) : ?>
          <td>
            <input type="checkbox" class="ap-role-toggle" data-user-id="<?php echo esc_attr($user['id']); ?>" data-role="<?php echo esc_attr($role); ?>" data-original="<?php echo !empty($matrix[$user['id']][$role]) ? '1' : '0'; ?>" <?php checked(!empty($matrix[$user['id']][$role])); ?> aria-describedby="ap-row-<?php echo esc_attr($user['id']); ?> ap-col-<?php echo esc_attr($role); ?>">
          </td>
<?php endforeach; ?>
        </tr>
<?php endforeach; ?>
<?php endif; ?>
      </tbody>
    </table>
  </div>
  <div class="ap-toast" id="ap-role-toast" role="status" aria-live="polite" aria-atomic="true"></div>
</div>
