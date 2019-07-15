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
session_start();
if (!isset($_SESSION['log']) OR $_SESSION['log'] != 1) {
    echo '<html>
    <head>
    </head>
    <body>';
    if (isset($_GET['badlogin'])) {
        echo '<p>falsche Logindaten</p>';
    }
    echo '<a href="webmail"><h2>Webmail</h2></a>
    <h2>Config-Login:</h2>
    <form method="POST" action="login.php">
    <label>Nutzername<input name="username" type="text"/></label>
    <label>Passwort<input name="password" type="password"/></label>
    <input name="Submit" type="submit" value="Einloggen"/>
    </form>
    <h3>Neues Konto erstellen:</h3>
    <a href="bin/createmailpre.php"><button>Kontoerstellung</button></a>
    </body>
    </html>
    ';
} else {
    header("Location: settings.php");
}
?>