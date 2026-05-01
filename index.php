<?php
$page_title = '3D VR House — Interactive 3D & VR Walkthroughs';
$meta_description = 'Create, manage and showcase 3D house models with VR walkthroughs. Integrate Unity WebGL and manage scenes from your dashboard.';
$extra_css = 'home.css';
require_once __DIR__ . '/includes/header.php';
?>

<section class="hero">
  <div class="container hero-grid">
    <div class="hero-left">
      <h1 class="hero-title">Bring houses to life — in 3D & VR</h1>
      <p class="hero-sub">Design, explore, and present immersive 3D house visualizations. Integrate Unity scenes and let viewers step inside your designs.</p>

      <div class="hero-ctas">
      <a class="btn-primary" href="<?= BASE_URL ?>auth/register.php">Get started — it's free</a>
      </div>

      <ul class="hero-features" aria-hidden="false">
        <li>Web & VR-friendly previews</li>
        <li>Per-user scene management</li>
        <li>Lightweight, responsive UI</li>
      </ul>
    </div>

    <div class="hero-right" aria-hidden="true">
      <div class="device-mock">
        <img src="<?= BASE_URL ?>assets/images/home-demo.jpg" alt="3D house preview on device" loading="lazy">
      </div>
    </div>
  </div>
</section>

<section id="features" class="container features">
  <h2 class="section-title">Key features</h2>
  <div class="feature-list">
    <article class="feature-card">
      <h3>Immersive VR Walkthroughs</h3>
      <p>Low-latency scenes exported from Unity for a smooth VR experience.</p>
    </article>

    <article class="feature-card">
      <h3>Scene Manager</h3>
      <p>Upload, organize, and publish Unity builds — all from your dashboard.</p>
    </article>

    <article class="feature-card">
      <h3>Secure Accounts</h3>
      <p>Per-user access, secure authentication, and role-friendly preview modes.</p>
    </article>
  </div>
</section>

<section id="how" class="container how-it-works">
  <h2 class="section-title">How it works</h2>
  <ol class="steps">
    <li><strong>Create an account</strong> — register and open your dashboard.</li>
    <li><strong>Upload a Unity build</strong> — export WebGL and add it to a scene.</li>
    <li><strong>Preview or present</strong> — open Web or launch VR for immersive demos.</li>
  </ol>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
