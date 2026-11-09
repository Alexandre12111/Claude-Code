import React from "react";
import { AbsoluteFill, Easing, interpolate, useCurrentFrame } from "remotion";
import { BRAND, RESULT_VALUE, SCENES } from "../config";
import { ResultBlock } from "../calc/Calculator";
import "../calc/calc.css";

// 14 à 18 s : le résultat apparaît d'un coup, avec un compteur animé.
export const ResultScene: React.FC = () => {
  const frame = useCurrentFrame();
  const t = frame - SCENES.result.from;
  const pop = interpolate(t, [0, 3, 9], [1.08, 1.0, 1.0], { extrapolateRight: "clamp", easing: Easing.out(Easing.cubic) });
  const count = interpolate(t, [2, 40], [0, 1], {
    extrapolateLeft: "clamp",
    extrapolateRight: "clamp",
    easing: Easing.out(Easing.poly(5)),
  });
  const underline = interpolate(t, [38, 50], [0, 1], { extrapolateLeft: "clamp", extrapolateRight: "clamp", easing: Easing.out(Easing.cubic) });
  const flash = interpolate(t, [0, 6], [0.5, 0], { extrapolateRight: "clamp" });
  return (
    <AbsoluteFill style={{ background: BRAND.white }}>
      <AbsoluteFill style={{ alignItems: "center", paddingTop: 290 }}>
        <div
          className="acceen-calc"
          style={{ width: 600, transform: `scale(${1.66 * pop})`, transformOrigin: "50% 0", fontSize: 18.4 * 1.15 }}
        >
          <div style={{ position: "relative" }}>
            <ResultBlock value={RESULT_VALUE * count} fontSize={104} />
            <div
              style={{
                position: "absolute",
                left: "50%",
                top: 166,
                width: 330,
                height: 5,
                marginLeft: -165,
                borderRadius: 3,
                background: BRAND.gold,
                transform: `scaleX(${underline})`,
              }}
            />
          </div>
        </div>
      </AbsoluteFill>
      <AbsoluteFill style={{ background: "#fff", opacity: flash }} />
    </AbsoluteFill>
  );
};
