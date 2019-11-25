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
if ($_SESSION['log'] == 1 or $_SESSION['forcepwreset']) {
    if ($_POST['newmailpw'] == $_POST['newmailpwrep']) {
        $newmailpw = $_POST['newmailpw'];
        $oldmailpw = $_POST['oldmailpw'];
        if (strpos($newmailpw, "'") !== false) {
            if ($_SESSION['forcepwreset']) header("Location: ../index.php?wrongsymbols=1");
            else header("Location: settings.php?wrongsymbols=1");
            exit;
        }
        $mailusername = $_SESSION['username'];
        $maildomain = $_SESSION['domain'];
        $abfrage = "SELECT `password` FROM `accounts` WHERE `username` = :newmailusername AND `domain` = :newmaildomain";
        $sth = $dbh->prepare($abfrage);
        $sth->execute(array(':newmailusername' => $mailusername, ':newmaildomain' => $maildomain));
        $result= $sth->fetchAll();
        $oldpwhashed = $result[0]['password'];
        if ($_SESSION['forcepwreset'] and password_verify($newmailpw, $oldpwhashed)) {
            header("Location: ../index.php?newpwequal=1");
            exit;
        }
        if (password_verify($oldmailpw, $oldpwhashed)) {
            if (strlen($newmailpw) >= 8) {
                $newmailpwhashed = password_hash($newmailpw, PASSWORD_ARGON2I, ['memory_cost' => 32768, 'time_cost' => 4]);
                $eintrag = "UPDATE `accounts` SET `password` = :newmailpwhashed WHERE `username` LIKE :mailusername AND `domain` LIKE :maildomain";
                $sth = $dbh->prepare($eintrag);
                $sth->execute(array(':newmailpwhashed' => $newmailpwhashed, ':mailusername' => $mailusername, ':maildomain' => $maildomain));
                //if ($config['maildirencryption']) {
                //    if ($_POST['forcekeyregen']) {
                //        exec('sudo -u vmail /usr/bin/doveadm -o stats_writer_socket_path= -o plugin/mail_crypt_private_password=' . escapeshellarg($newmailpw) . ' mailbox cryptokey generate -U -f -u ' . escapeshellarg($mailusername));
                //    }
                //    else {
                //        exec('sudo -u vmail /usr/bin/doveadm mailbox cryptokey password -o stats_writer_socket_path= -u ' . escapeshellarg($mailusername) . ' -n ' . escapeshellarg($newmailpw) . ' -o' . escapeshellcmd($oldmailpw));
                //    }
                //}
                if ($_SESSION['forcepwreset']) {
                    $_SESSION['forcepwreset'] = 0;
                    $_SESSION['log'] = 1;
                    $eintrag = "UPDATE `accounts` SET `forcepwreset` = '0', `enabled` = '1' WHERE `username` LIKE :mailusername AND `domain` LIKE :maildomain";
                    $sth = $dbh->prepare($eintrag);
                    $sth->execute(array(':mailusername' => $mailusername, ':maildomain' => $maildomain));
                }
                header("Location: ../settings.php?success=1");
                exit;
            }
            else {
                if ($_SESSION['forcepwreset']) header("Location: ../index.php?pwtoshort=1");
                else header("Location: ../settings.php?pwtoshort=1");
                exit;
            }
        }
        else {
            if ($_SESSION['forcepwreset']) header("Location: ../index.php?pwmissmatch=1");
            else header( "Location: ../settings.php?pwmissmatch=1");
            exit;
        }
    }
    else {
        if ($_SESSION['forcepwreset']) header("Location: ../index.php?pwnotequal=1");
        else header("Location: ../settings.php?pwnotequal=1");
        exit;
    }
}
header("Location: ../index.php");
