<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
</div></main></div>
<script>
document.querySelectorAll('[data-toggle-password]').forEach(function (b) {
  b.addEventListener('click', function () {
    var i = document.getElementById(b.getAttribute('data-toggle-password')); var show = i.type === 'password';
    i.type = show ? 'text' : 'password'; b.setAttribute('aria-pressed', show ? 'true' : 'false');
    b.querySelector('i').className = show ? 'bi bi-eye-slash' : 'bi bi-eye';
  });
});
</script>
</body></html>
