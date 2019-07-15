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
session_start();
if ($_SESSION['log'] == 1 and $_SESSION['admin'] == 1) {
    echo '<html>
<head>
<title>Roteserver - Mail Admin Settings</title>
</head>
<body>
<h1>Mail Admin Settings:</h1>';
    if (isset($_GET['success'])) {
        echo 'Erfolgreich geändert.';
    }
    if (isset($_GET['fehler'])) {
        echo '<h3>Fehler: ' . $_GET['fehler'] . '</h3>';
    }
    echo '<a href="settings.php"><p>Normale Einstellungen</p></a><a href="logout.php"><button>Logout</button></a>';
    echo '<h3>Mailadresse aktivieren:</h3>
<form name="activatemail" method=POST action="bin/activatemail.php">
<label>Activate Mail:<select name="mailuserID">';
    $abfrage = "SELECT `id`, `email` FROM `virtual_users` WHERE `active` LIKE 0";
    $result = $dbh->query($abfrage);
    while ($emails = $result->fetch()) {
        echo '<option value="' . $emails['id'] . '">' . $emails['email'] . '</option>';
    }
    echo '</select></label>
<input type="submit" name="submit" value="aktivieren"/>';

    echo '</form>
<h3>Mailadresse deaktivieren:</h3>
<form name="deactivatemail" method=POST action="bin/deactivatemail.php">
<label>Deactivate Mail:<select name="mailuserID">';
    $abfrage = "SELECT `id`, `email` FROM `virtual_users` WHERE `active` LIKE 1";
    $result = $dbh->query($abfrage);
    while ($emails = $result->fetch()) {
        echo '<option value="' . $emails['id'] . '">' . $emails['email'] . '</option>';
    }
    echo '</select></label>
<input type="submit" name="submit" value="deaktivieren"/>
</form>
<h3>Emailadresse hinzufügen:</h3>
<form name="createmailuser" method=POST action="bin/createmailuser.php">
<label>Neue email<input type="text" name="newmailusername"/>@roteserver.de (benutze nicht ' .  "'" . ')</label>
<label>Neues Passwort<input type="password" name="newmailpw"/>(min. 8 Zeichen, benutze nicht ' .  "'" . ')</label>
<label>Neues Passwort wiederholen<input type="password" name="newmailpwrep"/></label>
<input type="submit" name="submit" value="Hinzufügen"/>
</form>
<h3>Emailadresse entfernen:</h3>
<form name="deletemail" method=POST action="bin/deletemail.php">
<label>Delete Mail:<select name="mailuserID">';
    $abfrage = "SELECT `id`, `email` FROM `virtual_users`";
    $result = $dbh->query($abfrage);
    while ($emails = $result->fetch()) {
        echo '<option value="' . $emails['id'] . '">' . $emails['email'] . '</option>';
    }
    echo '</select></label>
<input type="submit" name="submit" value="ENTFERNEN"/>
</form>
</body>
</html>';
    exit;
}
header("Location: index.php");
?>