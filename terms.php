<?php
session_start();
include_once __DIR__ . '/includes/lang.php';
?>
<!DOCTYPE html>
<html lang="<?= $lang === 'fa' ? 'fa' : 'en' ?>" dir="<?= $dir ?>">
<head>
<meta charset="UTF-8">
<title><?= t('terms_title') ?> – Reviewon</title>
<link rel="stylesheet" href="css/style.css">
<link rel="stylesheet" href="">
</head>
<body class="content-page" dir="<?= $dir ?>">
<!-- <header class="page-header">
  <div class="container">
    <h1>Terms of Service</h1>
  </div>
</header> -->
<?php include 'includes/header.php'; ?>

<main class="container">
            <div class="space-5"></div>
  <section>
    <h2><?= t('terms_1_title') ?></h2>
    <p>
      <?= t('terms_1_text') ?>
    </p>
  </section>

  <section>
    <h2><?= t('terms_2_title') ?></h2>
    <p>
      <?= t('terms_2_text') ?>
    </p>
  </section>

  <section>
    <h2><?= t('terms_3_title') ?></h2>
    <p>
      <?= t('terms_3_text') ?>
    </p>
  </section>

  <section>
    <h2><?= t('terms_4_title') ?></h2>
    <p>
      <?= t('terms_4_text') ?>
    </p>
  </section>

  <section>
    <h2><?= t('terms_5_title') ?></h2>
    <p>
      <?= t('terms_5_text') ?>
    </p>
  </section>

  <section>
    <h2><?= t('terms_6_title') ?></h2>
    <p>
      <?= t('terms_6_text') ?>
    </p>
  </section>

  <section>
    <h2><?= t('terms_7_title') ?></h2>
    <p>
      <?= t('terms_7_text') ?>
    </p>
  </section>
              <div class="space-5"></div>
</main>

    <?php include 'includes/footer.php'; ?>

</body>
    <script src="js/script.js"></script>
</html>

