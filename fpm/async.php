<?php
  $cmd = "php transfer.php > /dev/null 2>&1 &";
  passthru($cmd);
  echo "I GOT IT\n";
?>