<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hasil</title>
</head>
<body>
    <h1>Hasil data</h1>
    <?php
    $d[] = $data;

    foreach ($d as $datas) {
        echo htmlspecialchars((string)($datas['u_username'] ?? ''), ENT_QUOTES, 'UTF-8');
        echo "<br>";
    }
    ?>
</body>
</html>