import React, { useCallback, useLayoutEffect, useRef, useState } from "react";
import {
  AbsoluteFill,
  continueRender,
  delayRender,
  Easing,
  interpolate,
  spring,
  useCurrentFrame,
  useVideoConfig,
} from "remotion";
import { CategoryPanel, FichePanel, FormPanel, FormState } from "../calc/Calculator";
import { Cursor } from "../components/Cursor";
import { LogoIcon } from "../components/Logo";
import {
  ANSWERS,
  BRAND,
  CLICKS,
  NOMBRE_TYPED,
  NOMBRE_VALUE,
  PANELS,
  PROFILE,
  SCENES,
  TYPING,
} from "../config";

// Zoom appliqué au calculateur pour rester lisible sur mobile.
const CALC_W = 600; // largeur "navigateur" en px CSS
const S = 1.7; // 600 x 1.7 = 1020 px dans la vidéo
const VIEW_TOP = 210;
const VIEW_H = 900;
const VIEW_LH = VIEW_H / S; // hauteur visible en px CSS

type Rect = { x: number; y: number; w: number; h: number };
type Rects = Record<string, Rect>;

const ease = Easing.inOut(Easing.cubic);

// Interpolation entre images clés {f, v}
const track = (frame: number, keys: { f: number; v: number }[]) => {
  if (frame <= keys[0].f) return keys[0].v;
  for (let i = 0; i < keys.length - 1; i++) {
    const a = keys[i];
    const b = keys[i + 1];
    if (frame <= b.f) {
      if (b.f === a.f) return b.v;
      return interpolate(frame, [a.f, b.f], [a.v, b.v], { easing: ease });
    }
  }
  return keys[keys.length - 1].v;
};

const typed = (frame: number, text: string, [a, b]: [number, number]) => {
  if (frame < a) return "";
  const n = Math.round(interpolate(frame, [a, b], [0, text.length], { extrapolateRight: "clamp" }));
  return text.slice(0, n);
};

