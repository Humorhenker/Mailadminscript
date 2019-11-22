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
if ($_SESSION['log'] == 1 and $_SESSION['admin'] == 1) {
    if (strpos($_POST['newmailpw'] , "'") !== false) {
        header("Location: ../admin.php?wrongsymbols=1");
        exit;
    }
    if ($_POST['newmailpw'] == $_POST['newmailpwrep']) {
        if (strlen($_POST['newmailpw'] ) >= 8) {
            $newmailpwhashed = password_hash($_POST['newmailpw'] , PASSWORD_ARGON2I, ['memory_cost' => 32768, 'time_cost' => 4]);
            $eintrag = "UPDATE `accounts` SET `password` = :newmailpwhashed WHERE `id` LIKE :id";
            $sth = $dbh->prepare($eintrag);
            $sth->execute(array(':newmailpwhashed' => $newmailpwhashed, ':id' => $_POST['changemailid']));
            header("Location: ../settings.php?success=1");
            exit;
        }
        else {
            header("Location: ../admin.php?pwtoshort=1");
            exit;
        }
    }
    else {
        header("Location: ../admin.php?pwnotequal=1");
        exit;
    }
}
header("Location: index.php");
?>