import React from "react";
import { interpolate, Easing } from "remotion";
import { BRAND } from "../config";

// Sous-titre court, intégré à l'image (lisible sans le son).
export const Subtitle: React.FC<{ frame: number; from: number; to: number; num?: string; text: string }> = ({
  frame,
  from,
  to,
  num,
  text,
}) => {
  if (frame < from || frame >= to) return null;
  const t = frame - from;
  const inP = interpolate(t, [0, 7], [0, 1], { extrapolateRight: "clamp", easing: Easing.out(Easing.cubic) });
  return (
    <div
      style={{
        position: "absolute",
        left: 0,
        right: 0,
        top: 1158,
        display: "flex",
        justifyContent: "center",
        opacity: inP,
        transform: `translateY(${(1 - inP) * 22}px)`,
      }}
    >
      <div
        style={{
          background: BRAND.violet,
          color: "#FFFFFF",
          borderRadius: 30,
          padding: "16px 40px 10px",
          fontFamily: "'Baloo 2'",
          fontWeight: 700,
          fontSize: 60,
          lineHeight: 1.15,
          display: "flex",
          alignItems: "center",
          gap: 20,
          boxShadow: "0 14px 40px rgba(57,18,103,.22)",
          border: `1px solid ${BRAND.white}`,
          whiteSpace: "nowrap",
        }}
      >
        {num ? <span style={{ color: BRAND.yellow }}>{num}</span> : null}
        <span>{text}</span>
      </div>
    </div>
  );
};
