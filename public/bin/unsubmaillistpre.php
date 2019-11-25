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
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require '../vendor/autoload.php';
$config = parse_ini_file('../../private/config.ini');
try {
    $dbh = new PDO('mysql:host=' . $config['dbservername'] . ';dbname=' . $config['dbname'], $config['dbusername'], $config['dbpassword'], array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
} catch (PDOException $e) {
    //echo 'Connection failled: '. $e->getMessage(); // Errormessage kann Sicherheitsrelevantes enthalen
    echo 'Connection failed';
}
$destination = explode('@', $_POST['destination_adress']);
$source = explode('@', $_POST['source_adress']);
$abfrage = "SELECT `alias_id` FROM `aliases` WHERE `source_username` LIKE :source_username AND `source_domain` LIKE :source_domain AND `destination_username` LIKE :destination_username AND `destination_domain` LIKE :destination_domain";
$result = $dbh->prepare($abfrage);
$result->execute(array(':source_username' => $source[0], ':source_domain' => $source[1], ':destination_username' => $destination[0], ':destination_domain' => $destination[1]));
if ($result->rowCount() > 0) {
    $aliasid = $result->fetch()['alias_id'];
    $eintrag = "DELETE FROM `alias_del_requests` WHERE `alias_id` LIKE :aliasid AND `destination_username` LIKE :destination_username AND `destination_domain` LIKE :destination_domain";
    $sth = $dbh->prepare($eintrag);
    $sth->execute(array(':aliasid' => $aliasid, ':destination_username' => $destination[0], ':destination_domain' => $destination[1])); // eventuell bestehenden Token löschen
    $token = bin2hex(openssl_random_pseudo_bytes(16)); // Token zur abmeldung erstellen
    $date = date("Y-m-d H:i:s"); // Datum der Tokenerstellung für automatische löschung speichern
    $eintrag = "INSERT INTO `alias_del_requests` (`alias_id`, `destination_username`, `destination_domain`, `token`, `created`) VALUES (:aliasid, :destination_username, :destination_domain, :token, :created)";
    $sth = $dbh->prepare($eintrag);
    $sth->execute(array(':aliasid' => $aliasid, ':destination_username' => $destination[0], ':destination_domain' => $destination[1], ':token' => $token, ':created' => $date));
    $mail = new PHPMailer(true);
    try {
        $mail->CharSet = 'UTF-8';
        $mail->Encoding = 'base64';
        //Server settings
        $mail->SMTPDebug = 0;
        $mail->isSMTP();                                            // Set mailer to use SMTP
        $mail->Host       = $config['mailsmtpserver'];  // Specify main and backup SMTP servers
        $mail->SMTPAuth   = true;                                   // Enable SMTP authentication
        $mail->Username   = $config['mailadress'];                     // SMTP username
        $mail->Password   = $config['mailpw'];                               // SMTP password
        $mail->SMTPSecure = 'tls';
        $mail->Port       = 587;                                    // TCP port to connect to

        //Recipients
        $mail->setFrom($config['mailadress']);
        $mail->addAddress($_POST['destination_adress']);

        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Aus Liste ' . htmlspecialchars($_POST['source_adress']) . ' abmelden';
        $mail->Body    = 'Eine Anfrage zur Abmeldung dieser Adresse aus ' . htmlspecialchars($_POST['source_adress']) . ' wurde erstellt.<br><a href="https://mail.cloud.sdaj.org/bin/unsubmaillist.php?token=' . $token . '">Abmeldung abschließen</a>' . '<br>Der Link ist 2 Tage gültig<br>Schade, dass du gehst. Bis dahin.';
        $mail->AltBody = 'Eine Anfrage zur Abmeldung dieser Adresse aus ' . htmlspecialchars($_POST['source_adress']) . ' wurde erstellt. Hier kannst du die Abmeldung abschließen: https://mail.cloud.sdaj.org/bin/unsubmaillist.php?token=' . $token . 'Der Link ist 2 Tage gültig Schade, dass du gehst. Bis dahin.';

        $mail->send();
    } catch (Exception $e) {
        echo "Message could not be sent.";
    }
    header("Location: ../unsub.php?mailsent=1");
    exit;
}
else {
    header("Location: ../unsub.php?mailsent=1");
    exit;
}
?>