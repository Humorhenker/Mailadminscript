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
function createmailuser($newmailusername, $newmaildomainid, $newmailpw, $newmailpwrep, $newmailforcepwreset, $admin) {
    global $dbh;
    global $config;
    $abfrage = "SELECT domain FROM `domains` WHERE `id` LIKE :newmaildomainid";
    $sth = $dbh->prepare($abfrage);
    $sth->execute(array(':newmaildomainid' => $newmaildomainid));
    $result = $sth->fetchAll();
    $newmaildomain = $result[0]['domain'];
    $pattern = array();
    $pattern[0] = ' ';
    $pattern[1] = '@';
    if ($config['prohibadminmailcreation']) {
        $pattern[2] = 'admin';
        $pattern[3] = 'noreply';
        $pattern[4] = 'info';
        $pattern[5] = 'webmaster';
    }
    $newmailusername =  str_replace($pattern, "", $newmailusername);
    $newmailusernamefull = $newmailusername . '@' . $newmaildomain;
    if (!filter_var($newmailusernamefull, FILTER_VALIDATE_EMAIL)) {
        // nicht ordentliche EmailAdresse
        header("Location: createmailpre.php?wrongsymbols=1");
        exit;
    }
    if(strpos($newmailusername, "'") !== false) {
        if ($admin == 1) {
            header("Location: ../admin.php?fehler=Falsche Zeichen in Adresse");
            exit;
        } else {
            header("Location: createmailpre.php?wrongsymbols=1");
            exit;
        }    
    }
    if (strpos($newmailpw, "'") !== false) {
        if ($admin == 1) {
            header("Location: ../admin.php?fehler=Falsche Zeichen in Passwort");
            exit;
        } else {
            header("Location: createmailpre.php?wrongsymbols=1");
            exit;
        }
    }
    if (strlen($newmailpw) >= 8) {
        if ($newmailpw == $newmailpwrep) {
            $abfrage = "SELECT 1 FROM `accounts` WHERE `username` = :newmailusername AND `domain` = :newmaildomain";
            $sth = $dbh->prepare($abfrage);
            $sth->execute(array(':newmailusername' => $newmailusername, ':newmaildomain' => $newmaildomain));
            $result = $sth->fetchAll();
            //print_r($result);
            if ($result[0][1] !== 1) {
                $newmailpwhashed = password_hash($newmailpw, PASSWORD_ARGON2I, ['memory_cost' => 32768, 'time_cost' => 4]);
                //$createdtimestamp = date("Y-m-d H:i:s");
                // if ($config['maildirencryption']) {
                //     $eintrag = "INSERT INTO `virtual_users` (`domain_id`, `password`, `email`, `username`, `active`, `created`, `pre-pw-key`, `pw-key`, `admin`) VALUES ('1', :newmailpwhashed, :newmailusernamefull, :newmailusername, '1', '$createdtimestamp', '0', '0', '0')"; // Maildaten in MailServer DB eintragen
                //     $sth = $dbh->prepare($eintrag); // der Nutzer muss erst kurzzeitig aktive geschaltet werden, damit die cryptkeys erstellt werden können. Danach wird er direkt wieder deaktiviert.
                //     $sth->execute(array('newmailpwhashed' => $newmailpwhashed, 'newmailusernamefull' => $newmailusernamefull, 'newmailusername' =>$newmailusername));
                //     $maildirpath = $config['mailfolderpath'] . $newmailusername;
                //     umask(0);
                //     mkdir($maildirpath, 0770);
                //     exec('sudo -u vmail /usr/bin/doveadm -o stats_writer_socket_path= -o plugin/mail_crypt_private_password=' . escapeshellarg($newmailpw) . ' mailbox cryptokey generate -U -f -u ' . escapeshellarg($newmailusernamefull));
                //     $eintrag = "UPDATE `mailserver`.`virtual_users` SET `active`='0' WHERE `email` LIKE :newmailusernamefull";
                // }
                //else {
                    $eintrag = "INSERT INTO `accounts` (`username`, `domain`, `password`, `quota`, `enabled`, `forcepwreset`, `sendonly`, `admin`) VALUES (:newmailusername, :newmaildomain, :newmailpwhashed, '2048', '1', :forcepwreset, '0', '0')"; // Maildaten in MailServer DB eintragen
                    $sth = $dbh->prepare($eintrag);
                    $sth->execute(array(':newmailusername' => $newmailusername, ':newmaildomain' => $newmaildomain, ':newmailpwhashed' => $newmailpwhashed, ':forcepwreset' =>  $newmailforcepwreset));
                //$maildirpath = $config['mailfolderpath'] . $newmailusername;
                //    umask(0);
                //    mkdir($maildirpath, 0770);
                //}
                //$sth = $dbh->prepare($eintrag);
                //$sth->execute(array(':newmailusernamefull' => $newmailusernamefull));
                if ($config['sendactivationinfo']) {
                    $adminmailadress = $config['adminadress'];
                    $adresse = $config['domain'] . '/admin.php';
                    // eine Mail an den Admin verschicken, damit er die Mail freischalten kann
                    mail($adminmailadress, "Neue Mailadresse erstellt", "Eine neue Mailadresse wurde erstellt und muss freigeschaltet werden. \n \n" . htmlspecialchars($newmailusernamefull) . "\n " . $adresse, "From: mailservice");
                }
                if ($admin == 1) {
                    header("Location: ../admin.php?success=1");
                    exit;
                } else {
                    header("Location: ../index.php");
                    exit;
                }
                exit;
            } else { // Emailadresse ist bereits registriert
                if ($admin == 1) {
                    header("Location: ../admin.php?fehler=Mail besteht schon");
                    exit;
                } else {
                    header("Location: createmailpre.php?mailalreadytaken=1");
                    exit;
                }
            }
        }
        else {
            if ($admin == 1) {
                header("Location: ../admin.php?fehler=PW nicht gleich");
                exit;
            } else {
                header("Location: createmailpre.php?pwnotequal=1");
                exit;
            }
        }
    } else { // Passwort zu kurz
        if ($admin == 1) {
            header("Location: ../admin.php?fehler=PW zu kurz");
            exit;
        } else {
            header("Location: createmailpre.php?pwtooshort=1");
            exit;
        }
    }
}
session_start();
if ($_SESSION['log'] == 1 AND $_SESSION['admin'] == 1) {
    //print_r($_POST);
    createmailuser($_POST['newmailusername'], $_POST['newmaildomainid'], $_POST['newmailpw'], $_POST['newmailpwrep'], $_POST['forcepwreset'], 1);
    header("Location: ../admin.php");
    exit;
}
if ($config['allowregistration']) {
    if ($_POST['captchacode'] == $_SESSION['captchacode']) {
        createmailuser($_POST['newmailusername'], $_POST['newmaildomainid'], $_POST['newmailpw'], $_POST['newmailpwrep'], $_POST['forcepwreset'], 0);
    }
    elseif ($_POST['captchacode'] != $_SESSION['captchacode']) {
        header("Location: createmailpre.php?wrongcaptchacode=1");
        exit;    
    }
    header("Location: ../index.php");
}
else {
    header("Location: ../index.php");
}
?>