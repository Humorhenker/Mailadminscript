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
try {
    $dbh = new PDO('mysql:host=' . $config['dbservername'] . ';dbname=' . $config['dbname'], $config['dbusername'], $config['dbpassword'], array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
} catch (PDOException $e) {
    //echo 'Connection failled: '. $e->getMessage(); // Errormessage kann Sicherheitsrelevantes enthalen
    echo 'Connection failed';
}
$eintrag = "DELETE FROM `alias_del_requests` WHERE DATEDIFF(NOW(), `created`) > :datediff";
$sth = $dbh->prepare($eintrag);
$sth->execute(array(':datediff' => $config['deletedelrequestdaydiff']));
echo '<html>
    <head>
    <title>Abmelden</title>
    </head>
    <body>';
if (isset($_GET['unknowntoken'])) {
    echo '<p>Unbekannter Abmeldetoken. Erneut veruschen?</p>';
}
if (isset($_GET['mailsent'])) {
    echo '<h3>Falls die angegebene E-Mail-Adresse auf der Mailingliste steht, haben wir dir eine Email mit einem Link zur Bestätigung deiner Abmeldung geschickt. Der Link in der Mail ist 2 Tage gültig</h3>';
}
if (isset($_GET['success'])) {
    echo '<p>Erfolgreich abgemeldet</p>';
}
echo '<h2>Mailliste Abmeldung:</h2>
    <form method="POST" action="bin/unsubmaillistpre.php">
    <label>Maillistadresse: <input name="source_adress" type="text"';
    if (isset($_GET['maillist'])) {
        echo 'value="' . htmlentities($_GET['maillist']) . '" readonly="true"';
    }
    echo '/></label>
    <label>Nutzeradresse: <input name="destination_adress" type="text"/></label>
    <input name="Submit" type="submit" value="Abmelden"/>
    </form>';
echo '</body>
</html>';
?>