document.addEventListener('DOMContentLoaded', () => {
  const loginForm = document.getElementById('login-form');
  loginForm.addEventListener('submit', async e => {
    e.preventDefault();
    const email    = document.getElementById('login-email').value;
    const password = document.getElementById('login-password').value;

    try {
      const res = await fetch('login.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ email, password })
      });

      const result = await res.json();
      console.log('Login response:', result);

      if (result.success) {
        if (result.role === 'admin') {
          window.location.href = 'admin_product.php';
        } else {
          window.location.href = 'home-login.php';
        }
      } else {
        alert(result.error || 'Login gagal');
      }
    } catch (err) {
      console.error(err);
      alert('Terjadi kesalahan saat login.');
    }
  });
});
