<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CSRF Test</title>
    <?= $csrf->metaTag('contact_form') ?>
</head>
<body>
    <h1>Form Kontak</h1>
    <form action="/csrf-test/process/" method="post">
        <?= $csrf->field('contact_form') ?>
        <label>Nama:
            <input type="text" name="nama" required>
        </label>
        <br>
        <label>Pesan:
            <textarea name="pesan" required></textarea>
        </label>
        <br>
        <button type="submit">Kirim</button>
    </form>
</body>
</html>