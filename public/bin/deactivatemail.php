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
try {
    $dbh = new PDO('mysql:host=' . $config['dbservername'] . ';dbname=' . $config['dbname'], $config['dbusername'], $config['dbpassword'], array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
} catch (PDOException $e) {
    //echo 'Connection failled: '. $e->getMessage(); // Errormessage kann Sicherheitsrelevantes enthalen
    echo 'Connection failed';
}
session_start();
if ($_SESSION['log'] == 1 && $_SESSION['admin']) {
    $mailuserID = $_POST['mailuserID'];
    $eintrag = "UPDATE `accounts` SET `enabled`='0' WHERE `id` LIKE :mailuserID";
    $sth = $dbh->prepare($eintrag);
    $sth->execute(array(':mailuserID' => $mailuserID));
    header("Location: ../admin.php?success=1");
    exit;
}
header("Location: ../index.php");
?>