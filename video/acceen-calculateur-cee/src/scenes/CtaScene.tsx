import React from "react";
import { AbsoluteFill, Easing, interpolate, useCurrentFrame } from "remotion";
import { BRAND, SCENES, TEXTS } from "../config";
import { LogoHorizontal } from "../components/Logo";

// 18 à 20 s : appel à l'action.
export const CtaScene: React.FC = () => {
  const frame = useCurrentFrame();
  const t = frame - SCENES.cta.from;
  const a = (d: number) =>
    interpolate(t, [d, d + 8], [0, 1], { extrapolateLeft: "clamp", extrapolateRight: "clamp", easing: Easing.out(Easing.cubic) });
  const arrow = interpolate(t, [14, 60], [0, 10], { extrapolateLeft: "clamp", extrapolateRight: "clamp" });
  return (
    <AbsoluteFill style={{ background: BRAND.white }}>
      <div style={{ position: "absolute", left: -120, top: 140, width: 380, height: 380, borderRadius: 64, background: "rgba(174,165,217,0.32)", border: `1px solid ${BRAND.white}`, transform: "rotate(-12deg)" }} />
      <AbsoluteFill style={{ alignItems: "center", justifyContent: "center", paddingBottom: 140 }}>
        <div
          style={{
            fontFamily: "'Baloo 2'",
            fontWeight: 700,
            color: BRAND.violet,
            fontSize: 124,
            lineHeight: 1.02,
            textAlign: "center",
            opacity: a(0),
            transform: `translateY(${(1 - a(0)) * 30}px)`,
          }}
        >
          Testez
          <br />
          le calculateur
        </div>
        <div
          style={{
            marginTop: 56,
            display: "flex",
            alignItems: "center",
            gap: 22,
            background: BRAND.violet,
            color: "#fff",
            borderRadius: 999,
            padding: "22px 26px 22px 44px",
            fontFamily: "Fustat",
            fontWeight: 700,
            fontSize: 48,
            opacity: a(5),
            transform: `translateY(${(1 - a(5)) * 30}px)`,
          }}
        >
          {TEXTS.ctaUrl}
          <span
            style={{
              width: 72,
              height: 72,
              borderRadius: "50%",
              background: BRAND.yellow,
              display: "flex",
              alignItems: "center",
              justifyContent: "center",
            }}
          >
            <svg width="36" height="36" viewBox="0 0 24 24" style={{ transform: `translateX(${Math.sin(arrow) * 3}px)` }}>
              <path d="M4 12h14M13 6l6 6-6 6" fill="none" stroke={BRAND.violet} strokeWidth="3" strokeLinecap="round" strokeLinejoin="round" />
            </svg>
          </span>
        </div>
      </AbsoluteFill>
      <div style={{ position: "absolute", bottom: 110, left: 0, right: 0, display: "flex", justifyContent: "center", opacity: a(8) }}>
        <LogoHorizontal width={330} />
      </div>
    </AbsoluteFill>
  );
};
