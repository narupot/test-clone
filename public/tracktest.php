<?php
file_put_contents("tracktest.txt", "OK ".date("Y-m-d H:i:s")."\n", FILE_APPEND);
echo "OK";