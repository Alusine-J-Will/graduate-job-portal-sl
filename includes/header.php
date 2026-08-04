<?php
/**
 * Shared HTML header partial.
 *
 * Loads meta tags and frontend asset links for every page.
 */
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="GradConnect SL helps graduates in Sierra Leone discover verified job opportunities and connect with employers.">
    <title>GradConnect SL | Connecting Graduates to Opportunities</title>
    <link href="<?php echo BASE_URL; ?>assets/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/all.min.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/style.css">
    <script>
        window.BASE_URL = '<?php echo rtrim(BASE_URL, '/'); ?>/';
    </script>
</head>
<body class="site-body">
