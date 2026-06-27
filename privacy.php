<?php
session_start();
include_once __DIR__ . '/includes/lang.php';
?>
<!DOCTYPE html>
<html lang="<?= $lang === 'fa' ? 'fa' : 'en' ?>" dir="<?= $dir ?>">
<head>
<meta charset="UTF-8">
<title><?= t('privacy_title') ?> – Reviewon</title>
<link rel="stylesheet" href="css/style.css">
</head>
<body class="content-page" dir="<?= $dir ?>">
<?php include 'includes/header.php'; ?>

<main class="container">
            <div class="space-5"></div>

  <section>
    <h2><?= t('privacy_1_title') ?></h2>
    <p>
      <?= t('privacy_1_text') ?>
    </p>
  </section>

  <section>
    <h2><?= t('privacy_2_title') ?></h2>
    <p>
      <?= t('privacy_2_text') ?>
    </p>
  </section>

  <section>
    <h2><?= t('privacy_3_title') ?></h2>
    <p>
      <?= t('privacy_3_text') ?>
    </p>
  </section>

  <section>
    <h2><?= t('privacy_4_title') ?></h2>
    <p>
      <?= t('privacy_4_text') ?>
    </p>
  </section>

  <section>
    <h2><?= t('privacy_5_title') ?></h2>
    <p>
      <?= t('privacy_5_text') ?>
    </p>
  </section>

  <section>
    <h2><?= t('privacy_6_title') ?></h2>
    <p>
      <?= t('privacy_6_text') ?>
    </p>
  </section>

  <section>
    <h2><?= t('privacy_7_title') ?></h2>
    <p>
      <?= t('privacy_7_text') ?>
    </p>
  </section>

  <section>
    <h2><?= t('privacy_8_title') ?></h2>
    <p>
      <?= t('privacy_8_text') ?>
    </p>
  </section>
              <div class="space-5"></div>

</main>

    <?php include 'includes/footer.php'; ?>

</body>
    <script src="js/script.js"></script>
</html>

