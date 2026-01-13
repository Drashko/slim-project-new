<?php if ($this->can('admin.access', $user ?? null)): ?>
granted
<?php else: ?>
denied
<?php endif; ?>
