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
$config = parse_ini_file('../private/config.ini');
session_start();
try {
    $dbh = new PDO('mysql:host=' . $config['dbservername'] . ';dbname=' . $config['dbname'], $config['dbusername'], $config['dbpassword'], array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
} catch (PDOException $e) {
    //echo 'Connection failled: '. $e->getMessage(); // Errormessage kann Sicherheitsrelevantes enthalen
    echo 'Connection failed';
}
$user = $_POST['username'];
$pw = $_POST['password'];

$abfrage = "SELECT `id`, `password`, `email`, `username`, `admin` FROM `virtual_users` WHERE `email` = :username AND `active`='1'";
$sth = $dbh->prepare($abfrage);
$sth->execute(array(':username' => $user));
$userdata = $sth->fetchAll();
if ($sth->rowCount() > 0) {
    if (password_verify($pw, $userdata[0]['password'])) {
        $_SESSION['log'] = 1;
        $_SESSION['username'] = $userdata[0]['username'];
        $_SESSION['email'] = $userdata[0]['email'];
        $_SESSION['admin'] = $userdata[0]['admin'];
        $_SESSION['mailID'] = $userdata[0]['id'];
        header("Location: settings.php");
        exit;
    }
}
header("Location: index.php?badlogin=1");
exit;
?>