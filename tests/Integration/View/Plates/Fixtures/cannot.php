<?php if ($this->cannot('admin.users.manage', $user ?? null)): ?>
denied
<?php else: ?>
granted
<?php endif; ?>
