document.addEventListener("DOMContentLoaded", function() {
      const emailInput = document.querySelector('input[name="usuario"]');
      const passwordInput = document.querySelector('input[name="senha"]');
      const rememberCheckbox = document.querySelector('input[name="lembrar"]');
      const form = document.querySelector('form');

      const saved = localStorage.getItem("edenLoginRemember");
      if (saved) {
        try {
          const data = JSON.parse(saved);
          if (data.email) emailInput.value = data.email;
          if (data.password) passwordInput.value = data.password;
          rememberCheckbox.checked = true;
        } catch (e) {
          localStorage.removeItem("edenLoginRemember");
        }
      }

      form.addEventListener("submit", function() {
        if (rememberCheckbox.checked) {
          localStorage.setItem("edenLoginRemember", JSON.stringify({
            email: emailInput.value,
            password: passwordInput.value
          }));
        } else {
          localStorage.removeItem("edenLoginRemember");
        }
      });
    });