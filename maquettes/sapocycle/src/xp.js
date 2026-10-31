/* Expérience 3D : la seconde vie d'un savon, pilotée par le défilement.
   window.SoapXP(canvas, opts) renvoie { setProgress(p), setPointer(x, y), resize(), dispose() }. */
(function () {
  'use strict';
  if (!window.THREE) return;
  const T = window.THREE;
  const clamp01 = (v) => Math.min(1, Math.max(0, v));
  const seg = (p, a, b) => clamp01((p - a) / (b - a));
  const ease = (t) => t * t * (3 - 2 * t);
  const easeOut = (t) => 1 - Math.pow(1 - t, 3);
  const lerp = (a, b, t) => a + (b - a) * t;

  // Forme du savon : rectangle très arrondi, extrudé avec un biseau doux
  function soapGeometry(w, h, r, depth, bevel, segs) {
    const s = new T.Shape();
    const x = -w / 2, y = -h / 2;
    s.moveTo(x + r, y);
    s.lineTo(x + w - r, y);
    s.quadraticCurveTo(x + w, y, x + w, y + r);
    s.lineTo(x + w, y + h - r);
    s.quadraticCurveTo(x + w, y + h, x + w - r, y + h);
    s.lineTo(x + r, y + h);
    s.quadraticCurveTo(x, y + h, x, y + h - r);
    s.lineTo(x, y + r);
    s.quadraticCurveTo(x, y, x + r, y);
    const g = new T.ExtrudeGeometry(s, { depth, bevelEnabled: true, bevelThickness: bevel, bevelSize: bevel * 0.9, bevelSegments: segs, curveSegments: 40, steps: 1 });
    g.center();
    g.computeVertexNormals();
    return g;
  }

  // Relief du logo, dessiné dans une texture (blanc = haut, gris = creux)
  function embossTexture() {
    const c = document.createElement('canvas');
    c.width = 1024; c.height = 683;
    const g = c.getContext('2d');
    g.fillStyle = '#fff';
    g.fillRect(0, 0, c.width, c.height);
    g.fillStyle = '#3f3f3f';
    g.strokeStyle = '#3f3f3f';
    g.textAlign = 'left';
    g.font = '400 200px Comfortaa, "Arial Rounded MT Bold", Arial, sans-serif';
    g.save();
    g.translate(512, 342);
    g.rotate(-0.07);
    g.fillText('sapo', -260, -10);
    g.fillText('cycle', -230, 160);
    g.lineWidth = 15;
    g.beginPath(); g.arc(225, -70, 66, 0, Math.PI * 2); g.stroke();
    g.beginPath(); g.arc(200, -88, 9, 0, Math.PI * 2); g.fill();
    g.beginPath(); g.arc(250, -88, 9, 0, Math.PI * 2); g.fill();
    g.beginPath(); g.arc(225, -62, 34, 0.25 * Math.PI, 0.75 * Math.PI); g.stroke();
    g.lineWidth = 11;
    [[160, -190, 26], [215, -225, 17], [258, -186, 13], [292, -165, 10]].forEach((b) => { g.beginPath(); g.arc(b[0], b[1], b[2], 0, Math.PI * 2); g.stroke(); });
    g.restore();
    // Flou léger pour un relief moulé
    const c2 = document.createElement('canvas');
    c2.width = c.width; c2.height = c.height;
    const g2 = c2.getContext('2d');
    g2.filter = 'blur(5px)';
    g2.drawImage(c, 0, 0);
    const tex = new T.CanvasTexture(c2);
    tex.wrapS = tex.wrapT = T.ClampToEdgeWrapping;
    return tex;
  }

  function dotTexture() {
    const c = document.createElement('canvas');
    c.width = c.height = 64;
    const g = c.getContext('2d');
    const gr = g.createRadialGradient(32, 32, 0, 32, 32, 32);
    gr.addColorStop(0, 'rgba(255,255,255,1)');
    gr.addColorStop(0.45, 'rgba(255,255,255,.9)');
    gr.addColorStop(1, 'rgba(255,255,255,0)');
    g.fillStyle = gr;
    g.fillRect(0, 0, 64, 64);
    return new T.CanvasTexture(c);
  }

  function shadowTexture() {
    const c = document.createElement('canvas');
    c.width = c.height = 256;
    const g = c.getContext('2d');
    const gr = g.createRadialGradient(128, 128, 0, 128, 128, 128);
    gr.addColorStop(0, 'rgba(6,24,43,.38)');
    gr.addColorStop(0.6, 'rgba(6,24,43,.1)');
    gr.addColorStop(1, 'rgba(6,24,43,0)');
    g.fillStyle = gr;
    g.fillRect(0, 0, 256, 256);
    return new T.CanvasTexture(c);
  }

  window.SoapXP = function (canvas, opts) {
    const o = Object.assign({ particles: 5200, crowd: 90 }, opts || {});
    const renderer = new T.WebGLRenderer({ canvas, antialias: true, alpha: true, powerPreference: 'high-performance' });
    renderer.setPixelRatio(Math.min(window.devicePixelRatio || 1, 1.75));
    renderer.outputEncoding = T.sRGBEncoding;
    renderer.toneMapping = T.ACESFilmicToneMapping;
    renderer.toneMappingExposure = 0.92;

    const scene = new T.Scene();
    const camera = new T.PerspectiveCamera(30, 1, 0.1, 100);
    camera.position.set(0, 0.4, 13);

    scene.add(new T.HemisphereLight(0xffffff, 0x8a9bb0, 0.55));
    const key = new T.DirectionalLight(0xfffaf2, 1.6);
    key.position.set(-5, 7, 5);
    scene.add(key);
    const rim = new T.DirectionalLight(0x7dbbf2, 0.9);
    rim.position.set(5, -2, -6);
    scene.add(rim);
    const fill = new T.PointLight(0xfff4e6, 0.35);
    fill.position.set(3, 2, 6);
    scene.add(fill);

    const W = 3.2, H = 2.1, R = 0.95, D = 0.62, BV = 0.36;
    const geo = soapGeometry(W, H, R, D, BV, 14);
    const bump = embossTexture();
    // Les UV des faces suivent les coordonnées de la forme : on les ramène sur [0, 1]
    bump.repeat.set(1 / (W + BV * 1.8), 1 / (H + BV * 1.8));
    bump.offset.set(0.5, 0.5);

    const uniforms = { uErode: { value: 0 }, uTime: { value: 0 } };
    const mat = new T.MeshPhysicalMaterial({ color: 0xece6da, roughness: 0.48, metalness: 0, clearcoat: 0.35, clearcoatRoughness: 0.45, bumpMap: bump, bumpScale: 0.13, transparent: true });
    mat.onBeforeCompile = (sh) => {
      sh.uniforms.uErode = uniforms.uErode;
      sh.uniforms.uTime = uniforms.uTime;
      sh.vertexShader = sh.vertexShader
        .replace('#include <common>', `#include <common>
          uniform float uErode; uniform float uTime;
          float h3(vec3 p){ p = fract(p * 0.3183099 + 0.1); p *= 17.0; return fract(p.x * p.y * p.z * (p.x + p.y + p.z)); }
          float n3(vec3 x){ vec3 i = floor(x); vec3 f = fract(x); f = f * f * (3.0 - 2.0 * f);
            return mix(mix(mix(h3(i), h3(i + vec3(1,0,0)), f.x), mix(h3(i + vec3(0,1,0)), h3(i + vec3(1,1,0)), f.x), f.y),
                       mix(mix(h3(i + vec3(0,0,1)), h3(i + vec3(1,0,1)), f.x), mix(h3(i + vec3(0,1,1)), h3(i + vec3(1,1,1)), f.x), f.y), f.z); }`)
        .replace('#include <begin_vertex>', `#include <begin_vertex>
          float er = uErode * (0.55 + 0.9 * n3(position * 1.6 + 3.0));
          transformed -= normal * er * 0.42;
          transformed.z *= 1.0 - uErode * 0.38;
          transformed.xy *= 1.0 - uErode * 0.16;`);
    };

    const pivot = new T.Group();
    scene.add(pivot);
    // Tranche sans logo : même matière, sans relief
    const sideMat = new T.MeshPhysicalMaterial({ color: 0xece6da, roughness: 0.48, metalness: 0, clearcoat: 0.35, clearcoatRoughness: 0.45, transparent: true });
    sideMat.onBeforeCompile = mat.onBeforeCompile;
    const soap = new T.Mesh(geo, [mat, sideMat]);
    pivot.add(soap);

    const shadow = new T.Mesh(new T.PlaneGeometry(6, 3.2), new T.MeshBasicMaterial({ map: shadowTexture(), transparent: true, depthWrite: false }));
    shadow.rotation.x = -Math.PI / 2;
    shadow.position.y = -2.1;
    scene.add(shadow);

    // Foule de savons : collecte dans les hôtels, puis distribution aux familles
    const smallGeo = soapGeometry(W, H, R, D, BV, 4);
    const crowdMat = new T.MeshStandardMaterial({ color: 0xf2eee6, roughness: 0.6, transparent: true });
    const crowd = new T.InstancedMesh(smallGeo, crowdMat, o.crowd);
    crowd.instanceMatrix.setUsage(T.DynamicDrawUsage);
    scene.add(crowd);
    const cs = [];
    for (let i = 0; i < o.crowd; i++) {
      const a = Math.random() * Math.PI * 2, rr = 6 + Math.random() * 6;
      const t = i / o.crowd, ang = t * Math.PI * 9, rad = 1.6 + t * 6.5;
      cs.push({
        from: new T.Vector3(Math.cos(a) * rr, (Math.random() - 0.5) * 7, -2 - Math.random() * 6),
        pile: new T.Vector3((Math.random() - 0.5) * 3.2, -1.7 + Math.random() * 0.7, (Math.random() - 0.5) * 1.8),
        out: new T.Vector3(Math.cos(ang) * rad, Math.sin(ang) * rad * 0.55 + (Math.random() - 0.5) * 0.8, -1 - t * 4 + Math.random()),
        rot: new T.Euler(Math.random() * 6, Math.random() * 6, Math.random() * 6),
        spin: (Math.random() - 0.5) * 2,
        s: 0.09 + Math.random() * 0.06,
        delay: Math.random() * 0.35
      });
    }
    const m4 = new T.Matrix4(), q = new T.Quaternion(), sv = new T.Vector3(), pv = new T.Vector3();

    // Copeaux : l'atelier râpe le savon, puis le reforme
    const N = o.particles;
    const pGeo = new T.BufferGeometry();
    const pos = new Float32Array(N * 3), A = new Float32Array(N * 3), B = new Float32Array(N * 3), dl = new Float32Array(N);
    for (let i = 0; i < N; i++) {
      // A : un point dans le volume du savon (rectangle arrondi)
      let x, y;
      do { x = (Math.random() - 0.5) * (W + 0.5); y = (Math.random() - 0.5) * (H + 0.5); } while (Math.pow(Math.abs(x) / ((W + 0.5) / 2), 4) + Math.pow(Math.abs(y) / ((H + 0.5) / 2), 4) > 1);
      A[i * 3] = x; A[i * 3 + 1] = y; A[i * 3 + 2] = (Math.random() - 0.5) * (D + BV * 1.6);
      // B : un nuage en tourbillon
      const th = Math.random() * Math.PI * 2, ph = Math.acos(2 * Math.random() - 1), rr = 1.6 + Math.pow(Math.random(), 1.4) * 2.8;
      B[i * 3] = Math.sin(ph) * Math.cos(th) * rr * 1.35; B[i * 3 + 1] = Math.cos(ph) * rr * 0.75; B[i * 3 + 2] = Math.sin(ph) * Math.sin(th) * rr;
      dl[i] = Math.random();
    }
    pGeo.setAttribute('position', new T.BufferAttribute(pos, 3));
    const pMat = new T.PointsMaterial({ size: 0.11, map: dotTexture(), color: 0xf6f2ea, transparent: true, depthWrite: false, opacity: 0, blending: T.AdditiveBlending });
    const points = new T.Points(pGeo, pMat);
    pivot.add(points);

    let progress = 0, shown = 0, t0 = performance.now(), raf = null, alive = true;
    const pointer = { x: 0, y: 0, sx: 0, sy: 0 };
    let layout = { x: 0, y: 0, s: 1 };

    function resize() {
      const w = canvas.clientWidth, h = canvas.clientHeight;
      if (!w || !h) return;
      renderer.setSize(w, h, false);
      camera.aspect = w / h;
      camera.updateProjectionMatrix();
      const narrow = w < 820;
      layout = narrow ? { x: 0, y: 1.3, s: 0.66 } : { x: w / h > 1.5 ? 2.2 : 1.7, y: 0.05, s: 1.22 };
    }

    function frame(now) {
      if (!alive) return;
      const time = (now - t0) / 1000;
      uniforms.uTime.value = time;
      shown += (progress - shown) * 0.09;
      const p = shown;
      pointer.sx += (pointer.x - pointer.sx) * 0.05;
      pointer.sy += (pointer.y - pointer.sy) * 0.05;

      // Étapes : 0 chambre, 1 usure, 2 collecte, 3 atelier, 4 renaissance, 5 familles, 6 final
      const wear = ease(seg(p, 0.1, 0.27));
      const collect = ease(seg(p, 0.27, 0.42));
      const shatter = seg(p, 0.42, 0.57);
      const reform = seg(p, 0.57, 0.7);
      const give = ease(seg(p, 0.71, 0.87));
      const fin = ease(seg(p, 0.87, 1));

      // Position du savon principal dans l'écran
      const centerPull = Math.max(ease(seg(p, 0.42, 0.5)), 0) * (1 - ease(seg(p, 0.86, 0.95)));
      pivot.position.x = lerp(layout.x, layout.x * 0.45, centerPull) + pointer.sx * 0.25;
      pivot.position.y = layout.y + Math.sin(time * 0.9) * 0.08 - collect * (1 - shatter) * 0.25;
      const sc = layout.s * (1 + 0.12 * ease(seg(p, 0, 0.1)) - 0.12 * give + 0.08 * fin);
      pivot.scale.setScalar(sc);

      // Rotation : présentation, puis vrille pendant l'atelier, puis face au visiteur
      // Face visible pendant l'usure, vrille pendant l'atelier, présentation à la fin
      const spinAtelier = ease(seg(p, 0.4, 0.66)) * Math.PI * 2;
      const rotY = -0.5 + Math.sin(time * 0.35) * 0.18 + spinAtelier + give * 0.5 - fin * 0.5;
      const rotX = -0.62 + wear * (1 - reform) * 0.18 + pointer.sy * 0.22 + Math.sin(time * 0.5) * 0.04;
      pivot.rotation.set(rotX, rotY + pointer.sx * 0.35, 0.16);

      // Usure : le savon se creuse puis redevient neuf après l'atelier
      uniforms.uErode.value = wear * (1 - ease(reform));
      const meshAlpha = (1 - ease(seg(p, 0.42, 0.47))) + ease(seg(p, 0.64, 0.7));
      mat.opacity = clamp01(meshAlpha);
      soap.visible = mat.opacity > 0.01;
      mat.bumpScale = 0.13 * (1 - wear * (1 - reform));
      sideMat.opacity = mat.opacity;

      // Copeaux
      const showP = seg(p, 0.41, 0.45) * (1 - seg(p, 0.66, 0.71));
      pMat.opacity = showP;
      points.visible = showP > 0.01;
      if (points.visible) {
        for (let i = 0; i < N; i++) {
          const d = dl[i] * 0.4;
          const k1 = easeOut(clamp01((shatter - d * 0.5) / (1 - d * 0.5)));
          const k2 = ease(clamp01((reform - d * 0.4) / 0.6));
          const sw = time * (0.25 + dl[i] * 0.3) + i;
          const bx = B[i * 3] * Math.cos(sw * 0.15) - B[i * 3 + 2] * Math.sin(sw * 0.15);
          const bz = B[i * 3] * Math.sin(sw * 0.15) + B[i * 3 + 2] * Math.cos(sw * 0.15);
          const by = B[i * 3 + 1] + Math.sin(sw) * 0.15;
          const ex = lerp(A[i * 3] * 0.8, bx, k1), ey = lerp(A[i * 3 + 1] * 0.8, by, k1), ez = lerp(A[i * 3 + 2], bz, k1);
          pos[i * 3] = lerp(ex, A[i * 3], k2);
          pos[i * 3 + 1] = lerp(ey, A[i * 3 + 1], k2);
          pos[i * 3 + 2] = lerp(ez, A[i * 3 + 2], k2);
        }
        pGeo.attributes.position.needsUpdate = true;
      }

      // Foule de savons
      const crowdIn = collect * (1 - seg(p, 0.42, 0.46));
      const crowdOut = give * (1 - seg(p, 0.9, 0.97));
      crowd.visible = crowdIn > 0.01 || crowdOut > 0.01;
      crowdMat.opacity = Math.max(crowdIn, crowdOut);
      if (crowd.visible) {
        for (let i = 0; i < o.crowd; i++) {
          const c = cs[i];
          if (crowdOut > crowdIn) {
            const k = ease(clamp01((give - c.delay) / (1 - c.delay)));
            pv.set(lerp(0, c.out.x, k) + pivot.position.x * 0, lerp(0, c.out.y, k), lerp(0, c.out.z, k));
            sv.setScalar(c.s * layout.s * (0.4 + 0.6 * k));
          } else {
            const k = ease(clamp01((collect - c.delay) / (1 - c.delay)));
            pv.set(lerp(c.from.x, c.pile.x + pivot.position.x, k), lerp(c.from.y, c.pile.y + pivot.position.y, k), lerp(c.from.z, c.pile.z, k));
            sv.setScalar(c.s * layout.s);
          }
          q.setFromEuler(new T.Euler(c.rot.x + time * c.spin * 0.3, c.rot.y + time * c.spin * 0.2, c.rot.z));
          m4.compose(pv, q, sv);
          crowd.setMatrixAt(i, m4);
        }
        crowd.instanceMatrix.needsUpdate = true;
      }

      shadow.position.x = pivot.position.x;
      shadow.material.opacity = clamp01(mat.opacity) * (1 - centerPull * 0.6);
      shadow.scale.setScalar(sc);

      renderer.render(scene, camera);
      raf = requestAnimationFrame(frame);
    }

    resize();
    raf = requestAnimationFrame(frame);

    return {
      setProgress(p) { progress = clamp01(p); },
      jump(p) { progress = shown = clamp01(p); },
      setPointer(x, y) { pointer.x = x; pointer.y = y; },
      resize,
      pause() { if (raf) cancelAnimationFrame(raf); raf = null; },
      play() { if (!raf && alive) raf = requestAnimationFrame(frame); },
      dispose() { alive = false; if (raf) cancelAnimationFrame(raf); renderer.dispose(); }
    };
  };
})();
