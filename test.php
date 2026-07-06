<?php

require_once 'config/database.php';

if (isset($conn) && $conn) {
    echo "<h1 style='color:green;'>✅ Database Connected Successfully!</h1>";
} else {
    echo "<h1 style='color:red;'>❌ Database Connection Failed!</h1>";
}