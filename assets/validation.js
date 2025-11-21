// assets/validation.js - client-side validation (no external libs)
document.addEventListener('DOMContentLoaded', function () {
  // validate forms with data-validate attribute
  document.querySelectorAll('form[data-validate]').forEach(form => {
    form.addEventListener('submit', function (e) {
      const pw = form.querySelector('[name="password"]');
      const confirm = form.querySelector('[name="confirm_password"]');
      if (pw && pw.value.length < 8) {
        alert('Password must be at least 8 characters.');
        e.preventDefault();
        return;
      }
      if (pw && confirm && pw.value !== confirm.value) {
        alert('Passwords do not match.');
        e.preventDefault();
        return;
      }
      // file size check example: profile_pic
      const fileInput = form.querySelector('input[type="file"][name="profile_pic"]');
      if (fileInput && fileInput.files[0] && fileInput.files[0].size > 2 * 1024 * 1024) {
        alert('Profile picture must be less than 2MB.');
        e.preventDefault();
        return;
      }
    });
  });
});
