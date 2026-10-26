<?php defined('BASEPATH') OR exit('No direct script access allowed');
$privacy = site_url(setting('privacy_page_slug', 'privacy-policy')); $site_key = setting('recaptcha_site_key'); ?>
<input type="hidden" name="form_token" value="<?= e($form_token) ?>">
<input type="hidden" name="source_url" value="<?= e(current_url()) ?>">
<div class="hp-field" aria-hidden="true"><label for="f-website">Website</label><input type="text" id="f-website" name="website" tabindex="-1" autocomplete="off"></div>
<div class="col-12">
  <div class="form-check">
    <input class="form-check-input<?= isset($errors['consent']) ? ' is-invalid' : '' ?>" type="checkbox" value="1" id="f-consent" name="consent" required<?= set_checkbox('consent', '1') ?>>
    <label class="form-check-label small" for="f-consent">I agree that my details will be used to respond to this enquiry as described in the <a href="<?= e($privacy) ?>" target="_blank">privacy policy</a>. <span class="text-danger" aria-hidden="true">*</span></label>
    <?php if (isset($errors['consent'])): ?><div class="invalid-feedback"><?= e($errors['consent']) ?></div><?php endif; ?>
  </div>
</div>
<?php if ($site_key): ?>
<input type="hidden" name="g-recaptcha-response" id="g-recaptcha-response">
<script src="https://www.google.com/recaptcha/api.js?render=<?= e($site_key) ?>" defer></script>
<script>document.addEventListener('submit',function(ev){var f=ev.target;if(!f.matches('[data-lead-form]')||f.dataset.rc)return;ev.preventDefault();grecaptcha.ready(function(){grecaptcha.execute(<?= json_encode($site_key) ?>,{action:'lead'}).then(function(t){f.querySelector('#g-recaptcha-response').value=t;f.dataset.rc=1;f.submit();});});});</script>
<?php endif; ?>
