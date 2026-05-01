<?php
$page_title = 'Demo — 3D VR House';
$meta_description = 'View interactive 3D house demo with VR support.';
$extra_css = 'home.css'; // you can create separate CSS later if needed

require_once __DIR__ . '/includes/header.php';
?>

<section class="container demo-section">
  <h1 class="section-title">3D House Demo</h1>
  <p class="muted">Explore our interactive 3D house experience.</p>

  <div class="demo-placeholder">
    <img src="<?= BASE_URL ?>assets/images/demo-placeholder.jpeg" alt="Demo placeholder image">
  </div>

  <!-- Later you will replace this with Unity WebGL -->
  <!-- Example:
  <iframe src="your-unity-build/index.html" width="100%" height="600"></iframe>
  -->

</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>