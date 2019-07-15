<?php
/*  Mailadminscript
    Copyright (C) 2019  Paul Schürholz contact AT roteserver . de

    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>. */
$config = parse_ini_file('../../private/config.ini');
if (TRUE) {
    session_start();
    $captchacode = rand(10000, 99999);
    $_SESSION['captchacode'] = $captchacode;
    $captcha = shell_exec('sh ../../private/captcha.sh ' . $captchacode);
    header('Content-type: image/png');
    echo $captcha;
    exit;
} else {
    header("Location: ../index.php");
}
?>