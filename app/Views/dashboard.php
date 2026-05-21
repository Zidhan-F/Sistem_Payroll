<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Payroll - Manajemen Klien</title>
    <!-- Font Awesome for Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?= base_url('css/style.css') ?>">
</head>

<body>

    <?= view('partials/_sidebar') ?>

    <!-- Main Content -->
    <div class="main-content">
        <?= view('partials/_header') ?>

        <div class="container">
            <?= view('partials/_view_dashboard') ?>
            <?= view('partials/_view_klien') ?>
            <?= view('partials/_view_karyawan') ?>
            <?= view('partials/_view_lokasi_kerja') ?>
            <?= view('partials/_view_workspace') ?>
            <?= view('partials/_view_payroll') ?>
            <?= view('partials/_view_pajak') ?>
            <?= view('partials/_view_simulasi') ?>
            <?= view('partials/_view_kompensasi') ?>
        </div>

    <?= view('partials/_modals') ?>

    <?= view('partials/_scripts') ?>
</body>

</html>
