/* ===== YouCraft apps (v1.7) =====
   Four complete, interactive application screens built on the same design system as the YouCraft film
   (1600 x 1000 virtual canvas): People Hub, Control Tower, Asset Monitor and Client Portal.
   All names and figures are sample data. Text goes through tr() so the deck's language switcher applies. */
(function () {
  'use strict';
  var IC = window.YA_ICONS || {}, PH = window.YA_PHOTOS || {};
  var UM = '#1E3BFF', INK = '#0B0B0F', TX2 = '#6B6B70', TX3 = '#9A9AA0', OKC = '#1C8C4E', BADC = '#CF3B3B', WARNC = '#D9890F';
  function tr(s) { return window.__ycTr ? window.__ycTr(s) : s; }
  function nf(v, d) { return window.__ycNf ? window.__ycNf(v, d || 0) : v.toFixed(d || 0); }
  function ic(n, s, c, sw) { return '<svg class="yi" width="' + s + '" height="' + s + '" viewBox="0 0 24 24" fill="none" stroke="' + (c || 'currentColor') + '" stroke-width="' + (sw || 2) + '" stroke-linecap="round" stroke-linejoin="round">' + (IC[n] || '') + '</svg>'; }
  function av(k, d) { return '<img class="ya-av" src="' + (PH['p' + (k < 10 ? '0' : '') + k] || '') + '" style="width:' + d + 'px;height:' + d + 'px" alt="">'; }
  function D(p, cls, css, html) { var e = document.createElement('div'); if (cls) e.className = cls; if (css) e.style.cssText = css; if (html != null) e.innerHTML = html; if (p) p.appendChild(e); return e; }
  function A(p, css, html, cls) { return D(p, 'a' + (cls ? ' ' + cls : ''), css, html); }
  function $$(s, r) { return Array.prototype.slice.call(r.querySelectorAll(s)); }
  function badge(t, k) { return '<span class="ya-badge ya-' + k + '"><i></i>' + tr(t) + '</span>'; }
  function btn(t, p, icn, cls) { return '<button class="ya-btn' + (p ? ' p' : '') + (cls ? ' ' + cls : '') + '">' + (icn ? ic(icn, 15, p ? '#fff' : TX2) : '') + tr(t) + '</button>'; }
  function bar(v, c, w) { return '<span class="ya-bar" style="width:' + w + 'px"><i style="width:' + Math.round(v * 100) + '%;background:' + c + '"></i></span>'; }
  function spark(vals, w, h, c) {
    var mx = Math.max.apply(0, vals), mn = Math.min.apply(0, vals);
    var pts = vals.map(function (v, i) { return [(i / (vals.length - 1)) * w, h - 2 - ((v - mn) / (mx - mn || 1)) * (h - 4)]; });
    var d = 'M' + pts.map(function (p) { return p[0].toFixed(1) + ' ' + p[1].toFixed(1); }).join(' L');
    return '<svg width="' + w + '" height="' + h + '" viewBox="0 0 ' + w + ' ' + h + '" style="display:block"><path d="' + d + ' L' + w + ' ' + h + ' L0 ' + h + ' Z" fill="' + c + '" opacity=".10"/><path d="' + d + '" fill="none" stroke="' + c + '" stroke-width="1.8" stroke-linejoin="round"/></svg>';
  }
  function card(p, x, y, w, h, title, right) { var c = A(p, 'left:' + x + 'px;top:' + y + 'px;width:' + w + 'px;height:' + h + 'px', '', 'ya-card'); if (title) { D(c, 'ya-ct', '', tr(title)); D(c, 'ya-cr', '', right || ic('ellipsis', 16, TX3)); } return c; }
  function kpi(p, x, y, w, label, icn, val, delta, good, sp, suffix) {
    var c = card(p, x, y, w, 104); c.classList.add('ya-kpi');
    D(c, 'l', '', ic(icn, 14, TX3) + tr(label));
    var v = D(c, 'v', '', val);
    D(c, 'd', 'color:' + (good ? OKC : BADC), delta + ' <span>' + tr(suffix || 'vs prev. quarter') + '</span>');
    D(c, 's', '', spark(sp, 96, 34, good ? OKC : BADC));
    return v;
  }
  function toast(app, msg) { var t = app.root.querySelector('.ya-toast'); if (!t) t = D(app.root, 'ya-toast'); t.innerHTML = '<i>' + ic('check', 12, '#fff', 3) + '</i>' + msg; t.classList.add('on'); clearTimeout(t._t); t._t = setTimeout(function () { t.classList.remove('on'); }, 2200); }
  function local(e, el) { var r = el.getBoundingClientRect(); var k = r.width / el.offsetWidth; return { x: (e.clientX - r.left) / k, y: (e.clientY - r.top) / k }; }
  function tip(app, e, html) { var t = app.root.querySelector('.ya-tip'); if (!t) t = D(app.root, 'ya-tip'); var p = local(e, app.root); t.innerHTML = html; t.classList.add('on'); var w = t.offsetWidth; t.style.left = Math.min(1600 - w - 8, p.x + 14) + 'px'; t.style.top = (p.y - 46) + 'px'; }
  function untip(app) { var t = app.root.querySelector('.ya-tip'); if (t) t.classList.remove('on'); }

  /* ---------- application shell ---------- */
  function shell(app, o) {
    var root = app.root; root.innerHTML = '';
    var sb = A(root, '', '', 'ya-sb');
    A(sb, '', '<span class="mk" style="background:' + o.mark + '">' + ic(o.icon, 17, o.mark === '#D7FF3F' ? INK : '#fff', 2.2) + '</span><span><b>' + o.name + '</b><span>' + (o.org || 'Northwind Group') + ' ▾</span></span>', 'ya-logo');
    A(sb, '', ic('search', 14, TX3) + tr('Search') + '<kbd>⌘K</kbd>', 'ya-srch');
    o.nav.forEach(function (n, i) {
      var b = document.createElement('button'); b.className = 'a ya-nav' + (i === o.active ? ' on' : ''); b.style.top = (112 + i * 34) + 'px';
      b.innerHTML = ic(n[0], 16, i === o.active ? UM : TX2) + tr(n[1]) + (n[2] ? '<span class="bd' + (n[2][0] === '!' ? ' r' : '') + '">' + n[2].replace('!', '') + '</span>' : '');
      b.onclick = function () { if (i !== o.active) toast(app, tr('This view is part of the full application')); };
      sb.appendChild(b);
    });
    var ny = 112 + o.nav.length * 34 + 22;
    A(sb, 'top:' + ny + 'px', tr('SAVED VIEWS'), 'ya-sv');
    ['My team', 'This week', 'To do'].forEach(function (t, i) { A(sb, 'top:' + (ny + 26 + i * 30) + 'px', '<i style="background:' + [UM, OKC, BADC][i] + '"></i>' + tr(t), 'ya-svi'); });
    A(sb, '', av(o.user[0], 30) + '<span>' + o.user[1] + '<span>' + tr('Admin') + '</span></span>', 'ya-me');
    var top = A(root, '', '', 'ya-top');
    A(top, '', o.crumbs.map(function (c, i) { return i === o.crumbs.length - 1 ? '<b>' + tr(c) + '</b>' : tr(c); }).join('<span style="color:' + TX3 + '">/</span>'), 'ya-crumb');
    var trr = A(top, '', (o.topSearch ? '<span class="ya-in" style="height:32px;width:260px">' + ic('search', 13, TX3) + '<input class="tq" placeholder="' + tr(o.topSearch) + '"></span>' : '') + '<span class="ya-bell">' + ic('bell', 18, TX2) + '</span>' + ic('settings', 18, TX2) + av(o.user[0], 30), 'ya-tr');
    var main = A(root, '', '', 'ya-main');
    A(main, '', tr(o.title), 'ya-h1');
    var sub = A(main, '', o.sub, 'ya-sub');
    var act = A(main, '', o.actions || '', 'ya-act');
    return { main: main, sub: sub, act: act, top: trr };
  }
  function live(t) { return '<span class="ya-live"></span><span>' + t + '</span>'; }

  /* ================================================================ 1. PEOPLE HUB */
  var TASKS = ['Contract signed', 'IT account created', 'Laptop shipped', 'Badge ordered', 'Buddy assigned', 'Welcome email sent', 'Security training', 'Tools access granted', 'Desk booked', 'Team lunch booked', 'Manager 1:1 scheduled', 'First-week plan shared'];
  function P0() {
    var R = [
      [0, 'Sarah Martin', 'Consultant', 'Consulting', 'Mon, Nov 3', 5, 'David Cohen', 11, -1, '', '', 1, 'Lucas Moreau'],
      [3, 'Tom Becker', 'Data analyst', 'Data', 'Mon, Nov 3', 4, 'Elena Rossi', 8, 2, 'Laptop in delivery', 'warn', 1, 'Priya Nair'],
      [2, 'Inès Duval', 'Project manager', 'Operations', 'Tue, Nov 4', 20, 'Paul Martin', 10, -1, '', '', 1, 'Jonas Weber'],
      [7, 'Marc Petit', 'Tax specialist', 'Finance', 'Wed, Nov 5', 5, 'David Cohen', 4, 0, 'Contract not signed', 'bad', 1, 'Sofia Alves'],
      [9, 'Amara Okafor', 'Recruiter', 'Human resources', 'Mon, Nov 10', 19, 'Claire Dubois', 12, -1, '', '', 0, 'Ryan Cole'],
      [10, 'Lucas Moreau', 'Engineer', 'Engineering', 'Mon, Nov 10', 4, 'Elena Rossi', 7, 7, 'Access pending', 'warn', 0, 'Tom Becker'],
      [6, 'Yuki Tanaka', 'Product designer', 'Product', 'Mon, Nov 17', 20, 'Paul Martin', 9, -1, '', '', 0, 'Inès Duval'],
      [16, 'Priya Nair', 'Analyst', 'Data', 'Mon, Nov 17', 4, 'Elena Rossi', 6, -1, '', '', 0, 'Tom Becker'],
      [15, 'Jonas Weber', 'Operations lead', 'Operations', 'Mon, Dec 1', 20, 'Paul Martin', 3, -1, '', '', 0, 'Inès Duval'],
      [13, 'Sofia Alves', 'Buyer', 'Purchasing', 'Mon, Dec 1', 5, 'David Cohen', 2, -1, '', '', 0, 'Marc Petit'],
      [1, 'Ryan Cole', 'Senior sales rep', 'Sales', 'Mon, Dec 8', 19, 'Claire Dubois', 1, -1, '', '', 0, 'Amara Okafor'],
    ];
    return R.map(function (r) {
      var t = []; for (var i = 0; i < 12; i++) t.push(i < r[7]); if (r[8] >= 0 && t[r[8]]) { t[r[8]] = false; t[r[7]] = true; }
      return { k: r[0], n: r[1], role: r[2], dep: r[3], date: r[4], mk: r[5], mgr: r[6], tasks: t, blk: r[8], bl: r[9], bk: r[10], week: !!r[11], buddy: r[12] };
    });
  }
  var FEED0 = [[22, '<b>Laptop</b> shipped to <b>Tom Becker</b>', 'just now', 'smartphone'], [0, '<b>Sarah Martin</b> completed the security training', '2 min ago', 'graduation-cap'], [5, '<b>David Cohen</b> granted access to <b>Marc Petit</b>', '6 min ago', 'shield-check'], [9, '<b>Amara Okafor</b> is ready for day one', '14 min ago', 'circle-check'], [20, '<b>Paul Martin</b> booked a team lunch', '22 min ago', 'utensils'], [4, '<b>Elena Rossi</b> picked a buddy for <b>Lucas</b>', '31 min ago', 'users']];
  var FEEDP = [[6, '<b>Yuki Tanaka</b> signed the IT charter', 'shield-check'], [19, '<b>Claire Dubois</b> scheduled 3 welcome meetings', 'calendar'], [16, '<b>Priya Nair</b> received her badge', 'circle-check'], [15, '<b>Jonas Weber</b> opened his onboarding plan', 'list-checks'], [2, '<b>Inès Duval</b> finished the tools training', 'graduation-cap']];
  function pstatus(r) { var d = r.tasks.filter(Boolean).length; if (d === 12) return ['Ready', 'ok', d]; if (r.blk >= 0 && !r.tasks[r.blk]) return [r.bl, r.bk, d]; if (d === 0) return ['Not started', 'neu', d]; return ['On track', 'ok', d]; }
  function people(app) {
    var S = app.state = app.state || { tab: 'all', q: '', dep: null, sort: null, sel: null, rows: P0(), feed: FEED0.map(function (f) { return f.slice(); }), fp: 0, alert: false, added: false };
    var sh = shell(app, { name: 'People Hub', icon: 'users', mark: UM, active: 2, user: [19, 'Claire Dubois'], crumbs: ['Human resources', 'Onboarding', 'Q4 2026'], title: 'New hire onboarding',
      nav: [['house', 'Home'], ['users', 'Employees'], ['user-plus', 'Onboarding', '9'], ['target', 'Recruiting', '12'], ['calendar', 'Time off'], ['layers', 'Org chart'], ['chart-column', 'Reports'], ['settings', 'Settings']],
      sub: live(tr('Live')) + '<span style="color:' + TX3 + '">·</span>' + tr('48 arrivals across 6 departments'),
      actions: btn('Oct 1 – Dec 31, 2026', false, 'calendar') + btn('Filters', false, 'list-checks') + btn('New hire', true, 'plus', 'nh') });
    var m = sh.main;
    var kv = [kpi(m, 28, 92, 318, 'Arrivals this quarter', 'user-plus', nf(48), '+12%', true, [22, 25, 24, 30, 33, 31, 38, 41, 48]),
      kpi(m, 362, 92, 318, 'Ready on day one', 'circle-check', nf(94.2, 1) + ' %', '+' + nf(3.1, 1) + ' ' + tr('pts'), true, [86, 88, 87, 90, 89, 91, 93, 92, 94]),
      kpi(m, 696, 92, 318, 'Time to productivity', 'timer', nf(18.4, 1) + ' ' + tr('days'), '−' + nf(2.6, 1) + ' ' + tr('days'), true, [26, 25, 24, 23, 22, 21, 20, 19, 18]),
      kpi(m, 1030, 92, 318, 'Open onboarding tasks', 'list-checks', '', '−14', true, [61, 58, 55, 52, 50, 46, 44, 40, 37])];
    var baseOpen = 37 - P0().reduce(function (a, r) { return a + 12 - r.tasks.filter(Boolean).length; }, 0);
    var tc = card(m, 28, 216, 900, 712, 'Upcoming arrivals', '<span class="ya-in">' + ic('search', 13, TX3) + '<input class="q" placeholder="' + tr('Search') + '"></span>' + btn('Export', false, 'download', 'sm ex'));
    var tabs = A(tc, 'left:18px;top:50px', '', 'ya-tabs');
    var chip = A(tc, 'left:540px;top:52px;display:none', '', '');
    var ban = A(tc, 'left:18px;top:86px;width:864px;display:none', '', 'ya-banner');
    var COLS = [['EMPLOYEE', 206, 'n'], ['DEPARTMENT', 120, 'dep'], ['START DATE', 100, 'date'], ['MANAGER', 150, ''], ['ONBOARDING', 150, 'prog'], ['STATUS', 150, '']];
    var th = A(tc, 'top:92px;width:900px', '', 'ya-th'); th.classList.add('a');
    var rows = A(tc, 'top:128px;width:900px;height:584px', '', 'ya-rows');
    function renderHead() { th.innerHTML = '<span style="width:18px"></span>' + COLS.map(function (c) { return '<span class="' + (c[2] ? 'srt' : '') + '" data-s="' + c[2] + '" style="width:' + c[1] + 'px">' + tr(c[0]) + (S.sort === c[2] && c[2] ? ' ↓' : '') + '</span>'; }).join(''); $$('.srt', th).forEach(function (s) { s.onclick = function () { S.sort = S.sort === s.dataset.s ? null : s.dataset.s; renderHead(); renderRows(); }; }); }
    function vis() {
      var q = S.q.toLowerCase();
      var L = S.rows.map(function (r, i) { return [r, i]; }).filter(function (x) { var r = x[0], st = pstatus(r);
        if (S.tab === 'week' && !r.week) return false; if (S.tab === 'blocked' && !(st[1] === 'warn' || st[1] === 'bad')) return false; if (S.tab === 'done' && st[2] !== 12) return false;
        if (S.dep && r.dep !== S.dep) return false; if (q && (r.n + ' ' + tr(r.role) + ' ' + tr(r.dep)).toLowerCase().indexOf(q) < 0) return false; return true; });
      if (S.sort === 'prog') L.sort(function (a, b) { return pstatus(b[0])[2] - pstatus(a[0])[2]; });
      if (S.sort === 'n') L.sort(function (a, b) { return a[0].n < b[0].n ? -1 : 1; });
      if (S.sort === 'dep') L.sort(function (a, b) { return tr(a[0].dep) < tr(b[0].dep) ? -1 : 1; });
      return L;
    }
    function renderTabs() {
      var c = { all: 0, week: 0, blocked: 0, done: 0 };
      S.rows.forEach(function (r) { var st = pstatus(r); c.all++; if (r.week) c.week++; if (st[1] === 'warn' || st[1] === 'bad') c.blocked++; if (st[2] === 12) c.done++; });
      tabs.innerHTML = [['all', 'All'], ['week', 'This week'], ['blocked', 'Blocked'], ['done', 'Ready']].map(function (t) { return '<button data-t="' + t[0] + '" class="' + (S.tab === t[0] ? 'on' : '') + '">' + tr(t[1]) + ' <b>' + c[t[0]] + '</b></button>'; }).join('');
      $$('button', tabs).forEach(function (b) { b.onclick = function () { S.tab = b.dataset.t; renderTabs(); renderRows(); }; });
      chip.style.display = S.dep ? 'block' : 'none';
      chip.innerHTML = S.dep ? '<span class="ya-badge ya-info" style="height:26px;cursor:pointer">' + tr('Department') + ' : ' + tr(S.dep) + ' &nbsp;×</span>' : '';
      chip.onclick = function () { S.dep = null; renderTabs(); renderRows(); };
    }
    function renderRows() {
      var L = vis(); rows.innerHTML = '';
      L.forEach(function (x) {
        var r = x[0], st = pstatus(r), row = D(rows, 'ya-row' + (S.sel === x[1] ? ' sel' : '') + (r._flash ? ' flash' : ''), 'width:900px');
        r._flash = false;
        var cells = ['<span class="ya-who">' + av(r.k, 30) + '<span><b>' + r.n + '</b><span>' + tr(r.role) + '</span></span></span>', tr(r.dep), tr(r.date), av(r.mk, 22) + '<span>' + r.mgr + '</span>', bar(st[2] / 12, st[2] === 12 ? OKC : UM, 84) + '<span style="color:' + TX2 + ';font-size:12px">' + st[2] + '/12</span>', badge(st[0], st[1])];
        row.innerHTML = '<span style="width:18px;flex:none"></span>' + cells.map(function (c, i) { return '<div style="width:' + COLS[i][1] + 'px">' + c + '</div>'; }).join('');
        row.onclick = function () { S.sel = x[1]; renderRows(); renderDrawer(); };
      });
      if (!L.length) D(rows, '', 'padding:40px;text-align:center;color:' + TX3, tr('No match'));
      var open = baseOpen + S.rows.reduce(function (a, r) { return a + 12 - r.tasks.filter(Boolean).length; }, 0);
      kv[3].textContent = nf(open);
      ban.style.display = S.alert ? 'flex' : 'none';
      ban.innerHTML = ic('bell', 15, '#A8670A') + tr('Alert rule active: contract not signed 3 days before the start date → the manager is notified');
      rows.style.top = S.alert ? '172px' : '128px'; th.style.top = S.alert ? '136px' : '92px';
    }
    var fc = card(m, 944, 216, 404, 420, 'Live activity', '<span style="display:flex;align-items:center;gap:6px;color:' + OKC + ';font-weight:600">' + live('') + tr('Live') + '</span>');
    var feed = A(fc, 'left:0;top:48px;width:404px;height:372px;overflow:hidden', '');
    function renderFeed(anim) {
      feed.innerHTML = '';
      S.feed.slice(0, 7).forEach(function (f, j) {
        var r = D(feed, 'ya-feed' + (anim && j === 0 ? ' new' : ''), 'top:' + ((anim ? j - 1 : j) * 60) + 'px;height:60px;' + (j ? 'border-top:1px solid #F0EFEC' : ''));
        A(r, 'left:18px;top:12px', av(f[0], 34)); A(r, 'left:62px;top:11px;width:320px;font-size:13px;line-height:1.35;color:#2A2A2F', tr(f[1]));
        A(r, 'left:62px;top:33px;font-size:11.5px;color:' + TX3 + ';display:flex;align-items:center;gap:5px', ic(f[3], 12, TX3) + tr(f[2]));
        if (anim) requestAnimationFrame(function () { requestAnimationFrame(function () { r.style.top = (j * 60) + 'px'; setTimeout(function () { r.classList.remove('new'); }, 1200); }); });
      });
    }
    var dc = card(m, 944, 652, 404, 276, 'Arrivals by department', '<span style="font-size:12px;color:' + TX3 + '">' + tr('Click a bar to filter') + '</span>');
    var DEP = [['Consulting', 'Cons.', 11], ['Data', 'Data', 9], ['Operations', 'Ops', 8], ['Engineering', 'Eng.', 7], ['Finance', 'Fin.', 6], ['Sales', 'Sales', 4], ['Purchasing', 'Purch.', 3]];
    (function () {
      var g = A(dc, 'left:12px;top:50px;width:380px;height:214px'), pl = 34, cw = 340, ch = 184, bw = cw / DEP.length, s = '<svg width="380" height="214" class="a" style="left:0;top:0">';
      for (var k = 0; k <= 4; k++) { var yy = 6 + ch - ch * k / 4; s += '<line x1="' + pl + '" x2="374" y1="' + yy + '" y2="' + yy + '" stroke="#EFEEEA"/><text x="' + (pl - 8) + '" y="' + (yy + 4) + '" text-anchor="end" font-size="11" fill="' + TX3 + '">' + (k * 3) + '</text>'; }
      DEP.forEach(function (d, i) { s += '<text x="' + (pl + bw * (i + .5)) + '" y="208" text-anchor="middle" font-size="11" fill="' + TX3 + '">' + tr(d[1]) + '</text>'; });
      g.innerHTML = s + '</svg>';
      DEP.forEach(function (d, i) { var h = d[2] / 12 * ch, b = A(g, 'left:' + (pl + bw * i + bw * .2) + 'px;top:' + (6 + ch - h) + 'px;width:' + (bw * .6) + 'px;height:' + h + 'px;border-radius:3px 3px 0 0;cursor:pointer;transition:filter .15s;background:' + (S.dep && S.dep !== d[0] ? '#C7CEFA' : (i < 5 ? UM : '#8C9BFF')));
        b.onmousemove = function (e) { b.style.filter = 'brightness(1.15)'; tip(app, e, tr(d[0]) + ' · <span>' + d[2] + ' ' + tr('arrivals') + '</span>'); };
        b.onmouseleave = function () { b.style.filter = ''; untip(app); };
        b.onclick = function () { S.dep = S.dep === d[0] ? null : d[0]; untip(app); people(app); }; });
    })();
    // drawer
    var dr = A(app.root, '', '', 'ya-dr');
    function renderDrawer() {
      if (S.sel === null) { dr.classList.remove('on'); return; }
      var r = S.rows[S.sel], st = pstatus(r);
      dr.innerHTML = '<button class="x">' + ic('x', 18, TX2) + '</button>' +
        '<div class="a" style="left:24px;top:24px;display:flex;gap:14px;align-items:center">' + av(r.k, 56) + '<div><div style="font-size:19px;font-weight:700">' + r.n + '</div><div style="color:' + TX2 + '">' + tr(r.role) + ' · ' + tr(r.dep) + '</div></div></div>' +
        '<div class="a" style="left:24px;top:104px;width:392px;display:grid;grid-template-columns:1fr 1fr;gap:12px">' + [['Start date', tr(r.date)], ['Manager', r.mgr], ['Buddy', r.buddy], ['Status', badge(st[0], st[1])]].map(function (x) { return '<div style="padding:10px 12px;border:1px solid #EFEEEA;border-radius:8px"><div style="font-size:11.5px;color:' + TX3 + ';font-weight:600">' + tr(x[0]).toUpperCase() + '</div><div style="margin-top:4px;font-weight:600">' + x[1] + '</div></div>'; }).join('') + '</div>' +
        '<div class="a" style="left:24px;top:248px;width:392px;display:flex;align-items:center;gap:10px"><b style="font-size:14px">' + tr('Onboarding checklist') + '</b><span style="margin-left:auto;color:' + TX2 + '">' + st[2] + '/12</span></div>' +
        '<div class="a" style="left:24px;top:274px">' + bar(st[2] / 12, st[2] === 12 ? OKC : UM, 392) + '</div>' +
        '<div class="a cl" style="left:14px;top:292px;width:412px"></div>' +
        '<div class="a" style="left:24px;bottom:22px;display:flex;gap:8px">' + btn('Send a reminder', false, 'send', 'rm') + btn('Open profile', true, 'external-link', 'op') + '</div>';
      var cl = dr.querySelector('.cl');
      TASKS.forEach(function (t, i) { var b = document.createElement('button'); b.className = 'ya-chk' + (r.tasks[i] ? ' on' : '') + (i === r.blk && !r.tasks[i] ? ' blk' : ''); b.innerHTML = '<span class="bx">' + (r.tasks[i] ? ic('check', 13, '#fff', 3) : '') + '</span><span>' + tr(t) + '</span>' + (i === r.blk && !r.tasks[i] ? '<span style="margin-left:auto">' + badge('Blocking', 'bad') + '</span>' : '');
        b.onclick = function () { r.tasks[i] = !r.tasks[i]; if (r.tasks[i]) { S.feed.unshift([r.k, '<b>' + r.n + '</b> · ' + tr(t), 'just now', 'circle-check']); renderFeed(true); if (pstatus(r)[2] === 12) toast(app, r.n + ' · ' + tr('ready for day one')); } renderTabs(); renderRows(); renderDrawer(); };
        cl.appendChild(b); });
      dr.querySelector('.x').onclick = function () { S.sel = null; renderRows(); renderDrawer(); };
      dr.querySelector('.rm').onclick = function () { toast(app, tr('Reminder sent to') + ' ' + r.mgr); };
      dr.querySelector('.op').onclick = function () { toast(app, tr('This view is part of the full application')); };
      dr.classList.add('on');
    }
    var qi = tc.querySelector('.q'); qi.value = S.q; qi.oninput = function () { S.q = qi.value; renderRows(); }; qi.onkeydown = function (e) { e.stopPropagation(); };
    tc.querySelector('.ex').onclick = function () { toast(app, tr('Export ready') + ' · ' + vis().length + ' ' + tr('rows') + ' (CSV)'); };
    sh.act.querySelector('.nh').onclick = function () {
      if (S.added) { toast(app, tr('Léa Fontaine is already in the list')); return; }
      S.added = true; S.rows.unshift({ k: 8, n: 'Léa Fontaine', role: 'Data scientist', dep: 'Data', date: 'Mon, Dec 15', mk: 4, mgr: 'Elena Rossi', tasks: [false, false, false, false, false, false, false, false, false, false, false, false], blk: -1, bl: '', bk: '', week: false, buddy: 'Priya Nair', _flash: true });
      if (S.sel !== null) S.sel++; S.tab = 'all'; renderTabs(); renderRows(); toast(app, tr('New hire added') + ' · Léa Fontaine');
    };
    renderHead(); renderTabs(); renderRows(); renderFeed(false); renderDrawer();
    app.tick = function () { if (++S.fp % 8 === 0) { var f = FEEDP[(S.fp / 8) % FEEDP.length]; S.feed.unshift([f[0], f[1], 'just now', f[2]]); S.feed.forEach(function (x, i) { if (i && x[2] === 'just now') x[2] = '1 min ago'; }); renderFeed(true); } };
    app.addAlert = function () { S.alert = true; renderRows(); };
    app.mobile = function () { return { title: 'New hire onboarding', k: [['Arrivals this quarter', nf(48)], ['Ready on day one', nf(94.2, 1) + ' %']], items: S.rows.slice(0, 7).map(function (r) { var st = pstatus(r); return [av(r.k, 36), r.n, tr(r.role) + ' · ' + tr(r.date), st]; }) }; };
  }

  /* ================================================================ 2. CONTROL TOWER */
  var CITY = { PAR: ['Paris', 258, 123], FRA: ['Frankfurt', 618, 92], STR: ['Strasbourg', 598, 216], MUC: ['Munich', 813, 216], GVA: ['Geneva', 516, 390, -12, -8], LYS: ['Lyon', 405, 430], TRN: ['Turin', 598, 513], MIL: ['Milan', 701, 463, 12, -8], MRS: ['Marseille', 444, 668] };
  var ROUTES = [['PAR', 'STR'], ['STR', 'FRA'], ['FRA', 'MUC'], ['MUC', 'MIL'], ['STR', 'GVA'], ['GVA', 'LYS'], ['GVA', 'TRN'], ['LYS', 'TRN'], ['TRN', 'MIL'], ['PAR', 'LYS'], ['LYS', 'MRS']];
  function T0() {
    return [
      { id: 'TR-2041', path: ['PAR', 'LYS', 'TRN', 'MIL'], s: 'late', u: 0.42, v: 0.006, car: 'TransAlp Logistics', drv: [12, 'Mateo Garcia', '+33 6 12 48 20 77'], cargo: '18 pallets · 12.4 t', temp: '4.0 °C', eta: '15:05', plan: '14:30', delay: '+35 min', note: 'Heavy traffic · stopped for 22 min', notified: true },
      { id: 'TR-5120', path: ['PAR', 'STR'], s: 'ok', u: 0.58, v: 0.01, car: 'Northwind Fleet', drv: [3, 'Tom Lefèvre', '+33 6 22 41 08 19'], cargo: '24 pallets · 16.1 t', temp: '—', eta: '13:20', plan: '13:25', delay: '', note: '' },
      { id: 'TR-1187', path: ['FRA', 'MUC'], s: 'ok', u: 0.45, v: 0.011, car: 'Rhein Cargo', drv: [17, 'Jan Schulz', '+49 151 2234 5512'], cargo: '20 pallets · 14.8 t', temp: '—', eta: '12:50', plan: '12:55', delay: '', note: '' },
      { id: 'TR-0906', path: ['LYS', 'GVA', 'STR'], s: 'warn', u: 0.3, v: 0.008, car: 'Northwind Fleet', drv: [22, 'Karim Benali', '+33 6 45 90 12 33'], cargo: '16 pallets · 10.2 t', temp: '—', eta: '17:40', plan: '17:28', delay: '+12 min', note: 'Loading delayed in Lyon' },
      { id: 'TR-2275', path: ['MUC', 'MIL'], s: 'ok', u: 0.4, v: 0.009, car: 'Alpina Trans', drv: [11, 'Luca Bianchi', '+39 347 552 1094'], cargo: '22 pallets · 15.0 t', temp: '—', eta: '16:10', plan: '16:15', delay: '', note: '' },
      { id: 'TR-4410', path: ['TRN', 'GVA'], s: 'ok', u: 0.5, v: 0.012, car: 'Alpina Trans', drv: [18, 'Marco Rossi', '+39 339 118 2240'], cargo: '12 pallets · 7.9 t', temp: '—', eta: '14:45', plan: '14:50', delay: '', note: '' },
      { id: 'TR-3302', path: ['MRS', 'LYS'], s: 'ok', u: 0.35, v: 0.01, car: 'Sud Express', drv: [14, 'Yanis Haddad', '+33 7 81 22 64 05'], cargo: '26 pallets · 17.3 t', temp: '—', eta: '15:30', plan: '15:30', delay: '', note: '' },
      { id: 'TR-3877', path: ['MIL', 'TRN'], s: 'warn', u: 0.62, v: 0.009, car: 'TransAlp Logistics', drv: [7, 'Paolo Conti', '+39 333 701 4468'], cargo: '14 pallets · 9.6 t', temp: '6.2 °C', eta: '13:55', plan: '13:55', delay: '', note: 'Temperature drift 6.2 °C' },
    ];
  }
  var SC = { ok: UM, late: BADC, warn: WARNC };
  function along(path, u) {
    var P = path.map(function (c) { return [CITY[c][1], CITY[c][2]]; }), L = [], tot = 0;
    for (var i = 1; i < P.length; i++) { var l = Math.hypot(P[i][0] - P[i - 1][0], P[i][1] - P[i - 1][1]); L.push(l); tot += l; }
    var d = u * tot;
    for (var j = 0; j < L.length; j++) { if (d <= L[j] || j === L.length - 1) { var f = Math.min(1, d / L[j]); return { x: P[j][0] + (P[j + 1][0] - P[j][0]) * f, y: P[j][1] + (P[j + 1][1] - P[j][1]) * f, a: Math.atan2(P[j + 1][1] - P[j][1], P[j + 1][0] - P[j][0]), seg: j }; } d -= L[j]; }
  }
  function tower(app) {
    var S = app.state = app.state || { trucks: T0(), sel: 0, off: {}, z: 1, ago: 2 };
    var sh = shell(app, { name: 'Control Tower', icon: 'map-pin', mark: INK, active: 1, user: [21, 'Nadia Haddad'], crumbs: ['Logistics', 'Live map'], title: 'Live map',
      nav: [['layout-dashboard', 'Overview'], ['map-pin', 'Live map'], ['inbox', 'Shipments', '214'], ['circle-alert', 'Incidents', '!4'], ['building-2', 'Carriers'], ['house', 'Warehouses'], ['chart-column', 'Analytics']],
      sub: '', actions: btn('Region: Europe', false, 'globe') + btn('Status: in transit', false, 'list-checks') + btn('Share', false, 'external-link', 'shr') });
    function sub() { sh.sub.innerHTML = live(tr('Live · updated') + ' ' + S.ago + ' s ' + tr('ago')) + '<span style="color:' + TX3 + '">·</span>' + tr('38 trucks in transit · Europe'); }
    sub();
    var m = sh.main;
    var mp = A(m, 'left:28px;top:92px;width:930px;height:836px', '', 'ya-map');
    var sea = '<path d="M0 760 C120 720 230 760 330 740 C420 722 470 700 520 720 C600 750 680 720 760 690 C830 664 880 680 930 650 L930 836 L0 836 Z" fill="#DCE6EE"/><path d="M455 395 C480 380 520 384 540 396 C520 404 480 406 455 395 Z" fill="#D5E2EC"/>';
    var bord = '<path d="M520 0 C540 80 560 140 600 170 C640 200 700 190 760 230 C780 260 770 320 720 360 C690 390 650 420 640 470 C630 520 660 560 700 600" fill="none" stroke="#C9CCC4" stroke-width="1.5" stroke-dasharray="6 6"/><path d="M380 0 C420 40 470 60 520 90" fill="none" stroke="#C9CCC4" stroke-width="1.5" stroke-dasharray="6 6"/>';
    var svg = '<svg width="930" height="836" viewBox="0 0 930 836"><g class="zg" style="transform-origin:465px 418px;transition:transform .35s">' + sea + bord +
      '<g class="rt"></g><g class="ct">' + Object.keys(CITY).map(function (k) { var c = CITY[k]; return '<circle cx="' + c[1] + '" cy="' + c[2] + '" r="6" fill="#fff" stroke="#55555A" stroke-width="2"/><text x="' + (c[1] + (c[3] || 10)) + '" y="' + (c[2] + (c[4] || 5)) + '"' + (c[3] < 0 ? ' text-anchor="end"' : '') + ' font-size="15" font-weight="600" fill="#2A2A2F" style="paint-order:stroke" stroke="#EEF0EC" stroke-width="4">' + tr(c[0]) + '</text>'; }).join('') + '</g><g class="tk"></g></g></svg>';
    mp.innerHTML = svg;
    var zg = mp.querySelector('.zg'), rt = mp.querySelector('.rt'), tk = mp.querySelector('.tk');
    var sel = S.trucks[S.sel];
    rt.innerHTML = ROUTES.map(function (r) { var a = CITY[r[0]], b = CITY[r[1]]; var late = sel && sel.path.join() .indexOf(r[0]) >= 0 && sel.path.indexOf(r[1]) >= 0 && Math.abs(sel.path.indexOf(r[0]) - sel.path.indexOf(r[1])) === 1; return '<line x1="' + a[1] + '" y1="' + a[2] + '" x2="' + b[1] + '" y2="' + b[2] + '" stroke="' + (late ? SC[sel.s] : '#9BA8F5') + '" stroke-width="' + (late ? 4 : 3) + '" stroke-linecap="round" opacity="' + (late ? 1 : .75) + '"/>'; }).join('');
    var leg = A(mp, '', '', 'ya-legend');
    function cnt(s) { return S.trucks.filter(function (t) { return t.s === s; }).length; }
    leg.innerHTML = [['ok', 'In transit', 34], ['late', 'Delayed', 4], ['warn', 'Watch', cnt('warn')]].map(function (l) { return '<button data-s="' + l[0] + '" class="' + (S.off[l[0]] ? 'off' : '') + '"><span class="ya-dot" style="background:' + SC[l[0]] + '"></span>' + tr(l[1]) + ' <b>' + l[2] + '</b></button>'; }).join('') + '<span style="display:flex;align-items:center;gap:7px;padding:0 10px;font-size:12.5px;font-weight:600;color:#3C3C42"><span class="ya-dot" style="background:' + OKC + '"></span>' + tr('Delivered today') + ' <b>61</b></span>';
    $$('button', leg).forEach(function (b) { b.onclick = function () { S.off[b.dataset.s] = !S.off[b.dataset.s]; b.classList.toggle('off'); draw(); }; });
    var zm = A(mp, '', '<button class="zi">+</button><button class="zo">−</button>', 'ya-zoom');
    zm.querySelector('.zi').onclick = function () { S.z = Math.min(1.6, S.z + 0.2); zg.style.transform = 'scale(' + S.z + ')'; };
    zm.querySelector('.zo').onclick = function () { S.z = Math.max(1, S.z - 0.2); zg.style.transform = 'scale(' + S.z + ')'; };
    zg.style.transform = 'scale(' + S.z + ')';
    function draw() {
      tk.innerHTML = S.trucks.map(function (t, i) {
        if (S.off[t.s]) return '';
        var p = along(t.path, t.u), c = SC[t.s], on = i === S.sel, deg = p.a * 180 / Math.PI;
        return '<g class="ya-truck" data-i="' + i + '" transform="translate(' + p.x.toFixed(1) + ' ' + p.y.toFixed(1) + ')">' + (on ? '<circle r="22" fill="' + c + '" opacity=".18"><animate attributeName="r" values="16;26;16" dur="1.8s" repeatCount="indefinite"/></circle>' : '') +
          '<circle class="h" r="12" fill="' + c + '" stroke="#fff" stroke-width="3"/><g transform="translate(-7 -7) scale(.58)" fill="none" stroke="#fff" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round">' + (IC.truck || '') + '</g>' +
          '<g transform="translate(18 -26)"><rect width="66" height="22" rx="5" fill="#fff" stroke="#E7E6E2"/><text x="33" y="15" text-anchor="middle" font-size="12" font-weight="700" fill="#111113">' + t.id + '</text></g></g>';
      }).join('');
      $$('.ya-truck', tk).forEach(function (g) { g.onclick = function () { S.sel = +g.dataset.i; tower(app); }; g.onmousemove = function (e) { var t = S.trucks[+g.dataset.i]; tip(app, e, t.id + ' · <span>' + t.path.map(function (c) { return tr(CITY[c][0]); }).join(' → ') + '</span>'); }; g.onmouseleave = function () { untip(app); }; });
    }
    draw();
    // details
    var dc = card(m, 974, 92, 374, 520);
    function details() {
      var t = S.trucks[S.sel], c = SC[t.s];
      var st = t.s === 'late' ? badge('Delayed', 'bad') : t.s === 'warn' ? badge('Watch', 'warn') : badge('On time', 'ok');
      var stops = t.path.map(function (cty, i) { var p = along(t.path, t.u); var done = i <= p.seg; var bad = t.s === 'late' && i === p.seg + 1; return [cty, done, bad]; });
      dc.innerHTML = '<div class="a" style="left:20px;top:18px;display:flex;align-items:center;gap:10px"><b style="font-size:18px">' + t.id + '</b>' + st + '</div>' +
        '<div class="a" style="left:20px;top:48px;color:' + TX2 + '">' + tr(CITY[t.path[0]][0]) + ' → ' + tr(CITY[t.path[t.path.length - 1]][0]) + ' · ' + t.car + '</div>' +
        '<div class="a" style="left:20px;top:80px;width:334px;height:76px;border-radius:9px;padding:12px 14px;background:' + (t.s === 'late' ? '#FFF4F3;border:1px solid #F6D0CC' : '#F6F7FF;border:1px solid #E1E5FF') + '">' +
        '<div style="font-size:12px;font-weight:600;color:' + (t.s === 'late' ? BADC : UM) + '">' + tr('Estimated arrival') + '</div><div style="display:flex;align-items:baseline;gap:10px;margin-top:4px"><b style="font-size:24px">' + t.eta + '</b><b style="color:' + BADC + '">' + t.delay + '</b>' +
        '<span style="margin-left:auto;text-align:right;font-size:12px;color:' + TX2 + '">' + tr('Planned') + ' ' + t.plan + '<br>' + (t.notified ? tr('Client notified') + ' ✓' : (t.delay ? '<button class="nt" style="color:' + UM + ';font-weight:700">' + tr('Notify client') + '</button>' : tr('On schedule'))) + '</span></div></div>' +
        '<div class="a ya-tl" style="left:24px;top:176px;width:330px">' + stops.map(function (s, i) { return '<div class="st ' + (s[1] ? 'ok' : s[2] ? 'bad' : '') + '"><i>' + (s[1] ? ic('check', 11, '#fff', 3) : '') + '</i><b>' + tr(CITY[s[0]][0]) + (i === 0 ? ' · ' + tr('warehouse') : i === stops.length - 1 ? ' · ' + tr('warehouse') : ' · ' + tr('hub')) + '</b><span>' + (s[1] ? tr('Departed') : s[2] ? tr(t.note) : tr('Expected')) + '</span></div>'; }).join('') + '</div>' +
        '<div class="a" style="left:20px;bottom:18px;width:334px;display:flex;align-items:center;gap:10px;padding-top:14px;border-top:1px solid #F0EFEC">' + av(t.drv[0], 38) + '<div><b>' + t.drv[1] + '</b><div style="font-size:12px;color:' + TX2 + '">' + tr('Driver') + ' · ' + t.drv[2] + '</div></div><div style="margin-left:auto;text-align:right;font-size:12px;color:' + TX2 + '">' + tr(t.cargo) + '<br><b style="color:' + (t.temp.indexOf('6.2') >= 0 ? WARNC : OKC) + '">' + t.temp + '</b></div></div>';
      var nt = dc.querySelector('.nt'); if (nt) nt.onclick = function () { t.notified = true; details(); toast(app, tr('Client notified') + ' · ' + t.id); };
    }
    details();
    var inc = card(m, 974, 628, 374, 300, 'Incidents', '<span style="color:' + UM + ';font-weight:600">' + tr('View all') + '</span>');
    var INC = [['TR-2041', 'Traffic jam on the A43 · +35 min', '2 min', 'bad'], ['TR-0906', 'Loading delayed in Lyon · +12 min', '18 min', 'warn'], ['TR-3877', 'Temperature drift 6.2 °C', '41 min', 'warn'], ['TR-1530', 'Missing delivery signature', '1 h', 'neu']];
    INC.forEach(function (x, j) { var r = A(inc, 'left:0;top:' + (52 + j * 60) + 'px;width:374px;height:60px;border-top:1px solid #F0EFEC;cursor:pointer;display:flex;align-items:center;gap:12px;padding:0 18px', badge(x[0], x[3]) + '<span style="flex:1;font-size:13px">' + tr(x[1]) + '</span><span style="font-size:12px;color:' + TX3 + '">' + x[2] + '</span>');
      r.onmouseenter = function () { r.style.background = '#FAFAF8'; }; r.onmouseleave = function () { r.style.background = ''; };
      r.onclick = function () { var i = S.trucks.map(function (t) { return t.id; }).indexOf(x[0]); if (i >= 0) { S.sel = i; tower(app); } else toast(app, x[0] + ' · ' + tr('already delivered, signature requested')); }; });
    sh.act.querySelector('.shr').onclick = function () { toast(app, tr('Link copied · read-only access')); };
    var acc = 0, last = 0;
    app.frame = function (dt) { S.trucks.forEach(function (t) { t.u += t.v * dt * (t.s === 'late' ? 0.5 : 1); if (t.u > 1) t.u = 0; }); draw(); acc += dt; if (acc > 1) { acc = 0; S.ago = S.ago >= 9 ? 1 : S.ago + 1; sub(); if (++last % 3 === 0) details(); } };
    app.mobile = function () { return { title: 'Live map', k: [['In transit', '34'], ['Delayed', '4']], items: S.trucks.slice(0, 7).map(function (t) { return ['<span class="ya-ini" style="background:' + SC[t.s] + ';color:#fff">' + ic('truck', 15, '#fff') + '</span>', t.id, tr(CITY[t.path[0]][0]) + ' → ' + tr(CITY[t.path[t.path.length - 1]][0]) + ' · ' + t.eta, t.s === 'late' ? ['Delayed', 'bad'] : t.s === 'warn' ? ['Watch', 'warn'] : ['On time', 'ok']]; }) }; };
  }

  /* ================================================================ 3. ASSET MONITOR */
  var MACH = [['Press no. 4', '800 t hydraulic press', 'PR-LY-004', 62, 'Alert', 'bad', 4.4, 1], ['Press no. 2', '600 t hydraulic press', 'PR-LY-002', 91, 'Running', 'ok', 2.6, 0], ['Welding cell W3', 'Robotic welding cell', 'WC-LY-003', 78, 'Watch', 'warn', 4.6, 0], ['Paint booth P1', 'Paint booth', 'PB-LY-001', 94, 'Running', 'ok', 2.1, 0], ['Conveyor C7', 'Belt conveyor', 'CV-LY-007', 71, 'Belt wear', 'warn', 5.1, 0], ['Robot R12', '6-axis robot', 'RB-LY-012', 96, 'Running', 'ok', 1.7, 0]];
  function assets(app) {
    var S = app.state = app.state || { m: 0, rng: 0, t: 0, x: [], y: [], wo: [['OT-1287', 'Press no. 4 · vibration above threshold', 'Lyon', 'High', 'bad', 22, 'Karim Benali', 14363, 'Assigned', 'info'], ['OT-1286', 'Robot R2 · gearbox temperature', 'Łódź', 'Medium', 'warn', 17, 'Hugo Laurent', 102020, 'In progress', 'warn'], ['OT-1285', 'Conveyor C7 · replace the belt', 'Turin', 'Medium', 'warn', 18, 'Kwame Mensah', 212700, 'Parts ordered', 'neu'], ['OT-1284', 'Paint booth P1 · filter change', 'Lyon', 'Low', 'neu', 12, 'Julien Leroy', 441600, 'Planned', 'neu']], n: 1288 };
    function seed() { S.x = []; S.y = []; for (var i = 0; i < 120; i++) push(i); }
    function val(i) { var M = MACH[S.m], b = M[6], tr2 = M[7] ? Math.max(0, (i - 80) / 40) * 3.6 : 0; return Math.max(0.2, b + Math.sin(i * 0.45) * 0.45 + Math.sin(i * 1.7) * 0.25 + (Math.random() - 0.5) * 0.5 + tr2); }
    function push(i) { var v = val(i); S.x.push(v); S.y.push(Math.max(0.2, v * 0.62 + (Math.random() - 0.5) * 0.4)); if (S.x.length > 120) { S.x.shift(); S.y.shift(); } }
    if (!S.x.length) seed();
    var M = MACH[S.m];
    var sh = shell(app, { name: 'Asset Monitor', icon: 'gauge', mark: UM, active: 3, user: [5, 'Karim Benali'], crumbs: ['Lyon plant', 'Press line', M[0]], title: M[0] + ' · ' + tr(M[1]),
      nav: [['layout-dashboard', 'Overview'], ['building-2', 'Sites', '42'], ['layers', 'Equipment'], ['activity', 'Sensors'], ['clipboard-check', 'Work orders', '23'], ['folder', 'Spare parts'], ['calendar', 'Schedules']],
      sub: '<span class="ya-dot" style="background:' + OKC + '"></span>' + tr('Live stream · 50 Hz') + '<span style="color:' + TX3 + '">·</span>' + tr('Machine') + ' ' + M[2] + ' · 18 ' + tr('sensors'),
      actions: '<span class="ya-tabs rg" style="align-items:center">' + ['15 min', '1 h', '24 h'].map(function (r, i) { return '<button data-r="' + i + '" class="' + (S.rng === i ? 'on' : '') + '" style="height:34px">' + tr(r) + '</button>'; }).join('') + '</span>' + btn('Compare', false, 'chart-column', 'cmp') + btn('Create work order', true, 'plus', 'cwo') });
    var m = sh.main;
    var K = [kpi(m, 28, 92, 318, 'Health score', 'gauge', M[3] + ' / 100', (M[3] < 80 ? '−' : '+') + Math.abs(M[3] - 83) + ' ' + tr('pts'), M[3] >= 80, M[3] < 80 ? [86, 85, 83, 80, 78, 74, 70, 66, 62] : [80, 82, 84, 85, 86, 88, 89, 90, M[3]]),
      kpi(m, 362, 92, 318, 'Vibration (RMS)', 'activity', '', M[7] ? '+38 %' : '−4 %', !M[7], M[7] ? [4, 4.2, 4.1, 4.5, 4.9, 5.6, 6.4, 7.1, 7.8] : [3, 2.9, 3.1, 2.8, 2.9, 2.7, 2.8, 2.7, 2.6]),
      kpi(m, 696, 92, 318, 'Oil temperature', 'thermometer', '', M[7] ? '+' + nf(6.2, 1) + ' °C' : '−' + nf(0.4, 1) + ' °C', !M[7], M[7] ? [64, 65, 65, 66, 67, 68, 69, 70, 71] : [58, 58, 57, 58, 57, 57, 56, 57, 56]),
      kpi(m, 1030, 92, 318, 'Plant efficiency', 'trending-up', nf(87.4, 1) + ' %', '+' + nf(2.1, 1) + ' ' + tr('pts'), true, [82, 83, 83, 84, 85, 85, 86, 87, 87.4])];
    var ch = card(m, 28, 216, 900, 400);
    var chHead = A(ch, 'left:18px;top:15px;display:flex;gap:10px;align-items:baseline;font-size:14px;font-weight:700', '');
    A(ch, 'right:18px;top:16px;display:flex;gap:16px;font-size:12px;color:' + TX2, '<span><span style="display:inline-block;width:14px;height:3px;background:' + UM + ';vertical-align:middle;margin-right:6px"></span>' + tr('Axis X') + '</span><span><span style="display:inline-block;width:14px;height:3px;background:#9BA8F5;vertical-align:middle;margin-right:6px"></span>' + tr('Axis Y') + '</span>');
    var pl = 44, cw = 900 - 18 - pl - 14, chh = 400 - 56 - 34, X0 = 18 + pl, Y0 = 56, ymax = 10, THR = 7;
    var plot = A(ch, 'left:0;top:0;width:900px;height:400px');
    function sy(v) { return Y0 + chh - v / ymax * chh; }
    function clk(i) { var span = [15 * 60, 3600, 86400][S.rng], t0 = 10 * 3600 + 45 * 60 - span, s = Math.round(t0 + (i / 119) * span) + S.t; s = ((s % 86400) + 86400) % 86400; var h = Math.floor(s / 3600), mi = Math.floor(s % 3600 / 60), se = s % 60; return (h < 10 ? '0' : '') + h + ':' + (mi < 10 ? '0' : '') + mi + (S.rng === 2 ? '' : ':' + (se < 10 ? '0' : '') + se); }
    function drawChart() {
      var s = '<svg width="900" height="400" class="a" style="left:0;top:0">';
      for (var k = 0; k <= 5; k++) { var v = k * 2, y = sy(v); s += '<line x1="' + X0 + '" x2="' + (X0 + cw) + '" y1="' + y + '" y2="' + y + '" stroke="#EFEEEA"/><text x="' + (X0 - 10) + '" y="' + (y + 4) + '" text-anchor="end" font-size="11" fill="' + TX3 + '">' + v + '</text>'; }
      for (var j = 0; j < 6; j++) { var i = Math.round(j * 119 / 5); s += '<text x="' + (X0 + i / 119 * cw) + '" y="' + (Y0 + chh + 22) + '" text-anchor="middle" font-size="11" fill="' + TX3 + '">' + clk(i) + '</text>'; }
      s += '<line x1="' + X0 + '" x2="' + (X0 + cw) + '" y1="' + sy(THR) + '" y2="' + sy(THR) + '" stroke="' + BADC + '" stroke-width="1.5" stroke-dasharray="5 4"/><text x="' + (X0 + 6) + '" y="' + (sy(THR) - 6) + '" font-size="11" font-weight="600" fill="' + BADC + '">' + tr('Threshold') + ' ' + THR + ' mm/s</text>';
      function pth(a) { return 'M' + a.map(function (v, i) { return (X0 + i / 119 * cw).toFixed(1) + ' ' + sy(Math.min(ymax, v)).toFixed(1); }).join(' L'); }
      var dx = pth(S.x);
      s += '<path d="' + dx + ' L' + (X0 + cw) + ' ' + (Y0 + chh) + ' L' + X0 + ' ' + (Y0 + chh) + ' Z" fill="' + UM + '" opacity=".08"/><path d="' + pth(S.y) + '" fill="none" stroke="#9BA8F5" stroke-width="1.8" stroke-linejoin="round"/><path d="' + dx + '" fill="none" stroke="' + UM + '" stroke-width="2.4" stroke-linejoin="round"/>';
      var lv = S.x[119]; s += '<circle cx="' + (X0 + cw) + '" cy="' + sy(Math.min(ymax, lv)) + '" r="4.5" fill="#fff" stroke="' + (lv > THR ? BADC : UM) + '" stroke-width="2.4"/>';
      var ai = -1; for (var q = 0; q < 120; q++) if (S.x[q] > THR) { ai = q; break; }
      if (ai >= 0) { var ax = X0 + ai / 119 * cw; s += '<line x1="' + ax + '" x2="' + ax + '" y1="' + Y0 + '" y2="' + (Y0 + chh) + '" stroke="' + BADC + '" stroke-opacity=".35" stroke-dasharray="3 3"/><g transform="translate(' + Math.min(ax - 10, X0 + cw - 210) + ' ' + (sy(THR) - 44) + ')"><rect width="200" height="26" rx="6" fill="#111113"/><circle cx="14" cy="13" r="5" fill="none" stroke="#FF8A80" stroke-width="2"/><text x="26" y="17" font-size="12" font-weight="600" fill="#fff">' + tr('Anomaly detected') + ' · ' + clk(ai) + '</text></g>'; }
      s += '<line class="xh" x1="0" x2="0" y1="' + Y0 + '" y2="' + (Y0 + chh) + '" stroke="#111113" stroke-opacity=".3" style="display:none"/><rect class="hit" x="' + X0 + '" y="' + Y0 + '" width="' + cw + '" height="' + chh + '" fill="transparent"/></svg>';
      plot.innerHTML = s;
      chHead.innerHTML = tr('Vibration · live') + ' <span style="color:' + (lv > THR ? BADC : UM) + ';font-weight:700">' + nf(lv, 2) + ' mm/s</span>';
      K[1].textContent = nf(lv, 2) + ' mm/s'; K[2].textContent = nf(60 + MACH[S.m][6] * 2.2 + (lv - MACH[S.m][6]) * 0.6, 1) + ' °C';
      var hit = plot.querySelector('.hit'), xh = plot.querySelector('.xh');
      hit.onmousemove = function (e) { var p = local(e, ch), i = Math.max(0, Math.min(119, Math.round((p.x - X0) / cw * 119))); xh.setAttribute('x1', X0 + i / 119 * cw); xh.setAttribute('x2', X0 + i / 119 * cw); xh.style.display = ''; tip(app, e, clk(i) + '<br>' + tr('Axis X') + ' <b>' + nf(S.x[i], 2) + '</b> · ' + tr('Axis Y') + ' <b>' + nf(S.y[i], 2) + '</b> <span>mm/s</span>'); app._hover = true; };
      hit.onmouseleave = function () { xh.style.display = 'none'; untip(app); app._hover = false; };
    }
    drawChart();
    var eq = card(m, 944, 216, 404, 400, 'Lyon plant · equipment', '<span style="color:' + UM + ';font-weight:600">42 ' + tr('sites') + ' ▾</span>');
    var el2 = A(eq, 'left:0;top:48px;width:404px', '');
    MACH.forEach(function (x, i) { var b = document.createElement('button'); b.className = 'ya-eq' + (i === S.m ? ' on' + (x[5] === 'bad' ? '' : ' okk') : ''); b.innerHTML = '<b>' + tr(x[0]) + '</b>' + badge(x[4], x[5]) + '<div style="display:flex;align-items:center;gap:10px;margin-top:8px">' + bar(x[3] / 100, x[5] === 'bad' ? BADC : x[5] === 'warn' ? WARNC : OKC, 160) + '<span style="font-size:12px;color:' + TX2 + '">' + x[3] + '</span></div>';
      b.onclick = function () { if (S.m === i) return; S.m = i; seed(); assets(app); }; el2.appendChild(b); });
    var wo = card(m, 28, 632, 1320, 296, 'Work orders', btn('All sites', false, 'building-2', 'sm') + btn('Priority', false, 'list-checks', 'sm'));
    var WC = [['ID', 110], ['MACHINE AND ISSUE', 400], ['SITE', 110], ['PRIORITY', 130], ['TECHNICIAN', 210], ['TIME LEFT', 150], ['STATUS', 170]];
    A(wo, 'top:50px;width:1320px', '<span style="width:18px"></span>' + WC.map(function (c) { return '<span style="width:' + c[1] + 'px">' + tr(c[0]) + '</span>'; }).join(''), 'ya-th');
    var wr = A(wo, 'top:86px;width:1320px;height:210px', '', 'ya-rows');
    function fmtd(s) { var d = Math.floor(s / 86400), h = Math.floor(s % 86400 / 3600), mi = Math.floor(s % 3600 / 60), se = s % 60; var p = function (n) { return (n < 10 ? '0' : '') + n; }; return d ? d + ' ' + tr('d') + ' ' + p(h) + ':' + p(mi) : h + ':' + p(mi) + ':' + p(se); }
    function renderWo() { wr.innerHTML = ''; S.wo.slice(0, 4).forEach(function (w) { var r = D(wr, 'ya-row' + (w[10] ? ' flash' : ''), 'width:1320px'); w[10] = false;
      var cells = ['<b style="color:' + UM + ';font-weight:600">' + w[0] + '</b>', (function () { var p = w[1].split(' · '); return tr(p[0]) + ' · ' + tr(p[1]); })(), w[2], badge(w[3], w[4]), av(w[5], 26) + '<span>' + w[6] + '</span>', '<span class="cd" style="font-variant-numeric:tabular-nums">' + fmtd(w[7]) + '</span>', badge(w[8], w[9])];
      r.innerHTML = '<span style="width:18px;flex:none"></span>' + cells.map(function (c, i) { return '<div style="width:' + WC[i][1] + 'px">' + c + '</div>'; }).join('');
      r.onclick = function () { toast(app, w[0] + ' · ' + tr('assigned to') + ' ' + w[6]); }; }); }
    renderWo();
    $$('.rg button', sh.act).forEach(function (b) { b.onclick = function () { S.rng = +b.dataset.r; $$('.rg button', sh.act).forEach(function (x) { x.classList.toggle('on', x === b); }); drawChart(); }; });
    sh.act.querySelector('.cmp').onclick = function () { toast(app, tr('Comparison with Press no. 2 added')); };
    sh.act.querySelector('.cwo').onclick = function () { var M2 = MACH[S.m]; S.wo.unshift(['OT-' + S.n++, M2[0] + ' · ' + (M2[7] ? 'vibration above threshold' : 'preventive inspection'), 'Lyon', M2[7] ? 'High' : 'Low', M2[7] ? 'bad' : 'neu', 22, 'Karim Benali', 14400, 'Assigned', 'info', true]); renderWo(); toast(app, tr('Work order created') + ' · OT-' + (S.n - 1)); };
    var acc = 0, acc2 = 0, k = 120;
    app.frame = function (dt) { acc += dt; acc2 += dt; if (acc > 0.5) { acc = 0; k++; S.t += [8, 30, 720][S.rng]; push(MACH[S.m][7] ? 118 + Math.sin(k * 0.05) * 2 : k); if (!app._hover) drawChart(); } if (acc2 > 1) { acc2 = 0; S.wo.forEach(function (w) { w[7] = Math.max(0, w[7] - 1); }); var cds = $$('.cd', wr); S.wo.slice(0, 4).forEach(function (w, i) { if (cds[i]) cds[i].textContent = fmtd(w[7]); }); } };
    app.mobile = function () { return { title: 'Equipment', k: [['Health score', MACH[S.m][3] + ' / 100'], ['Plant efficiency', nf(87.4, 1) + ' %']], items: MACH.map(function (x) { return ['<span class="ya-ini">' + ic('gauge', 15, TX2) + '</span>', tr(x[0]), tr(x[1]), [x[4], x[5]]]; }) }; };
  }

  /* ================================================================ 4. CLIENT PORTAL */
  var DOCS = ['Certificate of incorporation', 'Articles of association', 'ID of the legal representative', 'Proof of address', 'Bank details (IBAN)', 'Beneficial owners declaration', 'Last annual accounts', 'Tax certificate', 'Signed engagement letter', 'Data processing agreement'];
  function C0() {
    return [['NL', 'Northwind Logistics', 19, 'Claire Dubois', 'onb', 6, 'Nov 14'], ['BE', 'Bluebay Energy', 5, 'Mark Allen', 'val', 10, 'Nov 2'], ['AR', 'Atlas Retail', 13, 'Sofia Alves', 'miss', 3, 'Nov 20'], ['HP', 'Helio Pharma', 15, 'Jonas Weber', 'onb', 7, 'Nov 12'], ['VI', 'Vertex Industries', 8, 'Lena Novak', 'val', 10, 'Oct 30'], ['OB', 'Orion Bank', 1, 'Ryan Cole', 'rev', 10, 'Nov 9'], ['CT', 'Celta Telecom', 16, 'Priya Nair', 'miss', 4, 'Nov 22'], ['KC', 'Kappa Chemicals', 10, 'Lucas Moreau', 'onb', 5, 'Nov 18'], ['ML', 'Meridian Labs', 9, 'Amara Okafor', 'val', 10, 'Oct 28'], ['SG', 'Solis Group', 3, 'Tom Becker', 'miss', 2, 'Nov 27']]
      .map(function (r) { var d = []; for (var i = 0; i < 10; i++) d.push(i < r[5]); return { ini: r[0], n: r[1], k: r[2], c: r[3], st: r[4], docs: d, due: r[6] }; });
  }
  var CST = { onb: ['In progress', 'info'], val: ['Validated', 'ok'], miss: ['Missing documents', 'warn'], rev: ['In review', 'neu'] };
  function cstat(r) { if (r.st === 'val') return 'val'; var n = r.docs.filter(Boolean).length; if (n === 10) return 'rev'; return r.st === 'miss' ? 'miss' : 'onb'; }
  function portal(app) {
    var S = app.state = app.state || { tab: 'all', q: '', sel: null, rows: C0(), added: false };
    var sh = shell(app, { name: 'Client Portal', icon: 'building-2', mark: UM, active: 1, user: [19, 'Claire Dubois'], crumbs: ['Clients', 'All clients'], title: 'Client onboarding', topSearch: 'Search a client',
      nav: [['layout-dashboard', 'Dashboard'], ['building-2', 'Clients'], ['folder', 'Documents'], ['clipboard-check', 'Validations', '5'], ['message-square', 'Messages', '2'], ['settings', 'Settings']],
      sub: live(tr('Live')) + '<span style="color:' + TX3 + '">·</span>' + tr('24 clients · 3 advisers'), actions: btn('Export', false, 'download', 'ex') + btn('Add a client', true, 'plus', 'add') });
    var m = sh.main;
    var K = [kpi(m, 28, 92, 318, 'Active clients', 'users', '24', '+3', true, [15, 16, 17, 18, 19, 20, 21, 22, 24], 'this month'), kpi(m, 362, 92, 318, 'In onboarding', 'loader-circle', '', '+2', true, [5, 6, 6, 7, 7, 8, 8, 9, 9], 'this month'),
      kpi(m, 696, 92, 318, 'Average onboarding time', 'timer', nf(6.5, 1) + ' ' + tr('d'), '−' + nf(1.2, 1) + ' ' + tr('d'), true, [9.1, 8.8, 8.2, 7.9, 7.5, 7.1, 6.9, 6.7, 6.5], 'this month'), kpi(m, 1030, 92, 318, 'Pending validations', 'clipboard-check', '', '−2', true, [9, 8, 8, 7, 7, 6, 6, 5, 5], 'this month')];
    var tc = card(m, 28, 216, 1320, 712);
    var tabs = A(tc, 'left:18px;top:18px', '', 'ya-tabs');
    A(tc, 'right:18px;top:20px;font-size:12.5px;color:' + TX3, tr('Showing 10 of 24 clients'));
    var COLS = [['CLIENT', 330], ['CONTACT', 230], ['STATUS', 190], ['DOCUMENTS', 250], ['DUE DATE', 150], ['', 120]];
    A(tc, 'top:62px;width:1320px', '<span style="width:18px"></span>' + COLS.map(function (c) { return '<span style="width:' + c[1] + 'px">' + tr(c[0]) + '</span>'; }).join(''), 'ya-th');
    var rows = A(tc, 'top:98px;width:1320px;height:614px', '', 'ya-rows');
    function vis() { var q = S.q.toLowerCase(); return S.rows.map(function (r, i) { return [r, i]; }).filter(function (x) { var s = cstat(x[0]); if (S.tab !== 'all' && s !== S.tab) return false; if (q && (x[0].n + ' ' + x[0].c).toLowerCase().indexOf(q) < 0) return false; return true; }); }
    function render() {
      var c = { all: S.rows.length, onb: 0, miss: 0, val: 0, rev: 0 }; S.rows.forEach(function (r) { c[cstat(r)]++; });
      tabs.innerHTML = [['all', 'All'], ['onb', 'In progress'], ['miss', 'Missing documents'], ['rev', 'In review'], ['val', 'Validated']].map(function (t) { return '<button data-t="' + t[0] + '" class="' + (S.tab === t[0] ? 'on' : '') + '">' + tr(t[1]) + ' <b>' + c[t[0]] + '</b></button>'; }).join('');
      $$('button', tabs).forEach(function (b) { b.onclick = function () { S.tab = b.dataset.t; render(); }; });
      K[1].textContent = nf(c.onb + c.miss); K[3].textContent = nf(c.rev);
      rows.innerHTML = '';
      vis().forEach(function (x) { var r = x[0], s = cstat(r), n = r.docs.filter(Boolean).length, row = D(rows, 'ya-row' + (S.sel === x[1] ? ' sel' : '') + (r._f ? ' flash' : ''), 'width:1320px;height:58px'); r._f = false;
        var cells = ['<span class="ya-ini">' + r.ini + '</span><b style="font-weight:600;font-size:13.5px">' + r.n + '</b>', av(r.k, 28) + '<span>' + r.c + '</span>', badge(CST[s][0], CST[s][1]), bar(n / 10, n === 10 ? OKC : UM, 150) + '<span style="color:' + TX2 + ';font-size:12px">' + n + '/10</span>', tr(r.due), '<span style="color:' + TX3 + '">' + ic('ellipsis', 16, TX3) + '</span>'];
        row.innerHTML = '<span style="width:18px;flex:none"></span>' + cells.map(function (cc, i) { return '<div style="width:' + COLS[i][1] + 'px">' + cc + '</div>'; }).join('');
        row.onclick = function () { S.sel = x[1]; render(); }; });
      if (!vis().length) D(rows, '', 'padding:40px;text-align:center;color:' + TX3, tr('No match'));
      drawer();
    }
    var dr = A(app.root, '', '', 'ya-dr');
    function drawer() {
      if (S.sel === null) { dr.classList.remove('on'); return; }
      var r = S.rows[S.sel], s = cstat(r), n = r.docs.filter(Boolean).length;
      dr.innerHTML = '<button class="x">' + ic('x', 18, TX2) + '</button><div class="a" style="left:24px;top:24px;display:flex;gap:14px;align-items:center"><span class="ya-ini" style="width:52px;height:52px;font-size:16px;border-radius:12px">' + r.ini + '</span><div><div style="font-size:19px;font-weight:700">' + r.n + '</div><div style="margin-top:4px">' + badge(CST[s][0], CST[s][1]) + '</div></div></div>' +
        '<div class="a" style="left:24px;top:100px;width:392px;display:flex;align-items:center;gap:10px;padding:12px;border:1px solid #EFEEEA;border-radius:9px">' + av(r.k, 34) + '<div><b>' + r.c + '</b><div style="font-size:12px;color:' + TX2 + '">' + tr('Main contact') + ' · ' + tr('due') + ' ' + tr(r.due) + '</div></div></div>' +
        '<div class="a" style="left:24px;top:178px;width:392px;display:flex"><b style="font-size:14px">' + tr('Documents') + '</b><span style="margin-left:auto;color:' + TX2 + '">' + n + '/10</span></div><div class="a" style="left:24px;top:202px">' + bar(n / 10, n === 10 ? OKC : UM, 392) + '</div><div class="a cl" style="left:14px;top:220px;width:412px"></div>' +
        '<div class="a" style="left:24px;bottom:22px;display:flex;gap:8px">' + btn('Request missing documents', false, 'send', 'rq') + btn(s === 'val' ? 'Validated' : 'Validate the client', true, 'circle-check', 'vl') + '</div>';
      var cl = dr.querySelector('.cl');
      DOCS.forEach(function (d, i) { var b = document.createElement('button'); b.className = 'ya-chk' + (r.docs[i] ? ' on' : ''); b.innerHTML = '<span class="bx">' + (r.docs[i] ? ic('check', 13, '#fff', 3) : '') + '</span><span>' + tr(d) + '</span>' + (r.docs[i] ? '<span style="margin-left:auto;font-size:12px;color:' + TX3 + '">PDF</span>' : '<span style="margin-left:auto">' + badge('Missing', 'warn') + '</span>');
        b.onclick = function () { if (r.st === 'val') return; r.docs[i] = !r.docs[i]; render(); }; cl.appendChild(b); });
      dr.querySelector('.x').onclick = function () { S.sel = null; render(); };
      dr.querySelector('.rq').onclick = function () { toast(app, tr('Reminder sent to') + ' ' + r.c); };
      var vl = dr.querySelector('.vl'); if (s !== 'val' && n < 10) { vl.style.opacity = '.45'; vl.title = tr('All documents are needed'); }
      vl.onclick = function () { if (s === 'val') return; if (n < 10) { toast(app, tr('All documents are needed') + ' · ' + (10 - n) + ' ' + tr('missing')); return; } r.st = 'val'; r._f = true; render(); toast(app, r.n + ' · ' + tr('client validated')); };
      dr.classList.add('on');
    }
    var tq = sh.top.querySelector('.tq'); tq.value = S.q; tq.oninput = function () { S.q = tq.value; render(); }; tq.onkeydown = function (e) { e.stopPropagation(); };
    sh.act.querySelector('.ex').onclick = function () { toast(app, tr('Export ready') + ' · ' + vis().length + ' ' + tr('rows') + ' (CSV)'); };
    sh.act.querySelector('.add').onclick = function () { if (S.added) { toast(app, tr('Nova Mobility is already in the list')); return; } S.added = true; S.rows.unshift({ ini: 'NM', n: 'Nova Mobility', k: 6, c: 'Yuki Tanaka', st: 'onb', docs: [false, false, false, false, false, false, false, false, false, false], due: 'Dec 5', _f: true }); if (S.sel !== null) S.sel++; S.tab = 'all'; render(); toast(app, tr('Client added') + ' · Nova Mobility · ' + tr('invitation sent')); };
    render();
    app.mobile = function () { return { title: 'Clients', k: [['Active clients', '24'], ['Pending validations', String(S.rows.filter(function (r) { return cstat(r) === 'rev'; }).length)]], items: S.rows.slice(0, 7).map(function (r) { var s = cstat(r); return ['<span class="ya-ini">' + r.ini + '</span>', r.n, r.c + ' · ' + r.docs.filter(Boolean).length + '/10', CST[s]]; }) }; };
  }

  /* ---------- phone preview ---------- */
  function mobile(host, app) {
    var d = app.mobile(); host.innerHTML = '';
    var ph = D(host, 'ya-ph', 'left:0;top:0'); D(ph, 'nt'); var sc = D(ph, 'scr');
    A(sc, 'left:0;top:0;width:100%;height:96px;background:#fff;border-bottom:1px solid #E7E6E2', '');
    A(sc, 'left:22px;top:52px;display:flex;align-items:center;gap:10px;font-weight:700;font-size:16px', '<span style="width:28px;height:28px;border-radius:8px;background:' + app.mark + ';display:flex;align-items:center;justify-content:center">' + ic(app.icon, 15, '#fff', 2.2) + '</span>' + app.label);
    A(sc, 'right:20px;top:50px', av(19, 30));
    A(sc, 'left:22px;top:116px;font-size:21px;font-weight:700;letter-spacing:-.02em', tr(d.title));
    d.k.forEach(function (k, i) { A(sc, 'left:' + (22 + i * 172) + 'px;top:156px;width:160px;height:84px;border-radius:12px;background:#fff;border:1px solid #E7E6E2;padding:12px', '<div style="font-size:11.5px;color:' + TX2 + '">' + tr(k[0]) + '</div><div style="font-size:22px;font-weight:700;margin-top:8px">' + k[1] + '</div>'); });
    var ls = A(sc, 'left:14px;top:256px;width:334px;border-radius:12px;background:#fff;border:1px solid #E7E6E2;overflow:hidden', '');
    d.items.forEach(function (it, i) { D(ls, '', 'display:flex;align-items:center;gap:10px;padding:10px 12px;' + (i ? 'border-top:1px solid #F0EFEC' : ''), it[0] + '<div style="flex:1;min-width:0"><div style="font-weight:600;font-size:13.5px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">' + it[1] + '</div><div style="font-size:11.5px;color:' + TX2 + ';white-space:nowrap;overflow:hidden;text-overflow:ellipsis">' + it[2] + '</div></div>' + badge(it[3][0], it[3][1]).replace('ya-badge', 'ya-badge" style="font-size:10.5px;height:20px;padding:0 6px')); });
    A(sc, 'left:0;bottom:0;width:100%;height:70px;background:#fff;border-top:1px solid #E7E6E2;display:flex;justify-content:space-around;align-items:center;padding-bottom:10px', ['house', app.icon, 'bell', 'settings'].map(function (n, i) { return ic(n, 20, i === 1 ? UM : TX3); }).join(''));
  }

  /* ---------- public API ---------- */
  var DEF = { people: [people, 'People Hub', 'users', UM, 'people-hub.northwind.youcraft.build/onboarding'], tower: [tower, 'Control Tower', 'map-pin', INK, 'control-tower.northwind.youcraft.build/live-map'], assets: [assets, 'Asset Monitor', 'gauge', UM, 'asset-monitor.northwind.youcraft.build/sensors/PR-LY-004'], portal: [portal, 'Client Portal', 'building-2', UM, 'client-portal.northwind.youcraft.build/clients'] };
  var LIVE = [];
  function create(name, host) {
    var d = DEF[name], root = D(host, 'ya'), app = { name: name, root: root, host: host, label: d[1], icon: d[2], mark: d[3], url: d[4], running: false, state: null };
    app.render = function () { d[0](app); app.fit(); };
    app.fit = function () {
      var w = host.clientWidth || host.offsetWidth, h = host.clientHeight || host.offsetHeight, k;
      if (host.dataset.fit === 'w') { k = w / 1600; var sp = host.querySelector('.ya-sp') || D(host, 'ya-sp', 'position:absolute;left:0;top:0;width:1px;pointer-events:none'); sp.style.height = Math.round(1000 * k) + 'px'; }
      else k = Math.min(w / 1600, h ? h / 1000 : 9);
      root.style.transform = 'scale(' + k + ')'; root.style.left = Math.max(0, (w - 1600 * k) / 2) + 'px'; };
    app.start = function () { if (app.running) return; app.running = true; var last = performance.now(), acc = 0; (function f(t) { if (!app.running) return; var dt = Math.min(0.1, (t - last) / 1000); last = t; if (app.frame) app.frame(dt); acc += dt; if (acc > 0.8 && app.tick) { acc = 0; app.tick(); } requestAnimationFrame(f); })(last); };
    app.stop = function () { app.running = false; };
    app.destroy = function () { app.stop(); var i = LIVE.indexOf(app); if (i >= 0) LIVE.splice(i, 1); root.remove(); };
    app.render(); LIVE.push(app); return app;
  }
  document.addEventListener('yc-lang', function () { LIVE.forEach(function (a) { if (a.root.isConnected) a.render(); }); });
  window.addEventListener('resize', function () { LIVE.forEach(function (a) { a.fit(); }); });
  window.YA = { create: create, mobile: mobile, DEF: DEF, ic: ic, av: av };
})();
