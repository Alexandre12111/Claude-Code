/* Charles Culioli-Paoli · interactions de la maquette */
(function () {
  "use strict";
  var $ = function (s, c) { return (c || document).querySelector(s); };
  var $$ = function (s, c) { return Array.prototype.slice.call((c || document).querySelectorAll(s)); };

  /* Menu plein écran */
  var drawer = $("#drawer");
  $$("[data-menu-open]").forEach(function (b) {
    b.addEventListener("click", function () {
      drawer.classList.add("open"); drawer.removeAttribute("aria-hidden");
      b.setAttribute("aria-expanded", "true"); $("[data-menu-close]").focus();
    });
  });
  $$("[data-menu-close]").forEach(function (b) {
    b.addEventListener("click", function () {
      drawer.classList.remove("open"); drawer.setAttribute("aria-hidden", "true");
      var o = $("[data-menu-open]"); o.setAttribute("aria-expanded", "false"); o.focus();
    });
  });

  /* Urgence pénale : numéro révélé au clic seulement */
  var dlg = $("#urgence");
  $$("[data-urgence]").forEach(function (b) {
    b.addEventListener("click", function () {
      if (drawer && drawer.classList.contains("open")) $("[data-menu-close]").click();
      if (dlg.showModal) dlg.showModal(); else dlg.setAttribute("open", "");
    });
  });
  $$("[data-urg-close]").forEach(function (b) { b.addEventListener("click", function () { dlg.close(); }); });
  dlg.addEventListener("click", function (e) { if (e.target === dlg) dlg.close(); });
  var reveal = $("#urg-reveal"), num = $("#urg-number");
  reveal.addEventListener("click", function () {
    var n = reveal.getAttribute("data-n"), d = reveal.getAttribute("data-d");
    var tel = $("a.tel", num); tel.href = "tel:" + n; tel.textContent = d;
    num.hidden = false; reveal.hidden = true; tel.focus();
  });
  var copy = $("#urg-copy");
  if (copy) copy.addEventListener("click", function () {
    var t = $("a.tel", num).textContent;
    var done = function () { copy.querySelector("span").textContent = "Numéro copié"; };
    try {
      navigator.clipboard.writeText(t).then(done, function () { selectText(); });
    } catch (e) { selectText(); }
    function selectText() {
      var r = document.createRange(); r.selectNodeContents($("a.tel", num));
      var s = window.getSelection(); s.removeAllRanges(); s.addRange(r);
    }
  });

  /* Frise de la procédure */
  var tabs = $$(".frise-tabs [role=tab]");
  if (tabs.length) {
    var bar = $(".frise-progress");
    var select = function (i, focus) {
      tabs.forEach(function (t, j) {
        var on = i === j, p = document.getElementById(t.getAttribute("aria-controls"));
        t.setAttribute("aria-selected", on); t.tabIndex = on ? 0 : -1;
        p.hidden = !on; if (on) { p.classList.remove("in"); void p.offsetWidth; p.classList.add("in"); }
      });
      if (bar) bar.style.width = ((i + 1) / tabs.length * 100) + "%";
      if (focus) tabs[i].focus();
      tabs[i].scrollIntoView({ block: "nearest", inline: "nearest" });
    };
    tabs.forEach(function (t, i) {
      t.addEventListener("click", function () { select(i); });
      t.addEventListener("keydown", function (e) {
        if (e.key === "ArrowRight") { e.preventDefault(); select((i + 1) % tabs.length, true); }
        if (e.key === "ArrowLeft") { e.preventDefault(); select((i - 1 + tabs.length) % tabs.length, true); }
      });
    });
    select(0);
  }

  /* Filtres établissements */
  var fbtn = $$(".filters button");
  fbtn.forEach(function (b) {
    b.addEventListener("click", function () {
      fbtn.forEach(function (x) { x.setAttribute("aria-pressed", x === b); });
      var f = b.getAttribute("data-f");
      $$("[data-region]").forEach(function (r) { r.hidden = f !== "all" && r.getAttribute("data-region") !== f; });
    });
  });

  /* Formulaire de contact (maquette : aucune donnée n'est envoyée) */
  var form = $("#contact-form");
  if (form) {
    var rules = {
      nom: function (v) { return v.trim().length > 1 || "Indiquez votre nom et votre prénom."; },
      email: function (v) { return /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/.test(v) || "Saisissez une adresse email valide, par exemple nom@domaine.fr."; },
      tel: function (v) { return v.replace(/\D/g, "").length >= 9 || "Saisissez un numéro de téléphone joignable."; },
      objet: function (v) { return !!v || "Choisissez l'objet de votre demande."; },
      domaine: function (v) { return !!v || "Choisissez le domaine concerné."; },
      message: function (v) { return v.trim().length >= 20 || "Décrivez votre situation en quelques lignes (20 caractères minimum)."; }
    };
    var check = function (name) {
      var el = form.elements[name], f = el.closest(".field"), r = rules[name](el.value);
      var err = $(".err", f); err.textContent = r === true ? "" : r;
      f.classList.toggle("invalid", r !== true); el.setAttribute("aria-invalid", r !== true);
      return r === true;
    };
    Object.keys(rules).forEach(function (n) { form.elements[n].addEventListener("blur", function () { check(n); }); });
    form.addEventListener("submit", function (e) {
      e.preventDefault();
      var first = null;
      Object.keys(rules).forEach(function (n) { if (!check(n) && !first) first = form.elements[n]; });
      var rgpd = form.elements.rgpd, rf = $("#rgpd-err");
      rf.textContent = rgpd.checked ? "" : "Cochez la case pour que le cabinet puisse vous répondre.";
      if (!rgpd.checked && !first) first = rgpd;
      if (first) { first.focus(); return; }
      form.hidden = true; $("#form-success").hidden = false; $("#form-success").focus();
    });
  }

  /* Apparitions au défilement : seuls les éléments hors écran au chargement sont masqués */
  var rm = window.matchMedia && window.matchMedia("(prefers-reduced-motion: reduce)").matches;
  if (!rm && "IntersectionObserver" in window) {
    var io = new IntersectionObserver(function (es) {
      es.forEach(function (e) { if (e.isIntersecting) { e.target.classList.remove("pre"); io.unobserve(e.target); } });
    }, { rootMargin: "0px 0px -8% 0px" });
    $$(".reveal").forEach(function (el) {
      if (el.getBoundingClientRect().top > window.innerHeight) { el.classList.add("pre"); io.observe(el); }
    });
  }
})();
