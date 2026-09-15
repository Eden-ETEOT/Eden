/* Máscaras de CPF e CNPJ (envio como string formatada). */
(function () {
  function soDigitos(v) { return (v || "").replace(/\D/g, ""); }

  function mascaraCPF(v) {
    v = soDigitos(v).slice(0, 11);
    if (v.length > 9) return v.replace(/(\d{3})(\d{3})(\d{3})(\d{1,2})/, "$1.$2.$3-$4");
    if (v.length > 6) return v.replace(/(\d{3})(\d{3})(\d{1,3})/, "$1.$2.$3");
    if (v.length > 3) return v.replace(/(\d{3})(\d{1,3})/, "$1.$2");
    return v;
  }

  function mascaraCNPJ(v) {
    v = soDigitos(v).slice(0, 14);
    if (v.length > 12) return v.replace(/(\d{2})(\d{3})(\d{3})(\d{4})(\d{1,2})/, "$1.$2.$3/$4-$5");
    if (v.length > 8) return v.replace(/(\d{2})(\d{3})(\d{3})(\d{1,4})/, "$1.$2.$3/$4");
    if (v.length > 5) return v.replace(/(\d{2})(\d{3})(\d{1,3})/, "$1.$2.$3");
    if (v.length > 2) return v.replace(/(\d{2})(\d{1,3})/, "$1.$2");
    return v;
  }

  function aplicar(id, fn) {
    var el = document.getElementById(id);
    if (!el) return;
    el.setAttribute("inputmode", "numeric");
    el.addEventListener("input", function (e) {
      var pos = e.target.selectionStart;
      var antes = e.target.value.length;
      e.target.value = fn(e.target.value);
      var depois = e.target.value.length;
      try { e.target.setSelectionRange(pos + (depois - antes), pos + (depois - antes)); } catch (err) {}
    });
    if (el.value) el.value = fn(el.value);
  }

  document.addEventListener("DOMContentLoaded", function () {
    aplicar("cpf", mascaraCPF);
    aplicar("cnpj", mascaraCNPJ);
  });
})();
