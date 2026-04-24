<?php
require_once __DIR__ . '/config.php';
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title><?= htmlspecialchars($page_title ?? '3D VR House') ?></title>
  <meta name="description" content="<?= htmlspecialchars($meta_description ?? 'Interactive 3D house visualizations and VR walkthroughs.') ?>">
  <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/main.css">
  <?php if (!empty($extra_css)): ?>
    <?php if (is_array($extra_css)): foreach ($extra_css as $css): ?>
      <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/<?= htmlspecialchars($css) ?>">
    <?php endforeach; else: ?>
      <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/<?= htmlspecialchars($extra_css) ?>">
    <?php endif; ?>
  <?php endif; ?>
</head>
<body>
<header class="site-header" role="banner">
  <div class="container header-row">
    <a class="brand" href="<?= BASE_URL ?>">
      <img src="<?= BASE_URL ?>assets/images/logo.png" alt="3D House logo" class="logo">
      <span class="brand-name">VistaHaus</span>
    </a>

    <nav class="main-nav" role="navigation" aria-label="Main navigation">
      <a href="<?= BASE_URL ?>" class="nav-link">Home</a>
      <a href="<?= BASE_URL ?>#features" class="nav-link">Features</a>
      <a href="<?= BASE_URL ?>#how" class="nav-link">How it works</a>
      <a href="<?= BASE_URL ?>#demo" class="nav-link">Demo</a>
      <?php if (!empty($_SESSION['user_id'])): ?>
        <a href="<?= BASE_URL ?>dashboard/" class="nav-link">Dashboard</a>
        <a href="<?= BASE_URL ?>auth/logout.php" class="nav-link">Logout</a>
      <?php else: ?>
        <a href="<?= BASE_URL ?>auth/login.php" class="nav-link btn-ghost">Login</a>
        <a href="<?= BASE_URL ?>auth/register.php" class="nav-link btn-ghost">Sign up</a>
      <?php endif; ?>
    </nav>

    <button class="nav-toggle" aria-controls="main-nav" aria-expanded="false" aria-label="Toggle navigation">☰</button>
  </div>
</header>

<main id="main" class="site-main" role="main">
