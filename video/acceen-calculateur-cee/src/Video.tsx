import React from "react";
import { AbsoluteFill, useCurrentFrame } from "remotion";
import { BRAND, SCENES, SUBTITLES } from "./config";
import { loadFonts } from "./fonts";
import { HookScene } from "./scenes/HookScene";
import { CalcScene } from "./scenes/CalcScene";
import { ResultScene } from "./scenes/ResultScene";
import { CtaScene } from "./scenes/CtaScene";
import { Subtitle } from "./components/Subtitle";

loadFonts();

const inScene = (f: number, s: { from: number; to: number }) => f >= s.from && f < s.to;

export const Video: React.FC = () => {
  const frame = useCurrentFrame();
  return (
    <AbsoluteFill style={{ background: BRAND.white }}>
      {inScene(frame, SCENES.calc) ? <CalcScene /> : null}
      {inScene(frame, SCENES.hook) ? <HookScene /> : null}
      {inScene(frame, SCENES.result) ? <ResultScene /> : null}
      {frame >= SCENES.cta.from ? <CtaScene /> : null}
      {SUBTITLES.map((s, i) => (
        <Subtitle key={i} frame={frame} {...s} />
      ))}
    </AbsoluteFill>
  );
};
