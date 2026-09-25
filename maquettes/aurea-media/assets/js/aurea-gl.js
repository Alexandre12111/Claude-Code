/*!
 * Aurea Media : champ de particules en phyllotaxie (angle d'or 137,508°)
 * WebGL natif, sans bibliothèque (environ 4 Ko minifié, contre 600 Ko pour Three.js).
 * Chaque <canvas data-gl> devient une spirale dorée qui s'assemble au chargement,
 * respire, s'écarte du curseur et se déploie au défilement.
 * Rendu suspendu hors écran et onglet masqué ; une seule image fixe si
 * l'utilisateur a demandé moins d'animations.
 */
(function () {
  'use strict';

  var reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  var canvases = document.querySelectorAll('canvas[data-gl]');
  if (!canvases.length) return;

  var VERT = [
    'precision highp float;',
    'attribute float aI;',
    'attribute float aR;',
    'uniform float uN, uTime, uIntro, uScroll, uAspect, uScale, uDpr, uPoint, uForce;',
    'uniform vec2 uCenter, uMouse;',
    'varying float vA;',
    'varying float vR;',
    'const float GA = 2.39996323;',   // angle d'or : 137,508° en radians
    'const float TAU = 6.2831853;',
    'float easeOut(float x){ return 1.0 - pow(1.0 - clamp(x, 0.0, 1.0), 4.0); }',
    'void main(){',
    // Spirale de Vogel : rayon en racine de l'indice, angle multiple de l'angle d'or
    '  float r = sqrt(aI / uN);',
    '  float th = aI * GA + uTime * 0.035 + uScroll * 1.1;',
    '  float rad = r * uScale * (1.0 + 0.018 * sin(uTime * 0.8 - r * 7.0)) * (1.0 + uScroll * 0.7);',
    '  vec2 p = vec2(cos(th), sin(th)) * rad;',
    // Assemblage au chargement : des positions dispersées vers la spirale
    '  vec2 scatter = vec2(cos(aR * 40.0), sin(aR * 57.0)) * (1.4 + aR * 1.2) * uScale;',
    '  float k = easeOut(uIntro * 1.5 - aR * 0.5);',
    '  p = mix(scatter, p, k) + uCenter;',
    // Le curseur écarte doucement les grains
    '  vec2 d = p - uMouse;',
    '  float dist = length(d);',
    '  p += normalize(d + 1e-5) * uForce * exp(-dist * dist * 10.0);',
    '  gl_Position = vec4(p.x / uAspect, p.y, 0.0, 1.0);',
    // Vague lumineuse qui parcourt les 21 bras (nombre de Fibonacci)
    '  float arm = mod(aI, 21.0) / 21.0;',
    '  float wave = pow(0.5 + 0.5 * cos(TAU * arm - uTime * 0.55), 7.0);',
    '  float arm2 = mod(aI, 34.0) / 34.0;',
    '  float wave2 = pow(0.5 + 0.5 * cos(TAU * arm2 + uTime * 0.35), 9.0);',
    '  gl_PointSize = uPoint * (0.8 + r * 0.35 + wave * 0.35) * uDpr;',
    '  vA = (0.28 + 0.95 * wave + 0.4 * wave2) * (1.0 - r * 0.35) * k * (1.0 - uScroll * 0.9);',
    '  vR = r;',
    '}'
  ].join('\n');

  var FRAG = [
    'precision mediump float;',
    'varying float vA;',
    'varying float vR;',
    'void main(){',
    '  float d = length(gl_PointCoord - 0.5);',
    '  float core = 1.0 - smoothstep(0.16, 0.3, d);',
    '  float halo = (1.0 - smoothstep(0.0, 0.5, d)) * 0.3;',
    '  float a = max(core, halo);',
    '  vec3 bright = vec3(0.894, 0.788, 0.541);', // #E4C98A
    '  vec3 gold = vec3(0.788, 0.663, 0.380);',   // #C9A961
    '  vec3 deep = vec3(0.604, 0.478, 0.227);',   // #9A7A3A
    '  vec3 col = mix(bright, gold, smoothstep(0.0, 0.55, vR));',
    '  col = mix(col, deep, smoothstep(0.55, 1.0, vR));',
    '  gl_FragColor = vec4(col, a * vA);',
    '}'
  ].join('\n');

  function shader(gl, type, src) {
    var s = gl.createShader(type);
    gl.shaderSource(s, src);
    gl.compileShader(s);
    if (!gl.getShaderParameter(s, gl.COMPILE_STATUS)) { throw new Error(gl.getShaderInfoLog(s)); }
    return s;
  }

  function init(canvas) {
    var gl = canvas.getContext('webgl', { alpha: true, antialias: false, premultipliedAlpha: false, powerPreference: 'low-power' });
    if (!gl) return;

    var prog;
    try {
      prog = gl.createProgram();
      gl.attachShader(prog, shader(gl, gl.VERTEX_SHADER, VERT));
      gl.attachShader(prog, shader(gl, gl.FRAGMENT_SHADER, FRAG));
      gl.linkProgram(prog);
      if (!gl.getProgramParameter(prog, gl.LINK_STATUS)) return;
    } catch (e) { return; }
    gl.useProgram(prog);

    var mobile = window.matchMedia('(max-width: 820px)').matches;
    var N = parseInt(canvas.dataset.count || (mobile ? 1500 : 2600), 10);
    var idx = new Float32Array(N), rnd = new Float32Array(N);
    for (var i = 0; i < N; i++) { idx[i] = i; rnd[i] = Math.random(); }

    function attr(name, data) {
      var b = gl.createBuffer();
      gl.bindBuffer(gl.ARRAY_BUFFER, b);
      gl.bufferData(gl.ARRAY_BUFFER, data, gl.STATIC_DRAW);
      var loc = gl.getAttribLocation(prog, name);
      gl.enableVertexAttribArray(loc);
      gl.vertexAttribPointer(loc, 1, gl.FLOAT, false, 0, 0);
    }
    attr('aI', idx);
    attr('aR', rnd);

    var U = {};
    ['uN', 'uTime', 'uIntro', 'uScroll', 'uAspect', 'uScale', 'uDpr', 'uPoint', 'uForce', 'uCenter', 'uMouse'].forEach(function (n) {
      U[n] = gl.getUniformLocation(prog, n);
    });
    gl.uniform1f(U.uN, N);
    gl.enable(gl.BLEND);
    gl.blendFunc(gl.SRC_ALPHA, gl.ONE);
    gl.clearColor(0, 0, 0, 0);

    var dpr = Math.min(window.devicePixelRatio || 1, mobile ? 1.25 : 2);
    var minDt = mobile ? 33 : 0, last = 0;
    var W = 0, H = 0, aspect = 1;
    // Position et taille de la spirale selon le format (valeurs en hauteurs d'écran)
    var cfg = {
      cx: parseFloat(canvas.dataset.cx || '0.62'),
      cy: parseFloat(canvas.dataset.cy || '0.08'),
      cxm: parseFloat(canvas.dataset.cxm || '0.62'),
      cym: parseFloat(canvas.dataset.cym || '0.58'),
      scale: parseFloat(canvas.dataset.scale || '0.8'),
      point: parseFloat(canvas.dataset.point || '7.5')
    };
    var center = [0, 0], scale = cfg.scale;

    function resize() {
      var r = canvas.getBoundingClientRect();
      W = Math.max(1, Math.round(r.width * dpr));
      H = Math.max(1, Math.round(r.height * dpr));
      if (canvas.width !== W || canvas.height !== H) { canvas.width = W; canvas.height = H; }
      gl.viewport(0, 0, W, H);
      aspect = W / H;
      var narrow = aspect < 1;
      center = narrow ? [cfg.cxm * aspect, cfg.cym] : [cfg.cx * aspect, cfg.cy];
      scale = narrow ? cfg.scale * (canvas.dataset.cxm ? 0.78 : 0.62) : cfg.scale;
      gl.uniform1f(U.uAspect, aspect);
      gl.uniform1f(U.uScale, scale);
      gl.uniform1f(U.uDpr, dpr);
      gl.uniform1f(U.uPoint, cfg.point);
      gl.uniform2f(U.uCenter, center[0], center[1]);
    }

    var mouse = [99, 99], target = [99, 99], force = 0, forceT = 0;
    var intro = reduce ? 1 : 0, start = performance.now(), t0 = start;
    var visible = true, running = false, raf = 0, scroll = 0;
    var host = canvas.closest('section') || canvas.parentElement;

    function onMove(e) {
      var r = canvas.getBoundingClientRect();
      var x = ((e.clientX - r.left) / r.width) * 2 - 1;
      var y = -(((e.clientY - r.top) / r.height) * 2 - 1);
      target = [x * aspect, y];
      forceT = 0.085;
    }
    if (!reduce && window.matchMedia('(pointer: fine)').matches) {
      host.addEventListener('pointermove', onMove, { passive: true });
      host.addEventListener('pointerleave', function () { forceT = 0; }, { passive: true });
    }

    function frame(now) {
      raf = 0;
      if (!visible) { running = false; return; }
      if (minDt && now - last < minDt) { raf = requestAnimationFrame(frame); return; }
      last = now;
      var t = (now - t0) / 1000;
      if (intro < 1) intro = Math.min(1, (now - start) / 2600);
      mouse[0] += (target[0] - mouse[0]) * 0.08;
      mouse[1] += (target[1] - mouse[1]) * 0.08;
      force += (forceT - force) * 0.06;
      var r = host.getBoundingClientRect();
      scroll = Math.min(1, Math.max(0, -r.top / Math.max(1, r.height)));
      gl.clear(gl.COLOR_BUFFER_BIT);
      gl.uniform1f(U.uTime, reduce ? 12.0 : t);
      gl.uniform1f(U.uIntro, intro);
      gl.uniform1f(U.uScroll, reduce ? 0 : scroll);
      gl.uniform1f(U.uForce, force);
      gl.uniform2f(U.uMouse, mouse[0], mouse[1]);
      gl.drawArrays(gl.POINTS, 0, N);
      if (reduce) { running = false; return; }
      raf = requestAnimationFrame(frame);
    }
    function play() { if (!running && visible) { running = true; raf = requestAnimationFrame(frame); } }

    resize();
    var ro = new ResizeObserver(function () { resize(); if (reduce) play(); });
    ro.observe(canvas);
    new IntersectionObserver(function (entries) {
      visible = entries[0].isIntersecting && !document.hidden;
      if (visible) play();
    }, { rootMargin: '100px' }).observe(canvas);
    document.addEventListener('visibilitychange', function () {
      visible = !document.hidden;
      if (visible) play();
    });
    canvas.addEventListener('webglcontextlost', function (e) { e.preventDefault(); visible = false; });

    document.documentElement.classList.add('has-gl');
    canvas.closest('.hero, .cta, section') && canvas.closest('.hero, .cta, section').classList.add('has-gl');
    play();
  }

  function boot() { for (var i = 0; i < canvases.length; i++) init(canvases[i]); }
  // Démarrage après le chargement complet, quand le navigateur est libre : la spirale
  // ne concurrence jamais l'affichage du contenu (LCP) ni les premières interactions.
  function later() {
    if ('requestIdleCallback' in window) requestIdleCallback(boot, { timeout: 1500 });
    else setTimeout(boot, 300);
  }
  if (document.readyState === 'complete') later();
  else window.addEventListener('load', later, { once: true });
})();
