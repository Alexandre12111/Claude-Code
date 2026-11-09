import React from "react";
import { interpolate } from "remotion";

// Curseur souris (flèche) avec effet de clic.
export const Cursor: React.FC<{
  x: number;
  y: number;
  frame: number;
  clicks: number[];
  opacity: number;
  size?: number;
}> = ({ x, y, frame, clicks, opacity, size = 26 }) => {
  const last = clicks.filter((c) => c <= frame).pop();
  const since = last === undefined ? 99 : frame - last;
  const press = since < 6 ? interpolate(since, [0, 2, 6], [1, 0.82, 1]) : 1;
  const ripple = since < 14 ? since / 14 : -1;
  return (
    <div style={{ position: "absolute", left: x, top: y, opacity, pointerEvents: "none", zIndex: 50 }}>
      {ripple >= 0 ? (
        <div
          style={{
            position: "absolute",
            left: -size * 1.1 * (0.4 + ripple),
            top: -size * 1.1 * (0.4 + ripple),
            width: size * 2.2 * (0.4 + ripple),
            height: size * 2.2 * (0.4 + ripple),
            borderRadius: "50%",
            background: "rgba(57,18,103,0.28)",
            opacity: 1 - ripple,
          }}
        />
      ) : null}
      <svg
        width={size}
        height={size * 1.3}
        viewBox="0 0 20 26"
        style={{ transform: `scale(${press})`, transformOrigin: "0 0", filter: "drop-shadow(0 2px 3px rgba(25,3,41,.35))" }}
      >
        <path d="M1 1 L1 20.5 L6 15.8 L9.4 23.6 L12.6 22.2 L9.3 14.6 L16 14.6 Z" fill="#190329" stroke="#fff" strokeWidth="1.6" strokeLinejoin="round" />
      </svg>
    </div>
  );
};
