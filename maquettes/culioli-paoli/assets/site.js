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

  var reduce = window.matchMedia && window.matchMedia("(prefers-reduced-motion: reduce)").matches;
  var fine = window.matchMedia && window.matchMedia("(hover: hover) and (pointer: fine)").matches;

  /* Halo qui suit le curseur (héros et en-têtes de page) */
  if (fine && !reduce) $$(".hero, .page-head").forEach(function (h) {
    h.addEventListener("pointermove", function (e) {
      var r = h.getBoundingClientRect();
      h.style.setProperty("--mx", (e.clientX - r.left) + "px"); h.style.setProperty("--my", (e.clientY - r.top) + "px");
    });
  });

  /* Halo sur les cartes */
  $$(".spot").forEach(function (c) {
    c.addEventListener("pointermove", function (e) {
      var r = c.getBoundingClientRect();
      c.style.setProperty("--x", (e.clientX - r.left) + "px"); c.style.setProperty("--y", (e.clientY - r.top) + "px");
    });
  });

  /* Inclinaison 3D */
  if (fine && !reduce) $$(".tilt").forEach(function (t) {
    var host = t.parentElement;
    host.addEventListener("pointermove", function (e) {
      var r = host.getBoundingClientRect(), x = (e.clientX - r.left) / r.width - .5, y = (e.clientY - r.top) / r.height - .5;
      t.style.transform = "rotateY(" + (x * 10) + "deg) rotateX(" + (-y * 10) + "deg)";
    });
    host.addEventListener("pointerleave", function () { t.style.transform = ""; });
  });

  /* Procédure : défilement horizontal piloté par le défilement vertical */
  var hs = $(".hs");
  if (hs) {
    var track = $(".hs-track", hs), hbar = $(".hs-bar i", hs);
    var hsScroll = function () {
      if (hs.classList.contains("static")) return;
      var r = hs.getBoundingClientRect(), dist = track.scrollWidth - window.innerWidth;
      var p = Math.min(Math.max(-r.top / Math.max(hs.offsetHeight - window.innerHeight, 1), 0), 1);
      track.style.transform = "translate3d(" + (-p * dist) + "px,0,0)";
      if (hbar) hbar.style.width = (p * 100) + "%";
    };
    var hsSetup = function () {
      var desktop = window.innerWidth > 860 && !reduce;
      hs.classList.toggle("static", !desktop);
      if (!desktop) { hs.style.height = ""; track.style.transform = ""; return; }
      hs.style.height = (window.innerHeight + Math.max(track.scrollWidth - window.innerWidth, 0)) + "px";
      hsScroll();
    };
    window.addEventListener("scroll", hsScroll, { passive: true });
    window.addEventListener("resize", hsSetup);
    window.addEventListener("load", hsSetup);
    hsSetup();
  }

  /* Cartes empilées : la carte recouverte recule légèrement */
  var cards = $$(".s-card");
  if (cards.length && !reduce) {
    var stackScroll = function () {
      cards.forEach(function (c, i) {
        var next = cards[i + 1];
        if (!next || window.innerWidth <= 760) { c.style.transform = ""; c.style.filter = ""; return; }
        var a = c.getBoundingClientRect(), b = next.getBoundingClientRect();
        var o = Math.min(Math.max((a.bottom - b.top) / a.height, 0), 1);
        c.style.transform = "scale(" + (1 - o * .05) + ")"; c.style.filter = "brightness(" + (1 - o * .35) + ")";
      });
    };
    window.addEventListener("scroll", stackScroll, { passive: true }); stackScroll();
  }

  /* Territoires : panneaux extensibles */
  var exs = $$(".ex");
  exs.forEach(function (x) {
    var on = function () { exs.forEach(function (y) { y.classList.toggle("on", y === x); }); };
    x.addEventListener("mouseenter", on); x.addEventListener("focus", on);
  });

  /* Cartes qui se retournent */
  $$(".flip").forEach(function (f) {
    f.addEventListener("click", function () { f.setAttribute("aria-pressed", f.getAttribute("aria-pressed") !== "true"); });
  });

  /* Recherche d'établissement */
  var q = $("#etab-q");
  if (q) {
    var items = $$(".results li[data-k]"), empty = $(".results .empty");
    var norm = function (s) { return s.toLowerCase().normalize("NFD").replace(/[̀-ͯ]/g, ""); };
    q.addEventListener("input", function () {
      var v = norm(q.value.trim()), n = 0;
      items.forEach(function (li) { var ok = !v || norm(li.getAttribute("data-k")).indexOf(v) > -1; li.hidden = !ok; if (ok) n++; });
      empty.hidden = n > 0;
    });
  }

  /* Compteurs */
  var count = function (el) {
    var to = parseFloat(el.getAttribute("data-count")), suf = el.getAttribute("data-suf") || "", t0 = null;
    var step = function (t) {
      if (!t0) t0 = t;
      var p = Math.min((t - t0) / 1400, 1);
      el.textContent = Math.round(to * (1 - Math.pow(1 - p, 3))) + suf;
      if (p < 1) requestAnimationFrame(step);
    };
    requestAnimationFrame(step);
  };

  /* Sommaire actif */
  var tocLinks = $$(".toc a[href^='#']");
  if (tocLinks.length && "IntersectionObserver" in window) {
    var tio = new IntersectionObserver(function (es) {
      es.forEach(function (e) {
        if (e.isIntersecting) tocLinks.forEach(function (a) { a.classList.toggle("is-active", a.getAttribute("href") === "#" + e.target.id); });
      });
    }, { rootMargin: "-20% 0px -70% 0px" });
    tocLinks.forEach(function (a) { var t = document.getElementById(a.getAttribute("href").slice(1)); if (t) tio.observe(t); });
  }

  /* Apparitions : seuls les éléments hors écran au chargement sont masqués */
  if (!reduce && "IntersectionObserver" in window) {
    var io = new IntersectionObserver(function (es) {
      es.forEach(function (e) {
        if (!e.isIntersecting) return;
        var el = e.target, kids = Array.prototype.slice.call(el.children);
        if (el.classList.contains("stagger")) {
          kids.forEach(function (c, i) { c.style.transitionDelay = (i * 80) + "ms"; });
          setTimeout(function () { kids.forEach(function (c) { c.style.transitionDelay = ""; }); }, 1600);
        }
        el.classList.remove("pre");
        $$("[data-count]", el).forEach(count);
        io.unobserve(el);
      });
    }, { rootMargin: "0px 0px -8% 0px" });
    $$(".reveal, .stagger").forEach(function (el) {
      if (el.getBoundingClientRect().top > window.innerHeight) { el.classList.add("pre"); io.observe(el); }
    });
  }
})();
