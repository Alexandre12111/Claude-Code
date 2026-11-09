import React from "react";
import { AbsoluteFill, Easing, interpolate, useCurrentFrame } from "remotion";
import { BRAND, SCENES, TEXTS } from "../config";
import { LogoHorizontal } from "../components/Logo";

// 0 à 2 s : accroche. L'image 0 est complète (elle sert de miniature).
export const HookScene: React.FC = () => {
  const frame = useCurrentFrame();
  const out = interpolate(frame, [SCENES.hook.to - 10, SCENES.hook.to - 2], [0, 1], {
    extrapolateLeft: "clamp",
    extrapolateRight: "clamp",
    easing: Easing.in(Easing.cubic),
  });
  const zoom = interpolate(frame, [0, SCENES.hook.to], [1, 1.04]);
  const marker = interpolate(frame, [0, 20], [1, 0.4], {
    extrapolateLeft: "clamp",
    extrapolateRight: "clamp",
    easing: Easing.out(Easing.cubic),
  });
  return (
    <AbsoluteFill style={{ background: BRAND.white, opacity: 1 - out }}>
      {/* Cartes décoratives (lavande, 32 %) */}
      <div style={{ position: "absolute", right: -120, top: 120, width: 420, height: 420, borderRadius: 64, background: "rgba(174,165,217,0.32)", border: `1px solid ${BRAND.white}`, transform: "rotate(14deg)" }} />
      <div style={{ position: "absolute", left: -140, bottom: 300, width: 360, height: 360, borderRadius: 64, background: "rgba(57,18,103,0.08)", transform: "rotate(-10deg)" }} />

      <AbsoluteFill
        style={{
          alignItems: "center",
          justifyContent: "center",
          transform: `scale(${zoom}) translateY(${-out * 60}px)`,
          paddingBottom: 120,
        }}
      >
        <div
          style={{
            background: BRAND.violet,
            color: "#fff",
            fontFamily: "Fustat",
            fontWeight: 700,
            fontSize: 38,
            borderRadius: 999,
            padding: "12px 34px",
            marginBottom: 46,
          }}
        >
          {TEXTS.hookKicker}
        </div>
        <div
          style={{
            fontFamily: "'Baloo 2'",
            fontWeight: 700,
            color: BRAND.violet,
            fontSize: 128,
            lineHeight: 1.02,
            textAlign: "center",
          }}
        >
          <div>{TEXTS.hookLine1}</div>
          <div>
            {TEXTS.hookLine2Before}
            <span style={{ position: "relative", display: "inline-block" }}>
              <span
                style={{
                  position: "absolute",
                  left: -12,
                  right: -12,
                  bottom: 14,
                  height: 58,
                  borderRadius: 14,
                  background: BRAND.yellow,
                  transformOrigin: "left center",
                  transform: `scaleY(${0.9 + 0.1 * marker})`,
                }}
              />
              <span style={{ position: "relative" }}>{TEXTS.hookHighlight}</span>
            </span>
          </div>
        </div>
      </AbsoluteFill>

      <div style={{ position: "absolute", bottom: 110, left: 0, right: 0, display: "flex", justifyContent: "center", opacity: 1 - out }}>
        <LogoHorizontal width={330} />
      </div>
    </AbsoluteFill>
  );
};
