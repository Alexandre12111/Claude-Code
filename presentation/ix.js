/* ===== Interactive platform screens (v1.5) =====
   Builder (slide 3), expense app (slide "From a prompt to a live app") and live dashboard.
   All figures are sample data. Text goes through tr() so the deck's language switcher applies. */
(function () {
  'use strict';
  var D = window.__YC_DICTS__ || {};
  function L() { return window.__ycCurLang || 'en'; }
  function tr(s) { var d = D[L()]; return (L() !== 'en' && d && Object.prototype.hasOwnProperty.call(d, s)) ? d[s] : s; }
  var LOC = { en: 'en-GB', fr: 'fr-FR', es: 'es-ES', sv: 'sv-SE', de: 'de-DE' };
  function nf(v, dec) { return new Intl.NumberFormat(LOC[L()], { minimumFractionDigits: dec || 0, maximumFractionDigits: dec || 0 }).format(v); }
  function eur(v, dec, unit) { var n = nf(v, dec); var u = unit ? tr(unit) : ''; if (L() === 'en') return '€' + n + u; return n + ' ' + (u ? u + (u.length > 1 ? ' ' : '') : '') + '€'; }
  window.__ycTr = tr; window.__ycNf = nf; window.__ycEur = eur;
  function icon(id, cls) { return '<svg class="ic ' + (cls || '') + '"><use href="#' + id + '"/></svg>'; }
  function $(s, r) { return (r || document).querySelector(s); }
  function $$(s, r) { return Array.prototype.slice.call((r || document).querySelectorAll(s)); }
  function go(n) { if (window.__ycGo) window.__ycGo(n); }
  var stage = document.getElementById('stage');
  /* stage is scaled: convert a client point to stage pixels */
  function local(e, el) { var r = el.getBoundingClientRect(); var k = r.width / el.offsetWidth; return { x: (e.clientX - r.left) / k, y: (e.clientY - r.top) / k, k: k }; }

  /* ---------------------------------------------------------------- builder */
  var mock = $('#s3 .mock');
  if (mock) {
    mock.classList.add('ix');
    var side = $$('.side div', mock), panes = $$('.pane', mock);
    side.forEach(function (d, i) { d.addEventListener('click', function () { side.forEach(function (x) { x.classList.toggle('on', x === d); }); $('.ix-gen', mock).classList.remove('on'); panes.forEach(function (p, k) { p.classList.toggle('on', k === i); }); }); });
    var t = $('.prompt .t', mock), go1 = $('.prompt .go', mock), gen = $('.ix-gen', mock);
    var TPL = ['Build a client onboarding tracker with reminders and a dashboard', 'Create an AI agent that checks supplier invoices and flags anomalies', 'Automate the weekly sales report and email it to managers every Monday', 'Build a CEO dashboard with revenue, margin and cash by region'];
    var tiles = $$('.tile', mock);
    tiles.forEach(function (x, i) { x.addEventListener('click', function () { tiles.forEach(function (y) { y.classList.toggle('sel', y === x); }); t._tt = {}; t.classList.remove('typing'); t.textContent = tr(TPL[i]); t.dataset.full = t.textContent; }); });
    $('.prompt', mock).addEventListener('click', function (e) { if (e.target === go1) return; t._tt = {}; t.classList.remove('typing'); t.setAttribute('contenteditable', 'true'); t.focus(); });
    t.addEventListener('keydown', function (e) { e.stopPropagation(); if (e.key === 'Enter') { e.preventDefault(); run(); } });
    go1.addEventListener('click', run);
    var timer = [];
    function run() {
      timer.forEach(clearTimeout); timer = [];
      t.removeAttribute('contenteditable');
      var name = (t.textContent || '').trim() || tr(TPL[0]);
      $('.ix-gen h5', mock).textContent = '“' + (name.length > 60 ? name.slice(0, 58) + '…' : name) + '”';
      var st = $$('.ix-step', mock), res = $('.ix-res', mock);
      st.forEach(function (s) { s.className = 'ix-step'; }); res.classList.remove('on'); gen.classList.add('on');
      st.forEach(function (s, k) { timer.push(setTimeout(function () { s.classList.add('run'); }, 250 + k * 650)); timer.push(setTimeout(function () { s.classList.remove('run'); s.classList.add('ok'); }, 800 + k * 650)); });
      timer.push(setTimeout(function () { res.classList.add('on'); }, 900 + st.length * 650));
    }
    $('.ix-open', mock).addEventListener('click', function () { go($$('.slide').indexOf($('#s6b'))); });
    $('.ix-back', mock).addEventListener('click', function () { gen.classList.remove('on'); });
    $$('.ix-sw', mock).forEach(function (s) { s.addEventListener('click', function () { s.classList.toggle('on'); var lab = s.parentNode.querySelector('.m span'); if (lab && lab.dataset.on) lab.textContent = tr(s.classList.contains('on') ? lab.dataset.on : lab.dataset.off); }); });
    $$('.ix-cn', mock).forEach(function (c) { c.addEventListener('click', function () { c.classList.toggle('on'); c.textContent = tr(c.classList.contains('on') ? 'Connected' : 'Connect'); }); });
    document.addEventListener('yc-lang', function () { $$('.ix-cn', mock).forEach(function (c) { c.textContent = tr(c.classList.contains('on') ? 'Connected' : 'Connect'); }); $$('.ix-sw', mock).forEach(function (s) { var lab = s.parentNode.querySelector('.m span'); if (lab && lab.dataset.on) lab.textContent = tr(s.classList.contains('on') ? lab.dataset.on : lab.dataset.off); }); });
  }

  /* ---------------------------------------------------------------- expense app */
  var app = $('#s6 .app');
  if (app) {
    app.classList.add('ix');
    var kb = $$('.kpi b', app), tip = document.createElement('div'), toast = document.createElement('div');
    tip.className = 'ix-tip'; toast.className = 'ix-toast'; $('.chartbox', app).appendChild(tip); app.appendChild(toast);
    var spent = 18.4, pend = 12, appr = 148;
    function setK() { kb[0].textContent = nf(pend); kb[1].textContent = nf(appr); kb[2].textContent = eur(spent, 1, 'k'); }
    function say(s) { toast.textContent = s; toast.classList.add('on'); clearTimeout(toast._t); toast._t = setTimeout(function () { toast.classList.remove('on'); }, 1800); }
    $$('.tr', app).forEach(function (r) {
      var acts = document.createElement('span'); acts.className = 'acts';
      acts.innerHTML = '<button class="y">' + tr('Approve') + '</button><button class="n">' + tr('Reject') + '</button>';
      r.appendChild(acts);
      r.addEventListener('click', function (e) {
        var stEl = r.querySelector('.st');
        if (e.target.tagName === 'BUTTON') {
          var ok = e.target.classList.contains('y'); var amt = parseFloat((r.firstChild.textContent.match(/(\d+)\s*€|€\s*(\d+)/) || [0, 0, 0]).slice(1).filter(Boolean)[0] || 0);
          var wasPending = !stEl.classList.contains('a') && !stEl.classList.contains('x');
          stEl.className = 'st ' + (ok ? 'a' : 'x'); stEl.textContent = tr(ok ? 'Approved' : 'Rejected');
          if (ok) stEl.style.cssText = ''; else stEl.style.cssText = 'background:#FBE3DF;color:#A3361F';
          if (wasPending) { pend = Math.max(0, pend - 1); if (ok) { appr++; spent += amt / 1000; } setK(); }
          r.classList.remove('open'); say(tr(ok ? 'Expense approved' : 'Expense rejected')); return;
        }
        $$('.tr', app).forEach(function (x) { if (x !== r) x.classList.remove('open'); }); r.classList.toggle('open');
      });
    });
    var WK = [2.9, 4.0, 3.2, 5.0, 4.2, 5.9];
    $$('.bar-c', app).forEach(function (b, i) {
      b.addEventListener('mousemove', function (e) { var p = local(e, $('.chartbox', app)); tip.innerHTML = tr('Week') + ' ' + (i + 1) + ' · ' + eur(WK[i], 1, 'k'); tip.style.left = p.x + 'px'; tip.style.top = p.y + 'px'; tip.classList.add('on'); });
      b.addEventListener('mouseleave', function () { tip.classList.remove('on'); });
    });
    $$('.kpi', app).forEach(function (k) { k.style.cursor = 'pointer'; k.addEventListener('click', function () { say(tr(['Showing pending requests', 'Showing approved requests', 'Showing spend breakdown'][$$('.kpi', app).indexOf(k)])); }); });
    var rp = $('.ix-replay'); if (rp) rp.addEventListener('click', function () { var all = $$('.slide'); var i = all.indexOf($('#s6')); if (window.__ycReplay) window.__ycReplay(i); });
    document.addEventListener('yc-lang', function () { $$('.acts', app).forEach(function (a) { a.children[0].textContent = tr('Approve'); a.children[1].textContent = tr('Reject'); }); });
    window.__ycResetExpenses = function () { spent = 18.4; pend = 12; appr = 148; $$('.tr', app).forEach(function (r) { r.classList.remove('open'); var s = r.querySelector('.st'); s.style.cssText = ''; s.className = 'st ' + s.dataset.c0; s.textContent = tr(s.dataset.t0); }); };
    $$('.tr .st', app).forEach(function (s) { s.dataset.c0 = s.className.replace('st ', ''); s.dataset.t0 = s.__ycSrc || s.textContent; s.firstChild && (s.dataset.t0 = s.firstChild.__ycSrc || s.textContent); });
  }

  /* ---------------------------------------------------------------- live dashboard */
  var db = $('#s6b .db');
  if (!db) return;
  var S = { view: 'overview', per: 12, reg: 'all', kpi: 0, sort: ['amt', -1], sel: -1 };
  var REG = { all: ['All regions', 1], fr: ['France', 0.42], eu: ['Rest of Europe', 0.36], int: ['International', 0.22] };
  function rnd(seed) { var s = seed; return function () { s = (s * 16807) % 2147483647; return (s - 1) / 2147483646; }; }
  function months(n) { var out = [], f = new Intl.DateTimeFormat(LOC[L()], { month: 'short' }); for (var i = n - 1; i >= 0; i--) { var d = new Date(2026, 9 - i, 1); out.push(f.format(d).replace('.', '')); } return out; }
  function series() {
    var k = REG[S.reg][1], r = rnd(7 + Object.keys(REG).indexOf(S.reg) * 13), rev = [], tgt = [];
    for (var i = 0; i < 12; i++) { var base = (3.4 + i * 0.12 + Math.sin(i * 0.9) * 0.25) * k * 2.4; rev.push(+(base * (0.92 + r() * 0.16)).toFixed(2)); tgt.push(+((3.5 + i * 0.11) * k * 2.4).toFixed(2)); }
    return { rev: rev.slice(12 - S.per), tgt: tgt.slice(12 - S.per), m: months(S.per), k: k };
  }
  function spark(vals, col) { var w = 90, h = 34, mn = Math.min.apply(0, vals), mx = Math.max.apply(0, vals); var p = vals.map(function (v, i) { return (i / (vals.length - 1) * w).toFixed(1) + ',' + (h - (v - mn) / (mx - mn || 1) * h).toFixed(1); }).join(' '); return '<svg width="' + w + '" height="' + h + '"><polyline points="' + p + '" fill="none" stroke="' + col + '" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/></svg>'; }
  function kpis(d) {
    var sum = d.rev.reduce(function (a, b) { return a + b; }, 0), k = d.k, per = S.per;
    var K = [
      ['Revenue', eur(sum, 1, 'M'), '+' + nf(6.8 + per * 0.1, 1) + ' %', 1, d.rev],
      ['Gross margin', nf(31.4 + (k - 0.5) * 2, 1) + ' %', '+' + nf(1.2, 1) + ' pt', 1, d.rev.map(function (v, i) { return 30 + Math.sin(i) + i * 0.1; })],
      ['Headcount', nf(Math.round(1240 * k)), '+' + nf(Math.round(38 * k)), 1, d.rev.map(function (v, i) { return 1200 + i * 4 + (i % 3); })],
      ['Cash', eur(12.8 * k, 1, 'M'), '−' + nf(2.1, 1) + ' %', 0, d.rev.map(function (v, i) { return 13 - i * 0.05 + Math.cos(i) * 0.3; })],
    ];
    return K.map(function (x, i) { return '<div class="dk' + (S.kpi === i ? ' on' : '') + '" data-k="' + i + '"><small>' + tr(x[0]) + '</small><b>' + x[1] + '</b><em class="' + (x[3] ? 'up' : 'dn') + '">' + x[2] + '</em>' + spark(x[4], x[3] ? '#1E3BFF' : '#C2410C') + '</div>'; }).join('');
  }
  function lineChart(d) {
    var W = 1000, H = 300, pl = 56, pr = 16, pt = 14, pb = 34, n = d.rev.length;
    var all = d.rev.concat(d.tgt), mn = Math.min.apply(0, all) * 0.9, mx = Math.max.apply(0, all) * 1.05;
    function X(i) { return pl + i / (n - 1) * (W - pl - pr); } function Y(v) { return pt + (1 - (v - mn) / (mx - mn)) * (H - pt - pb); }
    var g = '', ticks = 4;
    for (var j = 0; j <= ticks; j++) { var v = mn + (mx - mn) * j / ticks, y = Y(v); g += '<line x1="' + pl + '" x2="' + (W - pr) + '" y1="' + y + '" y2="' + y + '" stroke="#E3DFD3"/><text x="' + (pl - 10) + '" y="' + (y + 5) + '" text-anchor="end" font-size="15" fill="#8F8C86">' + nf(v, 1) + '</text>'; }
    d.m.forEach(function (m, i) { if (n > 8 && i % 2) return; g += '<text x="' + X(i) + '" y="' + (H - 8) + '" text-anchor="middle" font-size="15" fill="#8F8C86">' + m + '</text>'; });
    var pR = d.rev.map(function (v, i) { return X(i) + ',' + Y(v); }).join(' '), pT = d.tgt.map(function (v, i) { return X(i) + ',' + Y(v); }).join(' ');
    var area = 'M' + X(0) + ',' + Y(mn) + ' L' + pR.split(' ').join(' L') + ' L' + X(n - 1) + ',' + Y(mn) + ' Z';
    return '<svg class="plot" viewBox="0 0 ' + W + ' ' + H + '" preserveAspectRatio="none"><defs><linearGradient id="dbA" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="#1E3BFF" stop-opacity=".28"/><stop offset="1" stop-color="#1E3BFF" stop-opacity="0"/></linearGradient></defs>' + g +
      '<path d="' + area + '" fill="url(#dbA)"/><polyline points="' + pT + '" fill="none" stroke="#B5B0A5" stroke-width="2.5" stroke-dasharray="7 7"/><polyline class="ln" points="' + pR + '" fill="none" stroke="#1E3BFF" stroke-width="4" stroke-linejoin="round" stroke-linecap="round" pathLength="1" style="stroke-dasharray:1;stroke-dashoffset:1;animation:dbdraw 1.1s ease-out forwards"/>' +
      '<line class="xh" x1="0" x2="0" y1="' + pt + '" y2="' + (H - pb) + '" stroke="#0B0B0F" stroke-opacity=".25" stroke-width="1.5" style="display:none"/><circle class="dt" r="7" fill="#1E3BFF" stroke="#fff" stroke-width="3" style="display:none"/><circle class="dt2" r="6" fill="#B5B0A5" stroke="#fff" stroke-width="3" style="display:none"/>' +
      '<rect class="hit" x="' + pl + '" y="0" width="' + (W - pl - pr) + '" height="' + H + '" fill="transparent"/></svg>';
  }
  var MIX = [['Consulting', 38, '#1E3BFF'], ['Software', 27, '#4F6BFF'], ['Services', 21, '#D7FF3F'], ['Training', 14, '#0B0B0F']];
  function donut() {
    var R = 92, r = 58, cx = 120, cy = 120, a0 = -Math.PI / 2, s = '';
    MIX.forEach(function (m, i) { var a1 = a0 + m[1] / 100 * Math.PI * 2, lg = a1 - a0 > Math.PI ? 1 : 0;
      var p = 'M' + (cx + R * Math.cos(a0)) + ' ' + (cy + R * Math.sin(a0)) + ' A' + R + ' ' + R + ' 0 ' + lg + ' 1 ' + (cx + R * Math.cos(a1)) + ' ' + (cy + R * Math.sin(a1)) + ' L' + (cx + r * Math.cos(a1)) + ' ' + (cy + r * Math.sin(a1)) + ' A' + r + ' ' + r + ' 0 ' + lg + ' 0 ' + (cx + r * Math.cos(a0)) + ' ' + (cy + r * Math.sin(a0)) + ' Z';
      s += '<path data-i="' + i + '" d="' + p + '" fill="' + m[2] + '" style="cursor:pointer;transition:transform .2s;transform-origin:' + cx + 'px ' + cy + 'px"/>'; a0 = a1; });
    return '<div style="display:flex;align-items:center;gap:18px;flex:1"><svg width="240" height="240" viewBox="0 0 240 240" class="dn">' + s + '<text class="c1" x="120" y="116" text-anchor="middle" font-size="30" font-weight="700" fill="#0B0B0F" font-family="Plus Jakarta Sans">100 %</text><text class="c2" x="120" y="142" text-anchor="middle" font-size="15" fill="#8F8C86">' + tr('of revenue') + '</text></svg>' +
      '<div class="dl" style="flex:1">' + MIX.map(function (m, i) { return '<div class="r" data-i="' + i + '" style="grid-template-columns:18px 1fr auto"><i style="width:14px;height:14px;border-radius:4px;background:' + m[2] + '"></i><span>' + tr(m[0]) + '</span><b>' + nf(m[1]) + ' %</b></div>'; }).join('') + '</div></div>';
  }
  var DEALS = [['Northwind Group', 'Negotiation', 420, 'Camille'], ['Atlas Industries', 'Proposal', 310, 'Lucas'], ['Helios Energy', 'Won', 265, 'Sarah'], ['Orion Retail', 'Qualified', 180, 'Thomas'], ['Vega Logistics', 'Proposal', 150, 'Camille'], ['Nova Health', 'Won', 128, 'Sarah']];
  var STC = { Lead: 'c', Qualified: 'b', Proposal: 'b', Negotiation: 'c', Won: 'a' };
  function dealsTable() {
    var k = REG[S.reg][1] * 2.2, rows = DEALS.map(function (d, i) { return d.concat([i]); });
    var f = S.sort[0], dir = S.sort[1];
    rows.sort(function (a, b) { var A = f === 'amt' ? a[2] : f === 'cli' ? a[0] : a[1], B = f === 'amt' ? b[2] : f === 'cli' ? b[0] : b[1]; return (A > B ? 1 : A < B ? -1 : 0) * dir; });
    var ar = function (x) { return S.sort[0] === x ? (S.sort[1] > 0 ? ' ↑' : ' ↓') : ''; };
    return '<div class="dl"><div class="r h"><span data-sort="cli">' + tr('Client') + ar('cli') + '</span><span data-sort="stg">' + tr('Stage') + ar('stg') + '</span><span data-sort="amt" style="text-align:right">' + tr('Amount') + ar('amt') + '</span></div>' +
      rows.map(function (d) { return '<div class="r' + (S.sel === d[4] ? ' sel' : '') + '" data-d="' + d[4] + '"><span>' + d[0] + '</span><span class="pill ' + STC[d[1]] + '">' + tr(d[1]) + '</span><b style="text-align:right">' + eur(Math.round(d[2] * k), 0, 'k') + '</b></div>'; }).join('') + '</div>';
  }
  function funnel() {
    var k = REG[S.reg][1] * 2.2, st = [['Lead', 214, 9.8], ['Qualified', 128, 6.1], ['Proposal', 64, 3.9], ['Negotiation', 31, 2.2], ['Won', 19, 1.4]];
    return '<div class="dfun">' + st.map(function (s, i) { return '<div class="f" data-tip="' + tr(s[0]) + ' · ' + nf(Math.round(s[1] * k)) + ' ' + tr('deals') + ' · ' + eur(s[2] * k, 1, 'M') + '"><span>' + tr(s[0]) + '</span><div class="bb" style="width:' + (s[1] / 214 * 100) + '%;opacity:' + (1 - i * 0.12) + '"></div><b>' + nf(Math.round(s[1] * k)) + '</b></div>'; }).join('') + '</div>';
  }
  var DEP = [['Sales', 4.2, 4.6], ['Marketing', 2.1, 1.9], ['R&D', 5.4, 5.1], ['Operations', 3.6, 3.8], ['Support', 1.7, 1.6]];
  function budget() {
    var k = REG[S.reg][1] * 2.2 * S.per / 12, W = 1000, H = 300, pl = 56, pb = 40, mx = 6.5 * k, bw = 64;
    var g = '';
    for (var j = 0; j <= 4; j++) { var y = 14 + (H - pb - 14) * j / 4; g += '<line x1="' + pl + '" x2="' + W + '" y1="' + y + '" y2="' + y + '" stroke="#E3DFD3"/><text x="' + (pl - 10) + '" y="' + (y + 5) + '" text-anchor="end" font-size="15" fill="#8F8C86">' + nf(mx * (1 - j / 4), 1) + '</text>'; }
    DEP.forEach(function (d, i) { var x = pl + 40 + i * ((W - pl - 40) / DEP.length);
      [d[1], d[2]].forEach(function (v, k2) { var hgt = v * k / mx * (H - pb - 14); g += '<rect data-tip="' + tr(d[0]) + ' · ' + tr(k2 ? 'Actual' : 'Budget') + ' · ' + eur(v * k, 1, 'M') + '" x="' + (x + k2 * (bw + 8)) + '" y="' + (H - pb - hgt) + '" width="' + bw + '" height="' + hgt + '" rx="8" fill="' + (k2 ? (d[2] > d[1] ? '#C2410C' : '#1E3BFF') : '#D9D4C7') + '" style="cursor:pointer"/>'; });
      g += '<text x="' + (x + bw + 4) + '" y="' + (H - 12) + '" text-anchor="middle" font-size="16" fill="#55534E">' + tr(d[0]) + '</text>'; });
    return '<svg class="plot" viewBox="0 0 ' + W + ' ' + H + '" preserveAspectRatio="none">' + g + '</svg>';
  }
  function people() {
    var k = REG[S.reg][1], D2 = [['Sales', 312], ['Marketing', 96], ['R&D', 418], ['Operations', 284], ['Support', 130]];
    return '<div class="dfun">' + D2.map(function (d) { var v = Math.round(d[1] * k); return '<div class="f" data-tip="' + tr(d[0]) + ' · ' + nf(v) + ' ' + tr('employees') + '"><span>' + tr(d[0]) + '</span><div class="bb" style="width:' + (d[1] / 418 * 100) + '%"></div><b>' + nf(v) + '</b></div>'; }).join('') + '</div>';
  }
  function hires() {
    var H2 = [['Data engineer', 'R&D', 'Interview', 'b'], ['Account executive', 'Sales', 'Offer sent', 'a'], ['Product designer', 'R&D', 'Screening', 'c'], ['Support lead', 'Support', 'Interview', 'b'], ['Controller', 'Finance', 'Screening', 'c']];
    return '<div class="dl"><div class="r h"><span>' + tr('Open position') + '</span><span>' + tr('Team') + '</span><span>' + tr('Status') + '</span></div>' + H2.map(function (h) { return '<div class="r"><span>' + tr(h[0]) + '</span><span>' + tr(h[1]) + '</span><span class="pill ' + h[3] + '">' + tr(h[2]) + '</span></div>'; }).join('') + '</div>';
  }
  var VIEWS = { overview: 'Group overview', sales: 'Sales pipeline', finance: 'Budget vs actual', people: 'People' };
  var main = $('.db-m', db);
  function render() {
    var d = series(), v = S.view, html = '';
    html += '<div class="db-h"><h4>' + tr(VIEWS[v]) + '</h4><div class="seg">' + [[12, '12 months'], [6, '6 months'], [3, '3 months']].map(function (p) { return '<button data-per="' + p[0] + '" class="' + (S.per === p[0] ? 'on' : '') + '">' + tr(p[1]) + '</button>'; }).join('') + '</div>' +
      '<div class="db-sel"><button class="rg">' + tr(REG[S.reg][0]) + '</button><div class="menu">' + Object.keys(REG).map(function (r) { return '<button data-reg="' + r + '" class="' + (S.reg === r ? 'on' : '') + '">' + tr(REG[r][0]) + '</button>'; }).join('') + '</div></div><button class="db-ask"><svg class="ic"><use href="#i-spark"/></svg>' + tr('Ask your data') + '</button></div>';
    html += '<div class="db-k">' + kpis(d) + '</div><div class="db-g">';
    if (v === 'overview') html += '<div class="dc"><div class="ch"><b>' + tr('Revenue vs target') + ' (' + tr('€M') + ')</b><div class="lg"><span><i style="background:#1E3BFF"></i>' + tr('Revenue') + '</span><span><i style="background:#B5B0A5"></i>' + tr('Target') + '</span></div></div>' + lineChart(d) + '</div><div class="dc"><div class="ch"><b>' + tr('Revenue by business line') + '</b></div>' + donut() + '</div>';
    if (v === 'sales') html += '<div class="dc"><div class="ch"><b>' + tr('Pipeline by stage') + '</b></div>' + funnel() + '</div><div class="dc"><div class="ch"><b>' + tr('Top deals') + '</b><span style="font-size:15px;color:#8F8C86">' + tr('Click a column to sort') + '</span></div>' + dealsTable() + '</div>';
    if (v === 'finance') html += '<div class="dc" style="grid-column:span 2"><div class="ch"><b>' + tr('Budget vs actual by department') + ' (' + tr('€M') + ')</b><div class="lg"><span><i style="background:#D9D4C7"></i>' + tr('Budget') + '</span><span><i style="background:#1E3BFF"></i>' + tr('Actual') + '</span><span><i style="background:#C2410C"></i>' + tr('Over budget') + '</span></div></div>' + budget() + '</div>';
    if (v === 'people') html += '<div class="dc"><div class="ch"><b>' + tr('Headcount by team') + '</b></div>' + people() + '</div><div class="dc"><div class="ch"><b>' + tr('Open positions') + '</b></div>' + hires() + '</div>';
    html += '</div><div class="db-tip"></div>';
    main.innerHTML = html; wire(d);
  }
  function wire(d) {
    var tipE = $('.db-tip', main);
    var ask = $('.db-ask', main); if (ask) ask.onclick = function () { if (window.__ycAskOpen) window.__ycAskOpen(); };
    function show(e, h) { var p = local(e, main); tipE.innerHTML = h; tipE.classList.add('on'); var w = tipE.offsetWidth; tipE.style.left = Math.min(main.offsetWidth - w - 10, p.x + 16) + 'px'; tipE.style.top = (p.y - 50) + 'px'; }
    function hide() { tipE.classList.remove('on'); }
    $$('[data-per]', main).forEach(function (b) { b.onclick = function () { S.per = +b.dataset.per; render(); }; });
    var sel = $('.db-sel', main); $('.rg', sel).onclick = function (e) { e.stopPropagation(); sel.classList.toggle('open'); };
    $$('[data-reg]', main).forEach(function (b) { b.onclick = function () { S.reg = b.dataset.reg; render(); }; });
    $$('.dk', main).forEach(function (k) { k.onclick = function () { S.kpi = +k.dataset.k; $$('.dk', main).forEach(function (x) { x.classList.toggle('on', x === k); }); }; });
    $$('[data-tip]', main).forEach(function (x) { x.addEventListener('mousemove', function (e) { show(e, x.dataset.tip.replace(/ · ([^·]+)$/, ' · <b>$1</b>')); }); x.addEventListener('mouseleave', hide); });
    var hit = $('.hit', main);
    if (hit) {
      var svg = hit.ownerSVGElement, xh = $('.xh', svg), dt = $('.dt', svg), dt2 = $('.dt2', svg), n = d.rev.length;
      var all = d.rev.concat(d.tgt), mn = Math.min.apply(0, all) * 0.9, mx = Math.max.apply(0, all) * 1.05;
      hit.addEventListener('mousemove', function (e) {
        var r = svg.getBoundingClientRect(), fx = (e.clientX - r.left) / r.width * 1000; var i = Math.max(0, Math.min(n - 1, Math.round((fx - 56) / (1000 - 72) * (n - 1))));
        var X = 56 + i / (n - 1) * (1000 - 72), Y = function (v) { return 14 + (1 - (v - mn) / (mx - mn)) * (300 - 48); };
        xh.setAttribute('x1', X); xh.setAttribute('x2', X); xh.style.display = ''; dt.setAttribute('cx', X); dt.setAttribute('cy', Y(d.rev[i])); dt.style.display = ''; dt2.setAttribute('cx', X); dt2.setAttribute('cy', Y(d.tgt[i])); dt2.style.display = '';
        var gap = (d.rev[i] / d.tgt[i] - 1) * 100;
        show(e, d.m[i] + '<br>' + tr('Revenue') + ' : <b>' + eur(d.rev[i], 2, 'M') + '</b><br>' + tr('Target') + ' : ' + eur(d.tgt[i], 2, 'M') + ' <span style="color:' + (gap >= 0 ? '#D7FF3F' : '#FFB4A2') + '">(' + (gap >= 0 ? '+' : '') + nf(gap, 1) + ' %)</span>');
      });
      hit.addEventListener('mouseleave', function () { hide(); xh.style.display = dt.style.display = dt2.style.display = 'none'; });
    }
    var dn = $('.dn', main);
    if (dn) { var c1 = $('.c1', dn), c2 = $('.c2', dn);
      function hl(i) { $$('path', dn).forEach(function (p, k) { p.style.transform = k === i ? 'scale(1.07)' : ''; p.style.opacity = i < 0 || k === i ? 1 : 0.35; }); c1.textContent = i < 0 ? '100 %' : nf(MIX[i][1]) + ' %'; c2.textContent = i < 0 ? tr('of revenue') : tr(MIX[i][0]); }
      $$('[data-i]', main).forEach(function (p) { p.addEventListener('mouseenter', function () { hl(+p.dataset.i); }); p.addEventListener('mouseleave', function () { hl(-1); }); });
    }
    $$('[data-sort]', main).forEach(function (h) { h.onclick = function () { var f = h.dataset.sort; S.sort = [f, S.sort[0] === f ? -S.sort[1] : (f === 'amt' ? -1 : 1)]; render(); }; });
    $$('[data-d]', main).forEach(function (r) { r.onclick = function () { S.sel = S.sel === +r.dataset.d ? -1 : +r.dataset.d; render(); }; });
  }
  $$('.db-nav button', db).forEach(function (b) { b.addEventListener('click', function () { S.view = b.dataset.v; $$('.db-nav button', db).forEach(function (x) { x.classList.toggle('on', x === b); }); render(); }); });
  db.addEventListener('click', function (e) { var s = $('.db-sel', main); if (s && !s.contains(e.target)) s.classList.remove('open'); });
  document.addEventListener('yc-lang', render);
  window.__ycDashEnter = function () { render(); };
  window.__ycDash = { S: S, series: series, REG: REG, DEP: DEP, DEALS: DEALS, render: render };
  render();
})();
