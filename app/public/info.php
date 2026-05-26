<?php
echo "<h3>PHP Version: " . phpversion() . "</h3>";
echo "<h3>GD Extension Loaded: " . (extension_loaded('gd') ? 'YES' : 'NO') . "</h3>";
echo "<h3>imagecreatefromstring function exists: " . (function_exists('imagecreatefromstring') ? 'YES' : 'NO') . "</h3>";
phpinfo();
