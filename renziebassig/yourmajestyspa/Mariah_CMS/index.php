<?php
declare(strict_types=1);

// The CMS has no landing page of its own — send visitors to the dashboard,
// which redirects to the sign-in screen if there is no session.
header('Location: admin/', true, 302);
exit;
