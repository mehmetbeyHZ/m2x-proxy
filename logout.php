<?php
require "vendor/autoload.php";

(new \Networking\Components\Http\Session())->delete('authenticated');
redirect("login.php");