export const CalcScene: React.FC = () => {
  const frame = useCurrentFrame();
  const { fps } = useVideoConfig();

  // --- Mesure de la mise en page réelle (positions des éléments) ---
  const els = useRef<Record<string, HTMLElement | null>>({});
  const rootRef = useRef<HTMLDivElement>(null);
  const [rects, setRects] = useState<Rects | null>(null);
  const [handle] = useState(() => delayRender("Mesure du calculateur"));
  const reg = useCallback((key: string) => (el: HTMLElement | null) => {
    els.current[key] = el;
  }, []);

  useLayoutEffect(() => {
    let cancelled = false;
    const measure = () => {
      if (cancelled) return;
      const root = rootRef.current;
      if (!root) return;
      const r0 = root.getBoundingClientRect();
      const out: Rects = {};
      for (const [k, el] of Object.entries(els.current)) {
        if (!el) continue;
        const r = el.getBoundingClientRect();
        out[k] = { x: (r.left - r0.left) / S, y: (r.top - r0.top) / S, w: r.width / S, h: r.height / S };
      }
      setRects(out);
      continueRender(handle);
    };
    document.fonts.ready.then(() => requestAnimationFrame(measure));
    return () => {
      cancelled = true;
    };
  }, [handle]);

  const R = (k: string): Rect => rects?.[k] ?? { x: 0, y: 0, w: 0, h: 0 };
  const radio = (k: string) => {
    const r = R("radio-" + k);
    return { x: r.x + r.w / 2, y: r.y + r.h / 2 };
  };
  const at = (k: string, fx = 0.5, fy = 0.5) => {
    const r = R(k);
    return { x: r.x + r.w * fx, y: r.y + r.h * fy };
  };

  // --- Panneaux ---
  const catOp = interpolate(frame, [PANELS.categoryOut, PANELS.categoryOut + PANELS.fade], [1, 0], {
    extrapolateLeft: "clamp",
    extrapolateRight: "clamp",
  });
  const ficheOp =
    interpolate(frame, [PANELS.categoryOut, PANELS.categoryOut + PANELS.fade], [0, 1], {
      extrapolateLeft: "clamp",
      extrapolateRight: "clamp",
    }) *
    interpolate(frame, [PANELS.ficheOut, PANELS.ficheOut + PANELS.fade], [1, 0], {
      extrapolateLeft: "clamp",
      extrapolateRight: "clamp",
    });
  const formOp = interpolate(frame, [PANELS.ficheOut, PANELS.ficheOut + PANELS.fade], [0, 1], {
    extrapolateLeft: "clamp",
    extrapolateRight: "clamp",
  });
  const slide = (from: number) =>
    interpolate(frame, [from, from + PANELS.fade + 4], [26, 0], {
      extrapolateLeft: "clamp",
      extrapolateRight: "clamp",
      easing: Easing.out(Easing.cubic),
    });

  // --- Caméra (défilement vertical, en px CSS) ---
  const center = (k: string) => -(VIEW_LH - R(k).h) / 2;
  const submitBottom = R("submit").y + R("submit").h;
  const camKeys = [
    { f: 0, v: center("panel-category") },
    { f: PANELS.categoryOut, v: center("panel-category") },
    { f: PANELS.categoryOut + 1, v: center("panel-fiche") },
    { f: PANELS.ficheOut, v: center("panel-fiche") },
    { f: PANELS.ficheOut + 1, v: -8 },
    { f: CLICKS.type + 8, v: -8 },
    { f: CLICKS.type + 22, v: R("field-lieu").y - 150 },
    { f: CLICKS.lieu + 8, v: R("field-lieu").y - 150 },
    { f: CLICKS.lieu + 22, v: R("field-capacite").y - 150 },
    { f: CLICKS.capacite + 8, v: R("field-capacite").y - 150 },
    { f: CLICKS.capacite + 22, v: R("field-nombre").y - 190 },
    { f: NOMBRE_TYPED + 10, v: R("field-nombre").y - 190 },
    { f: TYPING.firstName[0] - 2, v: R("contact-title").y - 24 },
    { f: TYPING.phone[1], v: R("contact-title").y - 24 },
    { f: TYPING.job[0] + 2, v: submitBottom - VIEW_LH + 36 },
  ];
  const camY = track(frame, camKeys);

  // --- Curseur ---
  type WP = { f: number; p: { x: number; y: number } };
  const wps: WP[] = [];
  const go = (arrive: number, p: { x: number; y: number }, travel = 14) => {
    const prev = wps[wps.length - 1];
    if (prev) wps.push({ f: Math.max(prev.f, arrive - travel), p: prev.p });
    wps.push({ f: arrive, p });
  };
  const start = { x: CALC_W * 0.78, y: R("panel-category").y + R("panel-category").h + 60 };
  wps.push({ f: 0, p: start });
  go(CLICKS.category - 3, at("cat-transport", 0.3, 0.55), 16);
  go(CLICKS.fiche - 3, at("fiche-bus", 0.35, 0.55));
  go(CLICKS.type - 3, radio("type-agglo_plus"), 16);
  go(CLICKS.lieu - 3, radio("lieu-eee"), 16);
  go(CLICKS.capacite - 3, radio("capacite-standard"), 16);
  go(CLICKS.nombre - 3, at("input-nombre", 0.3, 0.55), 16);
  (Object.keys(TYPING) as (keyof typeof TYPING)[]).forEach((k, i) => {
    go(TYPING[k][0] - 1, at("input-" + k, 0.62, 0.6), i === 0 ? 10 : 6);
  });
  go(CLICKS.checkbox - 2, at("checkbox"), 6);
  go(CLICKS.submit - 2, at("submit", 0.55, 0.55), 7);
  const cx = track(frame, wps.map((w) => ({ f: w.f, v: w.p.x })));
  const cy = track(frame, wps.map((w) => ({ f: w.f, v: w.p.y })));
  const cursorOp = interpolate(frame, [SCENES.calc.from + 6, SCENES.calc.from + 12], [0, 1], {
    extrapolateLeft: "clamp",
    extrapolateRight: "clamp",
  });

  // --- État du formulaire ---
  const inWin = (c: number, before = 4, after = 8) => frame >= c - before && frame < c + after;
  const typingKey = (Object.keys(TYPING) as (keyof typeof TYPING)[]).find(
    (k) => frame >= TYPING[k][0] - 1 && frame <= TYPING[k][1] + 1
  );
  const contact: Record<string, string> = {};
  (Object.keys(PROFILE) as (keyof typeof PROFILE)[]).forEach((k) => {
    contact[k] = typed(frame, PROFILE[k], TYPING[k]);
  });
  const s: FormState = {
    checked: {
      type: frame >= CLICKS.type ? "agglo_plus" : undefined,
      lieu: frame >= CLICKS.lieu ? "eee" : undefined,
      capacite: frame >= CLICKS.capacite ? "standard" : undefined,
    },
    hoverOption: inWin(CLICKS.type)
      ? "type-agglo_plus"
      : inWin(CLICKS.lieu)
      ? "lieu-eee"
      : inWin(CLICKS.capacite)
      ? "capacite-standard"
      : undefined,
    nombre: frame >= NOMBRE_TYPED ? NOMBRE_VALUE : "",
    focus:
      frame >= CLICKS.nombre && frame < TYPING.firstName[0] - 1 ? "nombre" : typingKey ?? undefined,
    caretOn: typingKey !== undefined || Math.floor(frame / 9) % 2 === 0,
    contact,
    accepted: frame >= CLICKS.checkbox,
    submitEnabled: frame >= CLICKS.checkbox,
    submitHover: frame >= CLICKS.submit - 2,
  };

  // --- Barre de progression ---
  const answered = ANSWERS.filter((a) => frame >= a).length;
  const current = Math.min(answered + 1, 5);
  const sceneIn = interpolate(frame, [SCENES.calc.from, SCENES.calc.from + 8], [0, 1], {
    extrapolateLeft: "clamp",
    extrapolateRight: "clamp",
  });

  return (
    <AbsoluteFill style={{ background: BRAND.white, opacity: sceneIn }}>
      {/* Progression */}
      <div style={{ position: "absolute", left: 60, right: 60, top: 70 }}>
        <div style={{ display: "flex", alignItems: "center", justifyContent: "space-between", marginBottom: 20 }}>
          <div style={{ display: "flex", alignItems: "center", gap: 18 }}>
            <LogoIcon height={52} />
            <span style={{ fontFamily: "'Baloo 2'", fontWeight: 700, fontSize: 40, color: BRAND.violet, lineHeight: 1 }}>
              Calculateur CEE
            </span>
          </div>
          <div style={{ fontFamily: "'Baloo 2'", fontWeight: 700, fontSize: 46, color: BRAND.violet, lineHeight: 1 }}>
            {answered >= 5 ? (
              <span>
                <span style={{ background: BRAND.yellow, borderRadius: 10, padding: "2px 10px" }}>5</span>/5 ✓
              </span>
            ) : (
              <span>
                <span style={{ background: BRAND.yellow, borderRadius: 10, padding: "2px 10px" }}>{current}</span>/5
              </span>
            )}
          </div>
        </div>
        <div style={{ display: "flex", gap: 12 }}>
          {[0, 1, 2, 3, 4].map((i) => {
            const done = ANSWERS[i];
            const p = spring({ frame: frame - done, fps, config: { damping: 18, stiffness: 160 } });
            const isCurrent = i === answered;
            return (
              <div
                key={i}
                style={{
                  flex: 1,
                  height: 16,
                  borderRadius: 8,
                  background: isCurrent ? "rgba(174,165,217,0.75)" : "rgba(174,165,217,0.32)",
                  overflow: "hidden",
                }}
              >
                <div style={{ width: `${Math.max(0, Math.min(1, p)) * 100}%`, height: "100%", background: BRAND.violet, borderRadius: 8 }} />
              </div>
            );
          })}
        </div>
      </div>

      {/* Fenêtre du calculateur */}
      <div
        style={{
          position: "absolute",
          left: (1080 - CALC_W * S) / 2,
          top: VIEW_TOP,
          width: CALC_W * S,
          height: VIEW_H,
          overflow: "hidden",
          WebkitMaskImage:
            "linear-gradient(to bottom, transparent 0, #000 46px, #000 calc(100% - 46px), transparent 100%)",
        }}
      >
        <div
          ref={rootRef}
          className="acceen-calc"
          style={{
            width: CALC_W,
            transform: `scale(${S}) translateY(${-camY}px)`,
            transformOrigin: "0 0",
            position: "absolute",
            top: 0,
            left: 0,
          }}
        >
          <div style={{ position: "absolute", top: 0, left: 0, width: CALC_W, opacity: catOp }}>
            <CategoryPanel reg={reg} hover={inWin(CLICKS.category, 3, 7) ? "transport" : undefined} />
          </div>
          <div style={{ position: "absolute", top: 0, left: 0, width: CALC_W, opacity: ficheOp, transform: `translateY(${slide(PANELS.categoryOut)}px)` }}>
            <FichePanel reg={reg} hover={inWin(CLICKS.fiche, 3, 7) ? "bus" : undefined} />
          </div>
          <div style={{ position: "absolute", top: 0, left: 0, width: CALC_W, opacity: formOp, transform: `translateY(${slide(PANELS.ficheOut)}px)` }}>
            <FormPanel reg={reg} s={s} />
          </div>
          <Cursor
            x={cx}
            y={cy}
            frame={frame}
            clicks={[CLICKS.category, CLICKS.fiche, CLICKS.type, CLICKS.lieu, CLICKS.capacite, CLICKS.nombre, CLICKS.checkbox, CLICKS.submit]}
            opacity={cursorOp}
          />
        </div>
      </div>
    </AbsoluteFill>
  );
};
